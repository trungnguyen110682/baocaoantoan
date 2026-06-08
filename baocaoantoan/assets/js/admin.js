/* An Toàn MMB — Admin page JS */

let allReports = [];
let allGemba   = [];
let allQR      = [];
let srStatus   = '';
let srPage     = 1;
let wsChartTab = 'ws';
const PAGE_SIZE = 20;

let flType = 'all', flYear = '', flMonth = '';
let flWs = '';
let srFlType = 'all', srFlYear = '', srFlMonth = '', srFlWeek = '', srTableTab = 'ws';
let gbFlType = 'all', gbFlMonth = '', gbFlWeek = '';

window.addEventListener('DOMContentLoaded', () => {
  setInterval(() => { document.getElementById('nav-time').textContent = new Date().toLocaleTimeString('vi-VN'); }, 1000);
  loadAll();
  initImageUpload({ areaId:'af-uarea', cameraId:'af-btn-camera', libId:'af-btn-lib', previewId:'af-img-preview', inputId:'af-file-input' });
});

async function loadAll() {
  const [bcRes, gbRes, qrRes] = await Promise.all([
    fetch('/api/baocao.php').then(r=>r.json()),
    fetch('/api/gemba.php').then(r=>r.json()),
    fetch('/api/qrcore.php').then(r=>r.json()),
  ]);
  allReports = Array.isArray(bcRes) ? bcRes : [];
  allGemba   = Array.isArray(gbRes) ? gbRes : [];
  allQR      = Array.isArray(qrRes) ? qrRes : [];

  initYearSelectors();
  renderDashboard();
  applyFilters();
  renderGembaPage();
  renderGembaList();
  renderAnalytics();
  renderHopDauCa();
  renderQRTable();

  const ov = allReports.filter(r=>r.is_overdue).length;
  const alert = document.getElementById('overdue-alert');
  if (alert && ov > 0) {
    alert.style.display = 'flex';
    document.getElementById('overdue-msg').textContent = `⚠ Có ${ov} báo cáo quá hạn cần xử lý ngay!`;
  }
}

async function refreshDashboard() {
  const btn = document.getElementById('btn-refresh-dash');
  if (btn) btn.textContent = '⏳';
  try {
    const [bcRes, gbRes] = await Promise.all([
      fetch('/api/baocao.php').then(r=>r.json()),
      fetch('/api/gemba.php').then(r=>r.json()),
    ]);
    allReports = Array.isArray(bcRes) ? bcRes : [];
    allGemba   = Array.isArray(gbRes) ? gbRes : [];
    renderDashboard();
    applyFilters();
  } finally {
    if (btn) btn.textContent = '↻ Làm mới';
  }
}

async function refreshSafety() {
  const btn = document.getElementById('btn-refresh-safety');
  if (btn) btn.textContent = '⏳';
  try {
    const res = await fetch('/api/baocao.php').then(r=>r.json());
    allReports = Array.isArray(res) ? res : [];
    applyFilters();
  } finally {
    if (btn) btn.textContent = '↻ Làm mới';
  }
}

async function refreshGemba() {
  const btn = document.getElementById('btn-refresh-gemba');
  if (btn) btn.textContent = '⏳';
  try {
    const res = await fetch('/api/gemba.php').then(r=>r.json());
    allGemba = Array.isArray(res) ? res : [];
    renderGembaPage();
    renderGembaList();
  } finally {
    if (btn) btn.textContent = '↻ Làm mới';
  }
}

function getISOWeek(date) {
  const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
  const day = d.getUTCDay() || 7;
  d.setUTCDate(d.getUTCDate() + 4 - day);
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
  return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

function getISOWeekYear(date) {
  const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
  const day = d.getUTCDay() || 7;
  d.setUTCDate(d.getUTCDate() + 4 - day);
  return d.getUTCFullYear();
}

function initYearSelectors() {
  const years = [...new Set(allReports.map(r=>r.created_at?r.created_at.slice(0,4):null).filter(Boolean))].sort().reverse();
  const thisYear = new Date().getFullYear().toString();
  if (!years.length) years.push(thisYear);
  const monthOpts = Array.from({length:12},(_,i)=>`<option value="${i+1}">Tháng ${i+1}</option>`).join('');
  const weekOpts  = Array.from({length:52},(_,i)=>`<option value="${i+1}">Tuần ${i+1}</option>`).join('');
  const thisMonth = (new Date().getMonth()+1).toString();
  const thisWeek  = getISOWeek(new Date()).toString();

  // Dashboard selectors
  [['fl-year','fl-month'],['top-year','top-month'],['gb-fl-year','gb-fl-month']].forEach(([yid,mid]) => {
    const yr = document.getElementById(yid);
    if (!yr) return;
    yr.innerHTML = years.map(y=>`<option value="${y}">${y}</option>`).join('');
    yr.value = thisYear;
    const mo = document.getElementById(mid);
    if (mo) { mo.innerHTML = monthOpts; mo.value = thisMonth; }
  });
  const flwk = document.getElementById('fl-week');
  if (flwk) { flwk.innerHTML = weekOpts; flwk.value = thisWeek; }

  // Safety Report selectors (month)
  ['sr-fl-year-m'].forEach(yid => {
    const yr = document.getElementById(yid);
    if (!yr) return;
    yr.innerHTML = years.map(y=>`<option value="${y}">${y}</option>`).join('');
    yr.value = thisYear;
  });
  const srmo = document.getElementById('sr-fl-month');
  if (srmo) { srmo.innerHTML = monthOpts; srmo.value = thisMonth; }

  // Safety Report selectors (week)
  ['sr-fl-year-w'].forEach(yid => {
    const yr = document.getElementById(yid);
    if (!yr) return;
    yr.innerHTML = years.map(y=>`<option value="${y}">${y}</option>`).join('');
    yr.value = thisYear;
  });
  const srwk = document.getElementById('sr-fl-week');
  if (srwk) { srwk.innerHTML = weekOpts; srwk.value = thisWeek; }

  flYear = thisYear; flMonth = thisMonth;
  srFlYear = thisYear; srFlMonth = thisMonth; srFlWeek = thisWeek;
  gbFlYear = thisYear; gbFlMonth = thisMonth;
}

// ── Dashboard time filter ─────────────────────────────────────
function toggleDashFilter() {
  flType = document.getElementById('fl-type').value;
  const show = flType !== 'all';
  document.getElementById('fl-time-row').style.display = show ? 'flex' : 'none';
  document.getElementById('fl-month').style.display = (flType === 'month') ? '' : 'none';
  document.getElementById('fl-week').style.display  = (flType === 'week')  ? '' : 'none';
  renderDashboard();
}

function getDashFiltered() {
  flWs    = document.getElementById('fl-ws').value;
  flYear  = document.getElementById('fl-year')  ? document.getElementById('fl-year').value  : '';
  flMonth = document.getElementById('fl-month') ? document.getElementById('fl-month').value : '';
  const flWeek = document.getElementById('fl-week') ? document.getElementById('fl-week').value : '';
  return allReports.filter(r => {
    if (flWs && r.xuong !== flWs) return false;
    if (flType === 'month') {
      const d = new Date(r.created_at);
      if (flYear  && d.getFullYear().toString() !== flYear)  return false;
      if (flMonth && (d.getMonth()+1).toString() !== flMonth) return false;
    } else if (flType === 'week') {
      const d = new Date(r.created_at);
      if (flYear && getISOWeekYear(d).toString() !== flYear) return false;
      if (flWeek && getISOWeek(d).toString() !== flWeek)    return false;
    }
    return true;
  });
}

// ── Dashboard ─────────────────────────────────────────────────
function renderDashboard() {
  const data = getDashFiltered();
  const flWeekLbl = document.getElementById('fl-week') ? document.getElementById('fl-week').value : '';
  const lbl = flType === 'month'
    ? `Tháng ${flMonth}/${flYear}`
    : flType === 'week' ? `Tuần ${flWeekLbl}/${flYear}` : '';
  const rlbl = document.getElementById('fl-range-lbl');
  rlbl.style.display = lbl ? '' : 'none';
  rlbl.textContent = lbl;

  document.getElementById('s-total').textContent   = data.length;
  document.getElementById('s-pending').textContent  = data.filter(r=>r.status==='pending').length;
  document.getElementById('s-fixed').textContent    = data.filter(r=>r.status==='fixed').length;
  document.getElementById('s-overdue').textContent  = data.filter(r=>r.is_overdue).length;
  document.getElementById('sb-pending-cnt').textContent = allReports.filter(r=>r.status==='pending').length;
  document.getElementById('dash-sub').textContent = `${data.length} báo cáo` + (lbl ? ` · ${lbl}` : '');

  drawDoughnut('ch-status',['Chờ duyệt','Đã duyệt','Đã KP','Từ chối'],[
    data.filter(r=>r.status==='pending').length,
    data.filter(r=>r.status==='approved').length,
    data.filter(r=>r.status==='fixed').length,
    data.filter(r=>r.status==='rejected').length,
  ]);
  const ws={}; data.forEach(r=>{const w=r.xuong||'Khác';ws[w]=(ws[w]||0)+1;});
  const wse=topN(ws,8);
  drawBar('ch-xuong',wse.map(e=>e[0]),[{data:wse.map(e=>e[1]),backgroundColor:COLORS.navy,borderRadius:4}]);

  renderWsCharts(data);
  renderDashGemba();
}

// ── 4 Workshop charts ─────────────────────────────────────────
function switchWsChartTab(tab) {
  wsChartTab = tab;
  document.getElementById('ws-tab-ws').className = 'btn btn-sm ' + (tab === 'ws' ? 'btn-navy' : 'btn-ghost');
  document.getElementById('ws-tab-np').className = 'btn btn-sm ' + (tab === 'np' ? 'btn-navy' : 'btn-ghost');
  renderWsCharts(getDashFiltered());
}

function toggleWsTable(btn) {
  const wrap = document.getElementById('ws-table-wrap');
  if (wrap.style.display === 'none') { wrap.style.display = ''; btn.textContent = '▼ Ẩn bảng số liệu'; }
  else { wrap.style.display = 'none'; btn.textContent = '▶ Xem bảng số liệu'; }
}

function renderWsCharts(data) {
  if (!data) data = getDashFiltered();
  const map = {};
  data.forEach(r => {
    const key = wsChartTab === 'ws' ? (r.xuong || 'Khác') : (r.nguoi_kp || 'Chưa phân công');
    if (!map[key]) map[key] = {total:0, pending:0, fixed:0, overdue:0};
    map[key].total++;
    if (['pending','approved'].includes(r.status)) map[key].pending++;
    if (r.status === 'fixed') map[key].fixed++;
    if (r.is_overdue) map[key].overdue++;
  });

  const sorted = Object.entries(map).sort((a,b)=>b[1].total-a[1].total).slice(0,8);
  const labels = sorted.map(e=>e[0]);
  const totals = sorted.map(e=>e[1].total);
  const chuas  = sorted.map(e=>e[1].pending);
  const overdues = sorted.map(e=>e[1].overdue);
  const rates  = sorted.map(e=>e[1].total ? Math.round(e[1].fixed/e[1].total*100) : 0);

  const chartOpts = {
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      datalabels: {
        anchor: 'center', align: 'center',
        font: { size: 11, weight: 'bold' },
        color: 'white',
        formatter: v => v > 0 ? v : '',
      },
    },
    scales: {
      x: { grid: { display: false }, ticks: { font: { size: 10 } } },
      y: { beginAtZero: true, ticks: { font: { size: 10 }, stepSize: 1 } },
    },
  };
  destroyChart('wsc-total');
  new Chart(document.getElementById('wsc-total'), {type:'bar', data:{labels, datasets:[{data:totals,   backgroundColor:COLORS.navy,   borderRadius:4}]}, options:chartOpts});
  destroyChart('wsc-chua');
  new Chart(document.getElementById('wsc-chua'),  {type:'bar', data:{labels, datasets:[{data:chuas,    backgroundColor:COLORS.yellow, borderRadius:4}]}, options:chartOpts});
  destroyChart('wsc-overdue');
  new Chart(document.getElementById('wsc-overdue'),{type:'bar', data:{labels, datasets:[{data:overdues, backgroundColor:COLORS.orange, borderRadius:4}]}, options:chartOpts});
  drawBarKP('wsc-rate', labels, rates, { maintainAspectRatio: false });

  document.getElementById('ws-body').innerHTML = sorted.map(([k,v])=>
    `<tr><td><b>${k}</b></td><td>${v.total}</td><td><span class="chip chip-yellow">${v.pending}</span></td><td><span class="chip chip-green">${v.fixed}</span></td><td>${v.overdue?`<span class="chip chip-red">${v.overdue}</span>`:'—'}</td><td>${v.total?Math.round(v.fixed/v.total*100):0}%</td></tr>`).join('');
}

// ── Dashboard Gemba mini ──────────────────────────────────────
function renderDashGemba() {
  document.getElementById('d-gb-luot').textContent  = allGemba.length;
  document.getElementById('d-gb-nguoi').textContent = new Set(allGemba.map(g=>g.ma_nv)).size;
  document.getElementById('d-gb-suco').textContent  = allGemba.filter(g=>+g.co_su_co > 0).length;
  const wsMap = {};
  allGemba.forEach(g => { const w = g.xuong||'Khác'; wsMap[w] = (wsMap[w]||0)+1; });
  const sorted = Object.entries(wsMap).sort((a,b)=>b[1]-a[1]).slice(0,8);
  drawBarH('ch-gemba-ws', sorted.map(e=>e[0]), sorted.map(e=>e[1]), COLORS.denim);
}

// ── Safety Report time filter ─────────────────────────────────
// ── SR time filter buttons ────────────────────────────────────
function setSRTimeType(type) {
  srFlType = type;
  ['all','month','week'].forEach(t => {
    const btn = document.getElementById('sr-btn-'+t);
    if (btn) btn.className = 'btn btn-sm ' + (t === type ? 'btn-navy' : 'btn-ghost');
  });
  document.getElementById('sr-fl-month-row').style.display = (type === 'month') ? 'flex' : 'none';
  document.getElementById('sr-fl-week-row').style.display  = (type === 'week')  ? 'flex' : 'none';
  applyFilters();
}

function getSRTimeFiltered(arr) {
  srFlYear  = document.getElementById('sr-fl-year-m') ? document.getElementById('sr-fl-year-m').value : '';
  srFlMonth = document.getElementById('sr-fl-month')  ? document.getElementById('sr-fl-month').value  : '';
  const wYear = document.getElementById('sr-fl-year-w') ? document.getElementById('sr-fl-year-w').value : '';
  srFlWeek  = document.getElementById('sr-fl-week')   ? document.getElementById('sr-fl-week').value   : '';
  return arr.filter(r => {
    if (srFlType === 'month') {
      const d = new Date(r.created_at);
      if (srFlYear && d.getFullYear().toString() !== srFlYear) return false;
      if (srFlMonth && (d.getMonth()+1).toString() !== srFlMonth) return false;
    } else if (srFlType === 'week') {
      const d = new Date(r.created_at);
      if (wYear && getISOWeekYear(d).toString() !== wYear) return false;
      if (srFlWeek && getISOWeek(d).toString() !== srFlWeek) return false;
    }
    return true;
  });
}

let filteredReports = [];
function applyFilters() {
  const xuong  = document.getElementById('sr-fl-ws') ? document.getElementById('sr-fl-ws').value : '';
  const search = document.getElementById('sr-search') ? document.getElementById('sr-search').value.toLowerCase() : '';

  let base = getSRTimeFiltered(allReports);
  filteredReports = base.filter(r => {
    if (xuong  && r.xuong !== xuong) return false;
    if (search && !JSON.stringify(r).toLowerCase().includes(search)) return false;
    if (srStatus === 'overdue') return r.is_overdue;
    if (srStatus && r.status !== srStatus) return false;
    return true;
  });

  // Tab counts (based on time+ws filter, not status filter)
  const baseForCount = base;
  const c = {all:baseForCount.length, pending:0, approved:0, fixed:0, rejected:0, overdue:0};
  baseForCount.forEach(r => { c[r.status]=(c[r.status]||0)+1; if(r.is_overdue) c.overdue++; });
  Object.keys(c).forEach(k => { const el=document.getElementById('tc-'+k); if(el) el.textContent=c[k]; });

  // Stat cards
  const setEl = (id, v) => { const el=document.getElementById(id); if(el) el.textContent=v; };
  setEl('sr-s-total',   base.length);
  setEl('sr-s-chuakp',  base.filter(r=>r.status==='pending'||r.status==='approved').length);
  setEl('sr-s-dakp',    base.filter(r=>r.status==='fixed').length);
  setEl('sr-s-overdue', base.filter(r=>r.is_overdue).length);

  renderSRSummaryTable(base);
  srPage = 1; renderSRList();
  document.getElementById('sr-count-sub').textContent = `${filteredReports.length} / ${base.length} báo cáo`;
}

function switchSRTab(status, btn) {
  srStatus = status;
  document.querySelectorAll('#sr-tabs .itab').forEach(b=>b.classList.remove('on'));
  btn.classList.add('on');
  applyFilters();
}

// ── SR Summary Table ──────────────────────────────────────────
function switchSRTableTab(tab) {
  srTableTab = tab;
  document.getElementById('sr-tbl-btn-ws').className = 'btn btn-sm ' + (tab==='ws' ? 'btn-navy' : 'btn-ghost');
  document.getElementById('sr-tbl-btn-np').className = 'btn btn-sm ' + (tab==='np' ? 'btn-navy' : 'btn-ghost');
  const th = document.getElementById('sr-th-name');
  if (th) th.textContent = tab === 'ws' ? 'Xưởng' : 'Họ và tên';
  applyFilters();
}

function toggleSRTable(btn) {
  const wrap = document.getElementById('sr-summary-wrap');
  if (wrap.style.display === 'none') { wrap.style.display = ''; btn.textContent = '▼ Thu gọn'; }
  else { wrap.style.display = 'none'; btn.textContent = '▶ Mở rộng'; }
}

function renderSRSummaryTable(data) {
  const map = {};
  data.forEach(r => {
    const key = srTableTab === 'ws' ? (r.xuong || 'Chưa xác định') : (r.nguoi_kp || 'Chưa phân công');
    if (!map[key]) map[key] = {total:0, chuakp:0, dakp:0, overdue:0};
    map[key].total++;
    if (r.status === 'fixed') map[key].dakp++;
    else map[key].chuakp++;
    if (r.is_overdue) map[key].overdue++;
  });
  const rows = Object.entries(map).sort((a,b)=>b[1].total-a[1].total);
  const tbody = document.getElementById('sr-summary-body');
  if (!tbody) return;
  if (!rows.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:12px;">Không có dữ liệu</td></tr>'; return; }
  tbody.innerHTML = rows.map(([name, d]) => {
    const rate = d.total ? Math.round(d.dakp/d.total*100) : 0;
    const barColor = rate >= 80 ? 'var(--green)' : rate >= 50 ? 'var(--orange)' : 'var(--red)';
    return `<tr>
      <td><b>${name}</b></td>
      <td style="text-align:center;font-weight:700;">${d.total}</td>
      <td style="text-align:center;color:var(--orange);font-weight:600;">${d.chuakp||0}</td>
      <td style="text-align:center;color:var(--green);font-weight:600;">${d.dakp||0}</td>
      <td style="text-align:center;color:var(--red);font-weight:600;">${d.overdue||0}</td>
      <td style="min-width:120px;">
        <div style="display:flex;align-items:center;gap:8px;">
          <div style="flex:1;height:8px;background:var(--border);border-radius:4px;overflow:hidden;">
            <div style="width:${rate}%;height:100%;background:${barColor};border-radius:4px;transition:width .4s;"></div>
          </div>
          <span style="font-size:12px;font-weight:700;color:${barColor};min-width:32px;">${rate}%</span>
        </div>
      </td>
    </tr>`;
  }).join('');
}

function renderSRList() {
  const start = (srPage-1)*PAGE_SIZE;
  const page  = filteredReports.slice(start, start+PAGE_SIZE);
  const pages = Math.ceil(filteredReports.length/PAGE_SIZE);
  if (!page.length) {
    document.getElementById('sr-list').innerHTML = '<div class="empty"><div class="empty-icon">📭</div><p>Không có báo cáo</p></div>';
    document.getElementById('sr-pagination').innerHTML = ''; return;
  }
  document.getElementById('sr-list').innerHTML = page.map(r=>{
    const hasImages = r.hinh_anh || r.fix_img;
    const imgHtml = hasImages ? `
      <div class="rcard-images" onclick="event.stopPropagation();">
        <div class="rcard-img-box">
          <div class="rcard-img-label rcard-img-label--report">📷 Ảnh báo cáo</div>
          ${r.hinh_anh
            ? `<img class="rcard-img" src="${r.hinh_anh}" alt="Ảnh sự cố" onclick="openLightbox('${r.hinh_anh}')" loading="lazy">`
            : `<div class="rcard-img-empty">Chưa có ảnh</div>`}
        </div>
        <div class="rcard-img-box">
          <div class="rcard-img-label rcard-img-label--fix">✅ Ảnh khắc phục</div>
          ${r.fix_img
            ? `<img class="rcard-img" src="${r.fix_img}" alt="Ảnh khắc phục" onclick="openLightbox('${r.fix_img}')" loading="lazy">`
            : `<div class="rcard-img-empty">Chưa có ảnh</div>`}
        </div>
      </div>` : '';
    return `
    <div class="rcard">
      <div class="rcard-top" onclick="showDetail('${r.id}')" style="cursor:pointer;">
        <div><div class="rcard-id">${r.id}</div><div class="rcard-meta">${r.ho_ten||'—'} · ${r.bo_phan||'—'} · ${r.xuong||'—'}</div></div>
        ${statusChip(r.status)}
      </div>
      <div class="rcard-body" onclick="showDetail('${r.id}')" style="cursor:pointer;">${r.noi_dung||'—'}</div>
      ${imgHtml}
      <div class="rcard-footer">
        <span style="font-size:11px;color:var(--muted);">${r.vi_tri||''}</span>
        ${r.is_overdue?'<span class="chip chip-red">⚠ Quá hạn</span>':''}
        ${r.deadline?`<span style="font-size:11px;color:var(--muted);">Hạn: ${fmtDateOnly(r.deadline)}</span>`:''}
        <div style="margin-left:auto;display:flex;gap:6px;flex-wrap:wrap;">
          ${r.status==='pending'?`
            <button class="btn btn-green btn-xs" onclick="openApprove('${r.id}')">✓ Phê duyệt</button>
            <button class="btn btn-red btn-xs" onclick="openReject('${r.id}')">✗ Từ chối</button>`:''}
          <button class="btn btn-ghost btn-xs" onclick="showDetail('${r.id}')">Chi tiết</button>
          <button class="btn btn-ghost btn-xs" style="color:var(--navy);" onclick="openEditReport('${r.id}')">✏ Sửa</button>
          <button class="btn btn-ghost btn-xs" style="color:var(--red);" onclick="confirmDeleteReport('${r.id}')">🗑 Xóa</button>
        </div>
      </div>
    </div>`}).join('');
  buildPagination('sr-pagination', srPage, filteredReports.length, pages, `(p)=>{srPage=p;renderSRList();window.scrollTo(0,200);}`);
}

function showDetail(id) {
  const r = allReports.find(x=>x.id===id); if (!r) return;
  document.getElementById('md-title').textContent = r.id;
  document.getElementById('md-body').innerHTML = `
    <div class="detail-row"><span class="detail-lbl">Người BC</span><span class="detail-val"><b>${r.ho_ten||'—'}</b> (${r.ma_nv||'—'})</span></div>
    <div class="detail-row"><span class="detail-lbl">Bộ phận</span><span class="detail-val">${r.bo_phan||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Xưởng</span><span class="detail-val">${r.xuong||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Vị trí</span><span class="detail-val">${r.vi_tri||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Mô tả</span><span class="detail-val" style="white-space:pre-wrap;">${r.noi_dung||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Trạng thái</span><span class="detail-val">${statusChip(r.status)}</span></div>
    <div class="detail-row"><span class="detail-lbl">Phân loại</span><span class="detail-val">${r.category||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Golden Rule</span><span class="detail-val">${r.golden_rule||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Mức độ</span><span class="detail-val">${r.severity||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Khu vực</span><span class="detail-val">${r.area||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Người KP</span><span class="detail-val">${r.nguoi_kp||'—'} ${r.nkp_chuc_vu?'('+r.nkp_chuc_vu+')':''}</span></div>
    <div class="detail-row"><span class="detail-lbl">Hạn KP</span><span class="detail-val">${fmtDateOnly(r.deadline)||'—'} ${r.days_remaining!=null?'('+r.days_remaining+' ngày)':''}</span></div>
    <div class="detail-row"><span class="detail-lbl">Ngày duyệt</span><span class="detail-val">${fmtDate(r.approved_date)||'—'}</span></div>
    ${r.status==='fixed'?`<hr style="margin:12px 0;border-color:var(--border);">
    <div style="font-size:11px;font-weight:700;color:var(--green);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">KHẮC PHỤC</div>
    <div class="detail-row"><span class="detail-lbl">Người KP</span><span class="detail-val">${r.fixer||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Thời gian KP</span><span class="detail-val">${fmtDate(r.fix_time)||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Nội dung KP</span><span class="detail-val">${r.fix_note||'—'}</span></div>
    `:''}
    <hr style="margin:12px 0;border-color:var(--border);">
    <div class="detail-row"><span class="detail-lbl">Ngày BC</span><span class="detail-val">${fmtDate(r.created_at)}</span></div>
    ${(r.hinh_anh || r.fix_img) ? `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;">
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--navy);margin-bottom:5px;text-align:center;">📷 Ảnh sự cố</div>
        ${r.hinh_anh
          ? `<img src="${r.hinh_anh}" style="width:100%;height:160px;object-fit:cover;border-radius:8px;cursor:pointer;border:1px solid var(--border);" onclick="openLightbox('${r.hinh_anh}')">`
          : `<div style="width:100%;height:160px;border-radius:8px;border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--muted);">Chưa có ảnh</div>`}
      </div>
      <div>
        <div style="font-size:11px;font-weight:700;color:var(--green);margin-bottom:5px;text-align:center;">✅ Ảnh khắc phục</div>
        ${r.fix_img
          ? `<img src="${r.fix_img}" style="width:100%;height:160px;object-fit:cover;border-radius:8px;cursor:pointer;border:1px solid var(--border);" onclick="openLightbox('${r.fix_img}')">`
          : `<div style="width:100%;height:160px;border-radius:8px;border:1px dashed var(--border);display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--muted);">Chưa có ảnh</div>`}
      </div>
    </div>` : ''}
  `;
  const footer = document.getElementById('md-footer');
  footer.innerHTML = `<button class="btn btn-ghost" onclick="closeModal('modal-detail')">Đóng</button>`;
  footer.innerHTML += `<button class="btn btn-ghost btn-sm" style="color:var(--navy);" onclick="closeModal('modal-detail');openEditReport('${r.id}')">✏ Sửa</button>`;
  if (r.status !== 'fixed' && r.status !== 'rejected') {
    footer.innerHTML += `<button class="btn btn-orange btn-sm" onclick="closeModal('modal-detail');window.open('/pages/khacphuc.php?id=${r.id}','_blank')">🔧 Khắc phục</button>`;
  }
  if (r.status === 'pending') footer.innerHTML += `
    <button class="btn btn-red btn-sm" onclick="closeModal('modal-detail');openReject('${r.id}')">✗ Từ chối</button>
    <button class="btn btn-green btn-sm" onclick="closeModal('modal-detail');openApprove('${r.id}')">✓ Phê duyệt</button>`;
  openModal('modal-detail');
}

// ── Approve ───────────────────────────────────────────────────
function openApprove(id) {
  document.getElementById('ap-id').value = id;
  document.getElementById('ap-category').value   = '';
  document.getElementById('ap-goldenrule').value = '';
  document.getElementById('ap-severity').value   = '';
  document.getElementById('ap-area').value       = '';
  document.getElementById('ap-nkp').value        = '';
  document.getElementById('ap-nkp-cv').value     = '';
  document.getElementById('ap-nkp-email').value  = '';
  const d = new Date(); d.setDate(d.getDate()+7);
  document.getElementById('ap-deadline').value = d.toISOString().slice(0,10);
  openModal('modal-approve');
}
function onNKPSelect() {
  const sel = document.getElementById('ap-nkp');
  const opt = sel.options[sel.selectedIndex];
  document.getElementById('ap-nkp-cv').value    = opt.dataset.cv || '';
  document.getElementById('ap-nkp-email').value = opt.dataset.email || '';
}
async function doApprove() {
  const id = document.getElementById('ap-id').value;
  const nkp = document.getElementById('ap-nkp').value;
  const deadline = document.getElementById('ap-deadline').value;
  const category   = document.getElementById('ap-category').value;
  const goldenRule = document.getElementById('ap-goldenrule').value;
  const severity   = document.getElementById('ap-severity').value;
  const area       = document.getElementById('ap-area').value;
  if (!category)   { showToast('Vui lòng chọn Phân loại','error'); return; }
  if (!goldenRule) { showToast('Vui lòng chọn Golden Rule','error'); return; }
  if (!severity)   { showToast('Vui lòng chọn Mức độ','error'); return; }
  if (!area)       { showToast('Vui lòng chọn Khu vực','error'); return; }
  if (!nkp)        { showToast('Vui lòng chọn Người khắc phục','error'); return; }
  if (!deadline)   { showToast('Vui lòng nhập Hạn KP','error'); return; }
  try {
    const res = await fetch('/api/baocao.php',{method:'PUT',credentials:'same-origin',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({id,action:'approve',
        category:document.getElementById('ap-category').value,
        golden_rule:document.getElementById('ap-goldenrule').value,
        severity:document.getElementById('ap-severity').value,
        area:document.getElementById('ap-area').value,
        nguoi_kp:nkp, nkp_chuc_vu:document.getElementById('ap-nkp-cv').value,
        nkp_email:document.getElementById('ap-nkp-email').value, deadline})});
    const d = await res.json();
    if (!d.success) throw new Error(d.error);
    showToast('Đã phê duyệt!','success');
    closeModal('modal-approve'); await loadAll();
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

// ── Reject ────────────────────────────────────────────────────
function openReject(id) {
  document.getElementById('rj-id').value = id;
  document.getElementById('rj-id-show').textContent = id;
  openModal('modal-reject');
}
async function doReject() {
  const id = document.getElementById('rj-id').value;
  try {
    const res = await fetch('/api/baocao.php',{method:'PUT',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,action:'reject'})});
    const d = await res.json();
    if (!d.success) throw new Error(d.error);
    showToast('Đã từ chối','success');
    closeModal('modal-reject'); await loadAll();
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

// ── Edit Report ───────────────────────────────────────────────
async function openEditReport(id) {
  const r = allReports.find(x=>x.id===id); if (!r) return;
  document.getElementById('er-id').value        = id;
  document.getElementById('er-id-show').textContent = id;
  document.getElementById('er-manv').value      = r.ma_nv || '';
  document.getElementById('er-hoten').value     = r.ho_ten || '';
  document.getElementById('er-bophan').value    = r.bo_phan || '';
  document.getElementById('er-xuong').value     = r.xuong || '';
  document.getElementById('er-vitri').value     = r.vi_tri || '';
  document.getElementById('er-noidung').value   = r.noi_dung || '';
  document.getElementById('er-hinhanh').value   = r.hinh_anh || '';
  document.getElementById('er-category').value  = r.category || '';
  document.getElementById('er-severity').value  = r.severity || '';
  document.getElementById('er-goldenrule').value= r.golden_rule || '';
  document.getElementById('er-area').value      = r.area || '';
  document.getElementById('er-nkp').value       = r.nguoi_kp || '';
  document.getElementById('er-nkp-cv').value    = r.nkp_chuc_vu || '';
  document.getElementById('er-nkp-email').value = r.nkp_email || '';
  document.getElementById('er-deadline').value  = r.deadline || '';
  document.getElementById('er-status').value    = r.status || 'pending';

  // Fetch KP link
  try {
    const res = await fetch(`/api/baocao.php?id=${encodeURIComponent(id)}&token=1`);
    if (res.ok) {
      const d = await res.json();
      if (d.link) {
        document.getElementById('er-kp-link').textContent = d.link;
        document.getElementById('er-kp-open').href = d.link;
      }
    }
  } catch(_) {}

  openModal('modal-edit-report');
}

function onERNKPSelect() {
  const sel = document.getElementById('er-nkp');
  const opt = sel.options[sel.selectedIndex];
  document.getElementById('er-nkp-cv').value    = opt.dataset.cv || '';
  document.getElementById('er-nkp-email').value = opt.dataset.email || '';
}

function copyKPLink() {
  const link = document.getElementById('er-kp-link').textContent;
  if (!link || link === '—') return;
  navigator.clipboard.writeText(link).then(()=>showToast('Đã copy link KP','success')).catch(()=>showToast('Không thể copy','error'));
}

async function doEditReport() {
  const id = document.getElementById('er-id').value;
  const payload = {
    id,
    ma_nv:       document.getElementById('er-manv').value,
    ho_ten:      document.getElementById('er-hoten').value,
    bo_phan:     document.getElementById('er-bophan').value,
    xuong:       document.getElementById('er-xuong').value,
    vi_tri:      document.getElementById('er-vitri').value,
    noi_dung:    document.getElementById('er-noidung').value,
    hinh_anh:    document.getElementById('er-hinhanh').value,
    category:    document.getElementById('er-category').value,
    golden_rule: document.getElementById('er-goldenrule').value,
    severity:    document.getElementById('er-severity').value,
    area:        document.getElementById('er-area').value,
    nguoi_kp:    document.getElementById('er-nkp').value,
    nkp_chuc_vu: document.getElementById('er-nkp-cv').value,
    nkp_email:   document.getElementById('er-nkp-email').value,
    deadline:    document.getElementById('er-deadline').value,
    status:      document.getElementById('er-status').value,
  };
  try {
    const res = await fetch('/api/baocao.php', {method:'PUT', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)});
    const d = await res.json();
    if (!d.success) throw new Error(d.error||'Lỗi');
    showToast('Đã lưu thay đổi','success');
    closeModal('modal-edit-report');
    await loadAll();
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

// ── Delete Report ─────────────────────────────────────────────
function confirmDeleteReport(id) {
  if (!confirm(`Bạn có chắc muốn XÓA báo cáo ${id}?\n\nHành động này không thể hoàn tác!`)) return;
  doDeleteReport(id);
}

async function doDeleteReport(id) {
  try {
    const res = await fetch(`/api/baocao.php?id=${encodeURIComponent(id)}`, {method:'DELETE'});
    const d = await res.json();
    if (!d.success) throw new Error(d.error||'Lỗi');
    showToast(`Đã xóa ${id}`,'success');
    await loadAll();
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

// ── Admin Fix ─────────────────────────────────────────────────
function openFixFromEdit() {
  const id = document.getElementById('er-id').value;
  closeModal('modal-edit-report');
  openAdminFix(id);
}

function openAdminFix(id) {
  const r = allReports.find(x => x.id === id);
  document.getElementById('af-id').value     = id;
  document.getElementById('af-id-show').textContent = id;
  document.getElementById('af-fixer').value  = (r && r.nguoi_kp) ? r.nguoi_kp : '';
  document.getElementById('af-note').value   = '';
  // Reset image upload area
  const fileInput = document.getElementById('af-file-input');
  if (fileInput) fileInput.value = '';
  const preview = document.getElementById('af-img-preview');
  if (preview) { preview.style.display = 'none'; const img = preview.querySelector('img'); if (img) img.src = ''; }
  const area = document.getElementById('af-uarea');
  if (area) { area.classList.remove('has-file','req-err'); }
  const errImg = document.getElementById('err-af-img');
  if (errImg) errImg.classList.remove('on');
  openModal('modal-admin-fix');
}

async function doAdminFix() {
  const id    = document.getElementById('af-id').value;
  const fixer = document.getElementById('af-fixer').value.trim();
  const note  = document.getElementById('af-note').value.trim();
  const file  = document.getElementById('af-file-input').files[0];
  const area  = document.getElementById('af-uarea');
  const errImg= document.getElementById('err-af-img');
  let ok = true;
  if (!note) { showToast('Vui lòng nhập nội dung khắc phục','error'); ok = false; }
  if (!file) { area.classList.add('req-err'); errImg.classList.add('on'); ok = false; }
  else { area.classList.remove('req-err'); errImg.classList.remove('on'); }
  if (!ok) return;
  const btn = document.querySelector('#modal-admin-fix .btn-green');
  if (btn) { btn.disabled = true; btn.textContent = 'Đang lưu...'; }
  try {
    const fd = new FormData(); fd.append('image', file);
    const upRes = await fetch('/api/upload.php', {method:'POST', credentials:'same-origin', body:fd});
    const upData = await upRes.json();
    if (!upData.success) throw new Error(upData.error || 'Upload thất bại');
    const res = await fetch('/api/baocao.php', {method:'PUT', credentials:'same-origin', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({id, action:'fix', fixer, fix_note:note, fix_img:upData.url})});
    const d = await res.json();
    if (!d.success) throw new Error(d.error||'Lỗi');
    showToast('Đã đánh dấu khắc phục!','success');
    closeModal('modal-admin-fix');
    await loadAll();
  } catch(e) {
    showToast(e.message||'Lỗi','error');
    if (btn) { btn.disabled = false; btn.innerHTML = '✅ Xác nhận đã KP'; }
  }
}

// ── Gemba page ────────────────────────────────────────────────
function getISOWeekNum(date) {
  const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
  const day = d.getUTCDay() || 7;
  d.setUTCDate(d.getUTCDate() + 4 - day);
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
  return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

function toggleGembaFilter() {
  gbFlType = document.getElementById('gb-fl-type').value;
  document.getElementById('gb-fl-month').style.display = (gbFlType === 'month') ? '' : 'none';
  document.getElementById('gb-fl-week').style.display  = (gbFlType === 'week')  ? '' : 'none';
  renderGembaPage();
}

function getGembaFiltered() {
  gbFlMonth = document.getElementById('gb-fl-month') ? document.getElementById('gb-fl-month').value : '';
  gbFlWeek  = document.getElementById('gb-fl-week')  ? document.getElementById('gb-fl-week').value  : '';
  const search = (document.getElementById('gb-search') ? document.getElementById('gb-search').value : '').trim().toLowerCase();
  const year = new Date().getFullYear();
  return allGemba.filter(g => {
    // Loại trừ bộ phận SHE
    if ((g.bo_phan || '').trim().toUpperCase() === 'SHE') return false;
    // Lọc theo kỳ
    if (gbFlType === 'month' && gbFlMonth) {
      const d = new Date(g.created_at);
      if (d.getFullYear() !== year || (d.getMonth()+1).toString() !== gbFlMonth) return false;
    } else if (gbFlType === 'week' && gbFlWeek) {
      const d = new Date(g.created_at);
      if (d.getFullYear() !== year || getISOWeekNum(d).toString() !== gbFlWeek) return false;
    }
    // Tìm kiếm theo thông tin người nhập
    if (search) {
      const hay = [g.ho_ten, g.ma_nv, g.bo_phan, g.xuong].map(v=>(v||'').toLowerCase()).join(' ');
      if (!hay.includes(search)) return false;
    }
    return true;
  });
}

function renderGembaPage() {
  const data = getGembaFiltered();
  document.getElementById('gb-st0').textContent = data.length;
  document.getElementById('gb-st1').textContent = new Set(data.map(g=>g.ma_nv)).size;
  document.getElementById('gb-st2').textContent = data.filter(g=>g.co_su_co).length;

  const wsMap = {};
  data.forEach(g => { const w = g.xuong||'Khác'; wsMap[w] = (wsMap[w]||0)+1; });
  const sorted = Object.entries(wsMap).sort((a,b)=>b[1]-a[1]);
  drawBarH('gemba-ws-chart', sorted.map(e=>e[0]), sorted.map(e=>e[1]), COLORS.denim);

  const nvMap = {};
  data.forEach(g => {
    const k = g.ma_nv || '?';
    if (!nvMap[k]) nvMap[k] = {ho_ten:g.ho_ten,ma_nv:g.ma_nv,bo_phan:g.bo_phan,luot:0,suCo:0};
    nvMap[k].luot++; if (g.co_su_co) nvMap[k].suCo++;
  });
  document.getElementById('gb-person-tbody').innerHTML = Object.values(nvMap)
    .sort((a,b)=>b.luot-a.luot).slice(0,20)
    .map((v,i)=>`<tr><td>${i+1}</td><td><b>${v.ho_ten||'—'}</b></td><td style="font-size:11px;">${v.ma_nv||'—'}</td><td>${v.bo_phan||'—'}</td><td><span class="chip chip-blue">${v.luot}</span></td><td>${v.suCo?`<span class="chip chip-red">${v.suCo}</span>`:'—'}</td></tr>`).join('');

  renderGembaList();
}

function renderGembaList() {
  const xuong = document.getElementById('gb-filter-xuong').value;
  let data = getGembaFiltered();
  if (xuong) data = data.filter(g=>g.xuong===xuong);
  const ck = (v) => v==='Đạt'?'<span class="chip chip-green" style="font-size:10px;padding:2px 6px;">Đạt</span>':'<span class="chip chip-red" style="font-size:10px;padding:2px 6px;">KĐ</span>';
  document.getElementById('gb-list-body').innerHTML = data.length ? data.map(g=>`
    <tr>
      <td class="tbl-id" style="font-size:10px;">${g.id}</td>
      <td><b>${g.khu_vuc||'—'}</b><div class="tbl-sub">${g.phu_trach||''}</div></td>
      <td>${g.xuong||'—'}</td>
      <td>${g.ho_ten||'—'}<div class="tbl-sub">${g.ma_nv||''}</div></td>
      <td style="font-size:11px;">${g.bo_phan||'—'}</td>
      <td>${ck(g.interlock)}</td>
      <td>${ck(g.checan)}</td>
      <td>${ck(g.tudien)}</td>
      <td>${ck(g.s5_moitruong)}</td>
      <td>${g.co_su_co?'<span class="chip chip-red">Có</span>':'—'}</td>
      <td style="font-size:11px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${(g.ghi_chu||'').replace(/"/g,'&quot;')}">${g.ghi_chu||'—'}</td>
      <td style="font-size:11px;color:var(--muted);white-space:nowrap;">${fmtDate(g.created_at)}</td>
      <td><button class="btn btn-blue btn-xs" onclick="editGemba('${g.id}')">✏️</button></td>
      <td><button class="btn btn-red btn-xs" onclick="deleteGemba('${g.id}')">🗑</button></td>
    </tr>`).join('')
    : '<tr><td colspan="14"><div class="empty"><p>Không có dữ liệu</p></div></td></tr>';
}

function showGembaDetail(id) {
  const g = allGemba.find(x=>x.id===id); if (!g) return;
  document.getElementById('modal-gb-detail').dataset.id = id;
  const ck = (v) => v==='Đạt'?'<span class="chip chip-green">Đạt</span>':'<span class="chip chip-red">Không đạt</span>';
  document.getElementById('md-gb-body').innerHTML = `
    <div class="detail-row"><span class="detail-lbl">ID</span><span class="detail-val"><b>${g.id}</b></span></div>
    <div class="detail-row"><span class="detail-lbl">Mã QR</span><span class="detail-val">${g.ma_qr||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Xưởng</span><span class="detail-val">${g.xuong||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Khu vực</span><span class="detail-val">${g.khu_vuc||'—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Người KT</span><span class="detail-val"><b>${g.ho_ten||'—'}</b></span></div>
    <hr style="margin:10px 0;border-color:var(--border);">
    <div class="detail-row"><span class="detail-lbl">Interlock</span><span class="detail-val">${ck(g.interlock)}</span></div>
    <div class="detail-row"><span class="detail-lbl">Che chắn</span><span class="detail-val">${ck(g.checan)}</span></div>
    <div class="detail-row"><span class="detail-lbl">Tủ điện</span><span class="detail-val">${ck(g.tudien)}</span></div>
    <div class="detail-row"><span class="detail-lbl">S5 MT</span><span class="detail-val">${ck(g.s5_moitruong)}</span></div>
    <div class="detail-row"><span class="detail-lbl">Sự cố</span><span class="detail-val">${g.co_su_co?'<span class="chip chip-red">⚠ Có</span>':'<span class="chip chip-green">Không</span>'}</span></div>
    ${g.ghi_chu?`<div class="detail-row"><span class="detail-lbl">Ghi chú</span><span class="detail-val">${g.ghi_chu}</span></div>`:''}
    <div class="detail-row"><span class="detail-lbl">Thời gian</span><span class="detail-val">${fmtDate(g.created_at)}</span></div>
  `;
  openModal('modal-gb-detail');
}

function editGembaFromDetail() {
  const id = document.getElementById('modal-gb-detail').dataset.id;
  closeModal('modal-gb-detail');
  editGemba(id);
}

async function deleteGembaFromDetail() {
  const id = document.getElementById('modal-gb-detail').dataset.id;
  if (!id) return;
  if (!confirm(`Xóa bản ghi gemba ${id}?\nHành động này không thể hoàn tác!`)) return;
  closeModal('modal-gb-detail');
  await deleteGemba(id);
}

async function deleteGemba(id) {
  if (!confirm(`Xóa bản ghi gemba ${id}?`)) return;
  try {
    const res = await fetch('/api/gemba.php?id='+encodeURIComponent(id),{method:'DELETE',credentials:'same-origin'});
    const d = await res.json();
    if (!d.success) throw new Error(d.error);
    allGemba = allGemba.filter(g=>g.id!==id);
    renderGembaPage();
    showToast('Đã xóa','success');
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

function editGemba(id) {
  const g = allGemba.find(x=>x.id===id); if (!g) return;
  document.getElementById('gb-ed-id').value          = g.id;
  document.getElementById('gb-ed-ma_qr').value       = g.ma_qr    || '';
  document.getElementById('gb-ed-xuong').value       = g.xuong    || '';
  document.getElementById('gb-ed-khu_vuc').value     = g.khu_vuc  || '';
  document.getElementById('gb-ed-phu_trach').value   = g.phu_trach|| '';
  document.getElementById('gb-ed-ma_nv').value       = g.ma_nv    || '';
  document.getElementById('gb-ed-ho_ten').value      = g.ho_ten   || '';
  document.getElementById('gb-ed-bo_phan').value     = g.bo_phan  || '';
  document.getElementById('gb-ed-interlock').value   = g.interlock    || 'Đạt';
  document.getElementById('gb-ed-checan').value      = g.checan       || 'Đạt';
  document.getElementById('gb-ed-tudien').value      = g.tudien       || 'Đạt';
  document.getElementById('gb-ed-s5_moitruong').value= g.s5_moitruong || 'Đạt';
  document.getElementById('gb-ed-ghi_chu').value     = g.ghi_chu  || '';
  document.getElementById('gb-ed-co_su_co').value    = g.co_su_co ? '1' : '0';
  openModal('modal-gb-edit');
}

async function saveGembaEdit() {
  const id = document.getElementById('gb-ed-id').value;
  const payload = {
    ma_qr:        document.getElementById('gb-ed-ma_qr').value.trim(),
    xuong:        document.getElementById('gb-ed-xuong').value.trim(),
    khu_vuc:      document.getElementById('gb-ed-khu_vuc').value.trim(),
    phu_trach:    document.getElementById('gb-ed-phu_trach').value.trim(),
    ma_nv:        document.getElementById('gb-ed-ma_nv').value.trim(),
    ho_ten:       document.getElementById('gb-ed-ho_ten').value.trim(),
    bo_phan:      document.getElementById('gb-ed-bo_phan').value.trim(),
    interlock:    document.getElementById('gb-ed-interlock').value,
    checan:       document.getElementById('gb-ed-checan').value,
    tudien:       document.getElementById('gb-ed-tudien').value,
    s5_moitruong: document.getElementById('gb-ed-s5_moitruong').value,
    ghi_chu:      document.getElementById('gb-ed-ghi_chu').value.trim(),
    co_su_co:     document.getElementById('gb-ed-co_su_co').value === '1' ? 1 : 0,
  };
  try {
    const res = await fetch('/api/gemba.php?id='+encodeURIComponent(id), {
      method: 'PUT',
      credentials: 'same-origin',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const d = await res.json();
    if (!d.success) throw new Error(d.error);
    const idx = allGemba.findIndex(g=>g.id===id);
    if (idx !== -1) allGemba[idx] = {...allGemba[idx], ...payload};
    closeModal('modal-gb-edit');
    renderGembaPage();
    showToast('Đã lưu thay đổi','success');
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

// ── Analytics ─────────────────────────────────────────────────
function renderAnalytics() {
  const cat = countBy(allReports.filter(r=>r.category),'category');
  const catTop = topN(cat);
  drawDoughnut('ch-category',catTop.map(e=>e[0]),catTop.map(e=>e[1]));
  drawBarH('ch-cat-pareto',catTop.map(e=>e[0]),catTop.map(e=>e[1]),PALETTE.slice(0,catTop.length));
  document.getElementById('cat-tbody').innerHTML = catTop.map(([c,n])=>{const kp=allReports.filter(r=>r.category===c&&r.status==='fixed').length;return`<tr><td><b>${c}</b></td><td>${n}</td><td>${kp}</td><td>${Math.round(kp/n*100)}%</td></tr>`;}).join('');

  const gr = countBy(allReports.filter(r=>r.golden_rule),'golden_rule');
  const grTop = topN(gr);
  drawDoughnut('ch-gr',grTop.map(e=>e[0]),grTop.map(e=>e[1]));
  drawBarH('ch-gr-bar',grTop.map(e=>e[0]),grTop.map(e=>e[1]),COLORS.orange);
  document.getElementById('gr-tbody').innerHTML = grTop.map(([g,n])=>{const kp=allReports.filter(r=>r.golden_rule===g&&r.status==='fixed').length;return`<tr><td><b>${g}</b></td><td>${n}</td><td>${kp}</td><td>${Math.round(kp/n*100)}%</td></tr>`;}).join('');

  const sv = countBy(allReports.filter(r=>r.severity),'severity');
  const svTop = topN(sv);
  drawDoughnut('ch-severity',svTop.map(e=>e[0]),svTop.map(e=>e[1]));
  document.getElementById('severity-tbody').innerHTML = svTop.map(([s,n])=>{const kp=allReports.filter(r=>r.severity===s&&r.status==='fixed').length;return`<tr><td><b>${s}</b></td><td>${n}</td><td>${kp}</td><td>${Math.round(kp/n*100)}%</td></tr>`;}).join('');

  const ar = countBy(allReports.filter(r=>r.area),'area');
  const arTop = topN(ar);
  drawDoughnut('ch-area',arTop.map(e=>e[0]),arTop.map(e=>e[1]));
  document.getElementById('area-tbody').innerHTML = arTop.map(([a,n])=>{const kp=allReports.filter(r=>r.area===a&&r.status==='fixed').length;return`<tr><td><b>${a}</b></td><td>${n}</td><td>${kp}</td><td>${Math.round(kp/n*100)}%</td></tr>`;}).join('');
}

// ── Họp đầu ca ────────────────────────────────────────────────
function renderHopDauCa() { loadHopDauCa(); }
function loadHopDauCa() {
  const xuong = document.getElementById('hdc-xuong').value;
  document.getElementById('hdc-title').textContent = 'Báo cáo — ' + xuong;
  const rows = allReports.filter(r=>r.xuong===xuong);
  document.getElementById('hdc-tbody').innerHTML = rows.length ? rows.map(r=>`
    <tr style="cursor:pointer;" onclick="showDetail('${r.id}')">
      <td class="tbl-id">${r.id}</td><td>${r.ho_ten||'—'}</td><td>${r.vi_tri||'—'}</td>
      <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.noi_dung||'—'}</td>
      <td>${statusChip(r.status)}</td>
      <td style="font-size:11px;white-space:nowrap;">${fmtDateOnly(r.created_at)}</td>
      <td style="font-size:11px;white-space:nowrap;">${r.deadline?fmtDateOnly(r.deadline):'—'}</td>
    </tr>`).join('')
    : '<tr><td colspan="7"><div class="empty"><p>Không có báo cáo</p></div></td></tr>';
}

// ── QR Table ──────────────────────────────────────────────────
function renderQRTable() {
  document.getElementById('qr-tbody').innerHTML = allQR.map(q=>`
    <tr><td class="tbl-id">${q.ma_qr}</td><td>${q.xuong||'—'}</td><td>${q.khu_vuc||'—'}</td><td>${q.phu_trach||'—'}</td>
    <td><a href="/pages/gemba.php?qr=${q.ma_qr}" target="_blank" class="btn btn-ghost btn-xs">Mở form</a></td></tr>`).join('');
}

// ── Top NV modal ──────────────────────────────────────────────
function showTop() {
  document.getElementById('top-type').value = 'month';
  document.getElementById('top-month').style.display = '';
  renderTop();
  openModal('modal-top');
}

function renderTop() {
  const type   = document.getElementById('top-type').value;
  const year   = document.getElementById('top-year').value;
  const month  = document.getElementById('top-month').value;
  const search = (document.getElementById('top-search').value||'').toLowerCase();
  document.getElementById('top-month').style.display = (type === 'month') ? '' : 'none';

  let data = allReports;
  if (type === 'month') {
    data = data.filter(r => {
      const d = new Date(r.created_at);
      return (!year || d.getFullYear().toString() === year) && (!month || (d.getMonth()+1).toString() === month);
    });
  }

  const nvMap = {};
  data.forEach(r => {
    if (!r.ho_ten) return;
    const k = r.ma_nv || r.ho_ten;
    if (!nvMap[k]) nvMap[k] = {ho_ten:r.ho_ten, bo_phan:r.bo_phan||'—', count:0};
    nvMap[k].count++;
  });

  let rows = Object.values(nvMap).sort((a,b)=>b.count-a.count);
  if (search) rows = rows.filter(v=>v.ho_ten.toLowerCase().includes(search));
  const maxCount = rows[0]?.count || 1;

  const lbl = type === 'month' ? `Tháng ${month}/${year}` : 'Tất cả thời gian';
  document.getElementById('top-lbl').textContent = `📊 ${lbl} — ${rows.length} nhân viên`;

  document.getElementById('top-list').innerHTML = rows.length ? rows.slice(0,20).map((v,i)=>{
    const cls = i===0?'top-r1':i===1?'top-r2':i===2?'top-r3':'top-rn';
    return `<div class="top-row">
      <div class="top-rank ${cls}">${i+1}</div>
      <div style="flex:1;min-width:0;"><div style="font-size:13px;font-weight:700;">${v.ho_ten}</div><div style="font-size:11px;color:var(--muted);">${v.bo_phan}</div></div>
      <div class="top-bar-wrap"><div class="top-bar-fill" style="width:${Math.round(v.count/maxCount*100)}%"></div></div>
      <div class="top-count">${v.count} BC</div>
    </div>`;
  }).join('') : '<div class="empty"><p>Không có dữ liệu</p></div>';
}

// ── Settings ──────────────────────────────────────────────────
async function loadSettings() {
  try {
    const res = await fetch('/api/settings.php?action=webhooks');
    const webhooks = await res.json();
    if (!Array.isArray(webhooks)) { document.getElementById('webhook-list').innerHTML='<p style="color:var(--red)">Lỗi tải webhook</p>'; return; }

    const keyLabels = {
      baocao_moi: 'Báo cáo mới',
      phe_duyet: 'Phê duyệt (NKP)',
      phe_duyet_2: 'Phê duyệt (Người BC)',
      da_khac_phuc: 'Đã khắc phục',
    };

    document.getElementById('webhook-list').innerHTML = webhooks.map(w=>`
      <div class="wh-row">
        <div style="min-width:130px;">
          <div class="wh-key">${w.ten_key}</div>
          <div style="font-size:11px;color:var(--muted);margin-top:3px;">${keyLabels[w.ten_key]||''}</div>
        </div>
        <div class="wh-urls" style="flex:1;">
          ${w.url ? `<div class="wh-url-val">🔗 ${w.url}</div>` : '<div class="wh-url-empty">URL 1: (chưa cài)</div>'}
          ${w.url2 ? `<div class="wh-url-val">🔗 ${w.url2}</div>` : '<div class="wh-url-empty">URL 2: (chưa cài)</div>'}
        </div>
        <button class="btn btn-ghost btn-sm" onclick="openWebhookEdit(${w.id},'${w.ten_key}','${(w.url||'').replace(/'/g,"\\'")}','${(w.url2||'').replace(/'/g,"\\'")}','${keyLabels[w.ten_key]||w.ten_key}')">✏️ Sửa</button>
      </div>`).join('') || '<div class="empty"><p>Chưa có webhook</p></div>';
  } catch(e) { document.getElementById('webhook-list').innerHTML='<p style="color:var(--red)">Lỗi: '+e.message+'</p>'; }
}

function openWebhookEdit(id, key, url1, url2, desc) {
  document.getElementById('wh-id').value = id;
  document.getElementById('wh-modal-title').textContent = 'Webhook: ' + key;
  document.getElementById('wh-desc-text').textContent = desc;
  document.getElementById('wh-url1').value = url1;
  document.getElementById('wh-url2').value = url2;
  openModal('modal-webhook');
}

async function saveWebhook() {
  const id   = document.getElementById('wh-id').value;
  const url  = document.getElementById('wh-url1').value.trim();
  const url2 = document.getElementById('wh-url2').value.trim();
  try {
    const res = await fetch('/api/settings.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({action:'save_webhook', id, url, url2})});
    const d = await res.json();
    if (!d.success) throw new Error(d.error);
    showToast('Đã lưu webhook','success');
    closeModal('modal-webhook');
    loadSettings();
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

async function changePassword(role) {
  const newpass = document.getElementById(`pw-${role}-new`).value;
  const confirm = document.getElementById(`pw-${role}-confirm`).value;
  if (!newpass) { showToast('Vui lòng nhập mật khẩu mới','error'); return; }
  if (newpass !== confirm) { showToast('Mật khẩu xác nhận không khớp','error'); return; }
  if (newpass.length < 4) { showToast('Mật khẩu tối thiểu 4 ký tự','error'); return; }
  try {
    const res = await fetch('/api/settings.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({action:'change_password', role, password:newpass})});
    const d = await res.json();
    if (!d.success) throw new Error(d.error);
    document.getElementById(`pw-${role}-new`).value = '';
    document.getElementById(`pw-${role}-confirm`).value = '';
    showToast(`Đã đổi mật khẩu ${role === 'admin' ? 'Admin' : 'Viewer'} thành công!`, 'success');
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

// ── Page switch ───────────────────────────────────────────────
function switchPage(pageId, btn) {
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('.sb-item').forEach(b=>b.classList.remove('active'));
  document.getElementById('page-'+pageId).classList.add('active');
  btn.classList.add('active');
  const sb = document.querySelector('.sidebar'); if(sb) sb.classList.remove('open');
  const ov = document.getElementById('sb-overlay'); if(ov) ov.classList.remove('on');
  if (pageId === 'settings')   loadSettings();
  if (pageId === 'thanhvien')  loadThanhVien();
  if (pageId === 'dashboard')  renderDashGemba();
}

// ── Export ────────────────────────────────────────────────────
function exportAllCSV()    { exportCSV(allReports,    'baocao-'+new Date().toISOString().slice(0,10)+'.csv'); }
function exportSafetyCSV() { exportCSV(filteredReports,'baocao-loc-'+new Date().toISOString().slice(0,10)+'.csv'); }

// ── Lightbox ──────────────────────────────────────────────────
function openLightbox(src) { const lb=document.getElementById('lightbox'); document.getElementById('lb-img').src=src; lb.style.display='flex'; }
function closeLightbox()   { document.getElementById('lightbox').style.display='none'; }

// ── Thành viên ────────────────────────────────────────────────
let allTVMembers = [];

async function loadThanhVien() {
  try {
    const res = await fetch('/api/thanhvien.php?action=list');
    allTVMembers = await res.json();
    if (!Array.isArray(allTVMembers)) { allTVMembers = []; return; }

    const bpSet = [...new Set(allTVMembers.map(m=>m.bo_phan).filter(Boolean))].sort();
    const bpEl = document.getElementById('tv-filter-bophan');
    bpEl.innerHTML = '<option value="">Tất cả bộ phận</option>' + bpSet.map(b=>`<option>${b}</option>`).join('');

    renderTVList();
  } catch(e) { showToast('Lỗi tải thành viên: ' + e.message, 'error'); }
}

function renderTVList() {
  const vaitro = document.getElementById('tv-filter-vaitro').value;
  const bophan = document.getElementById('tv-filter-bophan').value;
  const q      = (document.getElementById('tv-search').value || '').toLowerCase();

  const filtered = allTVMembers.filter(m => {
    if (vaitro && m.vai_tro !== vaitro) return false;
    if (bophan && m.bo_phan !== bophan) return false;
    if (q && !(m.ho_ten||'').toLowerCase().includes(q) && !(m.ma_nv||'').toLowerCase().includes(q) && !(m.bo_phan||'').toLowerCase().includes(q)) return false;
    return true;
  });

  document.getElementById('tv-k0').textContent = allTVMembers.length;
  document.getElementById('tv-k1').textContent = allTVMembers.filter(m=>m.vai_tro==='nhanvien').length;
  document.getElementById('tv-k2').textContent = allTVMembers.filter(m=>m.vai_tro==='nguoikhacphuc').length;
  document.getElementById('tv-k3').textContent = allTVMembers.filter(m=>m.vai_tro==='quanly').length;
  document.getElementById('tv-sub').textContent = `${filtered.length} / ${allTVMembers.length} thành viên`;

  const vaitroChip = {
    nhanvien:      '<span class="chip chip-blue" style="font-size:10px;">Nhân viên</span>',
    nguoikhacphuc: '<span class="chip chip-green" style="font-size:10px;">Người KP</span>',
    quanly:        '<span class="chip" style="font-size:10px;background:var(--orange);color:white;">Quản lý</span>',
  };

  document.getElementById('tv-tbody').innerHTML = filtered.length ? filtered.map(m=>`
    <tr>
      <td class="tbl-id">${m.ma_nv||'—'}</td>
      <td><b>${m.ho_ten}</b></td>
      <td>${m.bo_phan||'—'}</td>
      <td>${m.xuong||'—'}</td>
      <td>${m.chuc_vu||'—'}</td>
      <td style="font-size:12px;">${m.email||'—'}</td>
      <td>${vaitroChip[m.vai_tro]||m.vai_tro}</td>
      <td style="text-align:center;white-space:nowrap;">
        <button class="btn btn-ghost btn-xs" onclick="openTVModal(${m.id})">✏️</button>
        <button class="btn btn-red btn-xs" onclick="deleteTVMember(${m.id},'${m.ho_ten.replace(/'/g,"\\'")}')">🗑</button>
      </td>
    </tr>`).join('')
    : '<tr><td colspan="8"><div class="empty"><p>Không có thành viên</p></div></td></tr>';
}

function openTVModal(id) {
  document.getElementById('tv-id').value = id || '';
  if (id) {
    const m = allTVMembers.find(x=>x.id==id); if (!m) return;
    document.getElementById('tv-modal-title').textContent = 'Sửa thành viên';
    document.getElementById('tv-manv').value   = m.ma_nv  || '';
    document.getElementById('tv-hoten').value  = m.ho_ten || '';
    document.getElementById('tv-bophan').value = m.bo_phan|| '';
    document.getElementById('tv-xuong').value  = m.xuong  || '';
    document.getElementById('tv-chucvu').value = m.chuc_vu|| '';
    document.getElementById('tv-email').value  = m.email  || '';
    document.getElementById('tv-vaitro').value = m.vai_tro|| 'nhanvien';
  } else {
    document.getElementById('tv-modal-title').textContent = 'Thêm thành viên mới';
    ['tv-manv','tv-hoten','tv-bophan','tv-chucvu','tv-email'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('tv-xuong').value  = '';
    document.getElementById('tv-vaitro').value = 'nhanvien';
  }
  openModal('modal-tv');
}

async function saveTVMember() {
  const id     = document.getElementById('tv-id').value;
  const ho_ten = document.getElementById('tv-hoten').value.trim();
  if (!ho_ten) { showToast('Vui lòng nhập họ và tên','error'); return; }
  const payload = {
    id:      id || undefined,
    ma_nv:   document.getElementById('tv-manv').value.trim()  || null,
    ho_ten,
    bo_phan: document.getElementById('tv-bophan').value.trim()|| null,
    xuong:   document.getElementById('tv-xuong').value        || null,
    chuc_vu: document.getElementById('tv-chucvu').value.trim()|| null,
    email:   document.getElementById('tv-email').value.trim() || null,
    vai_tro: document.getElementById('tv-vaitro').value,
  };
  try {
    const res = await fetch('/api/thanhvien.php',{
      method: id ? 'PUT' : 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(payload)
    });
    const d = await res.json();
    if (!d.success) throw new Error(d.error);
    showToast(id ? 'Đã cập nhật!' : 'Đã thêm thành viên!', 'success');
    closeModal('modal-tv');
    await loadThanhVien();
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

async function deleteTVMember(id, name) {
  if (!confirm(`Xóa thành viên "${name}"?\nHành động này không thể hoàn tác.`)) return;
  try {
    const res = await fetch('/api/thanhvien.php?id='+id,{method:'DELETE',credentials:'same-origin'});
    const d = await res.json();
    if (!d.success) throw new Error(d.error);
    showToast('Đã xóa thành viên','success');
    await loadThanhVien();
  } catch(e) { showToast(e.message||'Lỗi','error'); }
}

// ── Import Excel Thành Viên ───────────────────────────────────
let tvImportRows = [];

// Cột hợp lệ trong Excel (tên cột → field DB)
const TV_IMPORT_COLS = {
  'ma_nv':   'ma_nv',
  'mã nv':   'ma_nv',
  'ma nv':   'ma_nv',
  'ho_ten':  'ho_ten',
  'họ tên':  'ho_ten',
  'ho ten':  'ho_ten',
  'họ và tên': 'ho_ten',
  'bo_phan': 'bo_phan',
  'bộ phận': 'bo_phan',
  'bo phan': 'bo_phan',
  'xuong':   'xuong',
  'xưởng':   'xuong',
  'chuc_vu': 'chuc_vu',
  'chức vụ': 'chuc_vu',
  'chuc vu': 'chuc_vu',
  'email':   'email',
  'vai_tro': 'vai_tro',
  'vai trò': 'vai_tro',
  'vai tro': 'vai_tro',
};

const TV_ROLE_ALIAS = {
  'nhanvien': 'nhanvien', 'nhân viên': 'nhanvien', 'nhan vien': 'nhanvien', 'nv': 'nhanvien',
  'nguoikhacphuc': 'nguoikhacphuc', 'người khắc phục': 'nguoikhacphuc', 'khắc phục': 'nguoikhacphuc', 'kp': 'nguoikhacphuc',
  'quanly': 'quanly', 'quản lý': 'quanly', 'quan ly': 'quanly', 'ql': 'quanly',
};

function openTVImport() {
  resetTVImport();
  openModal('modal-tv-import');
}

function resetTVImport() {
  tvImportRows = [];
  document.getElementById('tv-imp-step1').style.display = '';
  document.getElementById('tv-imp-step2').style.display = 'none';
  document.getElementById('tv-imp-confirm-btn').style.display = 'none';
  document.getElementById('tv-imp-file').value = '';
  document.getElementById('tv-imp-errors').style.display = 'none';
  document.getElementById('tv-imp-preview-body').innerHTML = '';
}

async function handleTVImportFile(file) {
  if (!file) return;
  const ext = file.name.split('.').pop().toLowerCase();
  if (!['xlsx','xls'].includes(ext)) { showToast('Chỉ hỗ trợ file .xlsx hoặc .xls', 'error'); return; }

  // Load SheetJS nếu chưa có
  if (!window.XLSX) {
    await new Promise((res, rej) => {
      const s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js';
      s.onload = res; s.onerror = rej;
      document.head.appendChild(s);
    });
  }

  const buf  = await file.arrayBuffer();
  const wb   = XLSX.read(buf, { type: 'array' });
  const ws   = wb.Sheets[wb.SheetNames[0]];
  const raw  = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '' });

  if (!raw || raw.length < 2) { showToast('File không có dữ liệu (cần ít nhất 1 dòng tiêu đề + 1 dòng data)', 'error'); return; }

  // Map tiêu đề cột
  const headers = raw[0].map(h => (h + '').toLowerCase().trim());
  const colMap  = {};  // index → field
  headers.forEach((h, i) => { const f = TV_IMPORT_COLS[h]; if (f) colMap[i] = f; });

  if (!Object.values(colMap).includes('ho_ten')) {
    showToast('Không tìm thấy cột "ho_ten" hoặc "Họ tên" trong file', 'error'); return;
  }

  const rows = [];
  for (let i = 1; i < raw.length; i++) {
    const row = raw[i];
    if (row.every(c => (c + '').trim() === '')) continue; // bỏ dòng trống
    const obj = {};
    Object.entries(colMap).forEach(([idx, field]) => { obj[field] = (row[idx] + '').trim(); });

    // Chuẩn hóa vai_tro
    const vt = (obj.vai_tro || '').toLowerCase();
    obj.vai_tro = TV_ROLE_ALIAS[vt] || 'nhanvien';

    rows.push(obj);
  }

  if (!rows.length) { showToast('Không có dữ liệu hợp lệ trong file', 'error'); return; }

  tvImportRows = rows;

  // Kiểm tra trùng ma_nv trong chính file
  const seenMaNV = {};
  const rowStatus = rows.map((r, i) => {
    if (!r.ho_ten) return 'error';
    if (r.ma_nv) {
      if (seenMaNV[r.ma_nv]) return 'duplicate';
      seenMaNV[r.ma_nv] = true;
    }
    return 'ok';
  });

  // Hiển thị preview
  const vaitroChip = {
    nhanvien:      '<span style="font-size:10px;padding:2px 6px;background:#dbeafe;color:#1d4ed8;border-radius:4px;font-weight:600;">Nhân viên</span>',
    nguoikhacphuc: '<span style="font-size:10px;padding:2px 6px;background:#d1fae5;color:#065f46;border-radius:4px;font-weight:600;">Người KP</span>',
    quanly:        '<span style="font-size:10px;padding:2px 6px;background:#ffedd5;color:#9a3412;border-radius:4px;font-weight:600;">Quản lý</span>',
  };
  const statusChipImp = {
    ok:        '<span style="font-size:10px;padding:2px 6px;background:#d1fae5;color:#065f46;border-radius:4px;">✓ Hợp lệ</span>',
    duplicate: '<span style="font-size:10px;padding:2px 6px;background:#fef3c7;color:#92400e;border-radius:4px;">⚠ Trùng file</span>',
    error:     '<span style="font-size:10px;padding:2px 6px;background:#fef2f2;color:#991b1b;border-radius:4px;">✕ Thiếu tên</span>',
  };

  document.getElementById('tv-imp-preview-body').innerHTML = rows.map((r, i) => `
    <tr style="background:${rowStatus[i]==='error'?'#fef2f2':rowStatus[i]==='duplicate'?'#fffbeb':'white'};border-bottom:1px solid var(--border);">
      <td style="padding:5px 10px;color:var(--muted);">${i + 2}</td>
      <td style="padding:5px 10px;font-weight:600;">${r.ma_nv || '<span style="color:var(--muted);">—</span>'}</td>
      <td style="padding:5px 10px;">${r.ho_ten || '<span style="color:var(--red);">Thiếu!</span>'}</td>
      <td style="padding:5px 10px;">${r.bo_phan || '—'}</td>
      <td style="padding:5px 10px;">${r.xuong   || '—'}</td>
      <td style="padding:5px 10px;">${r.chuc_vu || '—'}</td>
      <td style="padding:5px 10px;font-size:10px;">${r.email   || '—'}</td>
      <td style="padding:5px 10px;">${vaitroChip[r.vai_tro] || r.vai_tro}</td>
      <td style="padding:5px 10px;text-align:center;">${statusChipImp[rowStatus[i]]}</td>
    </tr>`).join('');

  const errCount = rowStatus.filter(s => s !== 'ok').length;
  const okCount  = rows.length - rowStatus.filter(s => s === 'error').length;
  document.getElementById('tv-imp-summary').textContent =
    `📊 Đọc được ${rows.length} dòng · ${okCount} hợp lệ${errCount ? ' · ' + errCount + ' cần lưu ý' : ''}`;

  if (errCount > 0) {
    const errEl = document.getElementById('tv-imp-errors');
    errEl.innerHTML = '⚠️ <b>Lưu ý:</b> Các dòng thiếu họ tên sẽ bị bỏ qua khi import. Dòng trùng mã NV trong file sẽ lấy dòng đầu tiên.';
    errEl.style.display = '';
  }

  document.getElementById('tv-imp-step1').style.display = 'none';
  document.getElementById('tv-imp-step2').style.display = '';
  document.getElementById('tv-imp-confirm-btn').style.display = '';
}

async function confirmTVImport() {
  if (!tvImportRows.length) return;
  const btn  = document.getElementById('tv-imp-confirm-btn');
  const mode = document.getElementById('tv-imp-mode').value;
  btn.disabled = true; btn.textContent = 'Đang import...';
  try {
    const res = await fetch('/api/thanhvien.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'import', mode, rows: tvImportRows }),
    });
    const d = await res.json();
    if (!d.success) throw new Error(d.error || 'Import thất bại');

    const msg = `✅ Import xong! Thêm mới: ${d.inserted} · Cập nhật: ${d.updated} · Bỏ qua: ${d.skipped}`;
    showToast(msg, 'success', 5000);
    closeModal('modal-tv-import');
    await loadThanhVien();
  } catch(e) {
    showToast(e.message || 'Lỗi import', 'error');
    btn.disabled = false;
    btn.innerHTML = '<svg viewBox="0 0 24 24" width="15" height="15" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg> Xác nhận Import';
  }
}

function downloadTVTemplate() {
  // Tải trực tiếp từ server — PHP tạo file XLSX chuẩn có đầy đủ styling
  const a = document.createElement('a');
  a.href = '/api/template.php?type=thanhvien';
  a.download = 'mau_import_thanhvien.xlsx';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  showToast('Đang tải file mẫu Excel...', 'success');
}

// ── Responsive ───────────────────────────────────────────────
function fixChartGrids() {
  const w = window.innerWidth;
  document.querySelectorAll('.chart-grid-2').forEach(el=>{ el.style.gridTemplateColumns = w < 700 ? '1fr' : '1fr 1fr'; });
  document.querySelectorAll('.ws-chart-grid').forEach(el=>{ el.style.gridTemplateColumns = w < 600 ? '1fr' : '1fr 1fr'; });
}
window.addEventListener('resize', fixChartGrids);
fixChartGrids();
