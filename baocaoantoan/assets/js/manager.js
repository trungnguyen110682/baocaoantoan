/* An Toàn MMB — Manager Dashboard JS */

let allReports = [];
let allGemba   = [];
let srStatus   = '';
let srPage     = 1;
const PAGE_SIZE = 20;

let srFlType = 'all', srFlYear = '', srFlMonth = '', srFlWeek = '', srTableTab = 'ws';
let gbFlType = 'all', gbFlMonth = '', gbFlWeek = '';

window.addEventListener('DOMContentLoaded', () => {
  setInterval(() => { document.getElementById('nav-time').textContent = new Date().toLocaleTimeString('vi-VN'); }, 1000);
  loadAll();
});

async function loadAll() {
  const [bcRes, gbRes] = await Promise.all([
    fetch('/api/baocao.php').then(r => r.json()),
    fetch('/api/gemba.php').then(r => r.json()),
  ]);
  allReports = Array.isArray(bcRes) ? bcRes : [];
  allGemba   = Array.isArray(gbRes) ? gbRes : [];

  initYearSelectors();
  applyFilters();
  renderGembaPage();

  const ov = allReports.filter(r => r.is_overdue).length;
  const alert = document.getElementById('overdue-alert');
  if (alert && ov > 0) {
    alert.style.display = 'flex';
    document.getElementById('overdue-msg').textContent = `⚠ Có ${ov} báo cáo quá hạn cần xử lý ngay!`;
  }
}

// ── ISO week helpers ──────────────────────────────────────────
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

// ── Year/month/week selectors ─────────────────────────────────
function initYearSelectors() {
  const years = [...new Set(allReports.map(r => r.created_at ? r.created_at.slice(0, 4) : null).filter(Boolean))].sort().reverse();
  const thisYear  = new Date().getFullYear().toString();
  const thisMonth = (new Date().getMonth() + 1).toString();
  const thisWeek  = getISOWeek(new Date()).toString();
  if (!years.length) years.push(thisYear);

  const monthOpts = Array.from({ length: 12 }, (_, i) => `<option value="${i+1}">Tháng ${i+1}</option>`).join('');
  const weekOpts  = Array.from({ length: 52 }, (_, i) => `<option value="${i+1}">Tuần ${i+1}</option>`).join('');
  const yearOptsStr = years.map(y => `<option value="${y}">${y}</option>`).join('');

  ['sr-fl-year-m', 'sr-fl-year-w'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.innerHTML = yearOptsStr; el.value = thisYear; }
  });
  const srmo = document.getElementById('sr-fl-month');
  if (srmo) { srmo.innerHTML = monthOpts; srmo.value = thisMonth; }
  const srwk = document.getElementById('sr-fl-week');
  if (srwk) { srwk.innerHTML = weekOpts; srwk.value = thisWeek; }

  // Gemba year selectors
  const gbYears = [...new Set(allGemba.map(g => g.created_at ? g.created_at.slice(0, 4) : null).filter(Boolean))].sort().reverse();
  if (!gbYears.length) gbYears.push(thisYear);
  const gbYearOpts = gbYears.map(y => `<option value="${y}">${y}</option>`).join('');
  const gbFLMonth = document.getElementById('gb-fl-month');
  if (gbFLMonth) { gbFLMonth.innerHTML = monthOpts; gbFLMonth.value = thisMonth; }
  const gbFLWeek  = document.getElementById('gb-fl-week');
  if (gbFLWeek)  { gbFLWeek.innerHTML = weekOpts; gbFLWeek.value = thisWeek; }

  srFlYear = thisYear; srFlMonth = thisMonth; srFlWeek = thisWeek;
  gbFlMonth = thisMonth; gbFlWeek = thisWeek;
}

// ── Safety Report time filter ─────────────────────────────────
function setSRTimeType(type) {
  srFlType = type;
  ['all', 'month', 'week'].forEach(t => {
    const btn = document.getElementById('sr-btn-' + t);
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
      if (srFlYear  && d.getFullYear().toString() !== srFlYear) return false;
      if (srFlMonth && (d.getMonth() + 1).toString() !== srFlMonth) return false;
    } else if (srFlType === 'week') {
      const d = new Date(r.created_at);
      if (wYear   && getISOWeekYear(d).toString() !== wYear) return false;
      if (srFlWeek && getISOWeek(d).toString() !== srFlWeek) return false;
    }
    return true;
  });
}

let filteredReports = [];
function applyFilters() {
  const xuong  = document.getElementById('sr-fl-ws') ? document.getElementById('sr-fl-ws').value : '';
  const search = document.getElementById('sr-search') ? document.getElementById('sr-search').value.toLowerCase() : '';

  const base = getSRTimeFiltered(allReports);
  filteredReports = base.filter(r => {
    if (xuong && r.xuong !== xuong) return false;
    if (search && !JSON.stringify(r).toLowerCase().includes(search)) return false;
    if (srStatus === 'overdue') return r.is_overdue;
    if (srStatus && r.status !== srStatus) return false;
    return true;
  });

  const c = { all: base.length, pending: 0, approved: 0, fixed: 0, rejected: 0, overdue: 0 };
  base.forEach(r => { c[r.status] = (c[r.status] || 0) + 1; if (r.is_overdue) c.overdue++; });
  Object.keys(c).forEach(k => { const el = document.getElementById('tc-' + k); if (el) el.textContent = c[k]; });

  const setEl = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  setEl('sr-s-total',   base.length);
  setEl('sr-s-chuakp',  base.filter(r => r.status === 'pending' || r.status === 'approved').length);
  setEl('sr-s-dakp',    base.filter(r => r.status === 'fixed').length);
  setEl('sr-s-overdue', base.filter(r => r.is_overdue).length);

  renderSRSummaryTable(base);
  srPage = 1;
  renderSRList();
  const sub = document.getElementById('sr-count-sub');
  if (sub) sub.textContent = `${filteredReports.length} / ${base.length} báo cáo`;
  const badge = document.getElementById('sb-pending-cnt');
  if (badge) badge.textContent = allReports.filter(r => r.status === 'pending' || r.status === 'approved').length;
}

function switchSRTab(status, btn) {
  srStatus = status;
  document.querySelectorAll('#sr-tabs .itab').forEach(b => b.classList.remove('on'));
  btn.classList.add('on');
  applyFilters();
}

// ── SR Summary Table ──────────────────────────────────────────
function switchSRTableTab(tab) {
  srTableTab = tab;
  document.getElementById('sr-tbl-btn-ws').className = 'btn btn-sm ' + (tab === 'ws' ? 'btn-navy' : 'btn-ghost');
  document.getElementById('sr-tbl-btn-np').className = 'btn btn-sm ' + (tab === 'np' ? 'btn-navy' : 'btn-ghost');
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
    if (!map[key]) map[key] = { total: 0, chuakp: 0, dakp: 0, overdue: 0 };
    map[key].total++;
    if (r.status === 'fixed') map[key].dakp++;
    else map[key].chuakp++;
    if (r.is_overdue) map[key].overdue++;
  });
  const rows = Object.entries(map).sort((a, b) => b[1].total - a[1].total);
  const tbody = document.getElementById('sr-summary-body');
  if (!tbody) return;
  if (!rows.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:12px;">Không có dữ liệu</td></tr>'; return; }
  tbody.innerHTML = rows.map(([name, d]) => {
    const rate = d.total ? Math.round(d.dakp / d.total * 100) : 0;
    const barColor = rate >= 80 ? 'var(--green)' : rate >= 50 ? 'var(--orange)' : 'var(--red)';
    return `<tr>
      <td><b>${name}</b></td>
      <td style="text-align:center;font-weight:700;">${d.total}</td>
      <td style="text-align:center;color:var(--orange);font-weight:600;">${d.chuakp || 0}</td>
      <td style="text-align:center;color:var(--green);font-weight:600;">${d.dakp || 0}</td>
      <td style="text-align:center;color:var(--red);font-weight:600;">${d.overdue || 0}</td>
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

// ── Safety Report List ────────────────────────────────────────
function renderSRList() {
  const start = (srPage - 1) * PAGE_SIZE;
  const page  = filteredReports.slice(start, start + PAGE_SIZE);
  const pages = Math.ceil(filteredReports.length / PAGE_SIZE);
  if (!page.length) {
    document.getElementById('sr-list').innerHTML = '<div class="empty"><div class="empty-icon">📭</div><p>Không có báo cáo nào</p></div>';
    document.getElementById('sr-pagination').innerHTML = '';
    return;
  }
  document.getElementById('sr-list').innerHTML = page.map(r => {
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
    const canFix = r.status !== 'fixed' && r.status !== 'rejected';
    return `
    <div class="rcard">
      <div class="rcard-top" onclick="showDetail('${r.id}')" style="cursor:pointer;">
        <div><div class="rcard-id">${r.id}</div><div class="rcard-meta">${r.ho_ten || '—'} · ${r.bo_phan || '—'} · ${r.xuong || '—'}</div></div>
        ${statusChip(r.status)}
      </div>
      <div class="rcard-body" onclick="showDetail('${r.id}')" style="cursor:pointer;">${r.noi_dung || '—'}</div>
      ${imgHtml}
      <div class="rcard-footer">
        <span style="font-size:11px;color:var(--muted);">${r.vi_tri || ''}</span>
        ${r.is_overdue ? '<span class="chip chip-red">⚠ Quá hạn</span>' : ''}
        ${r.deadline ? `<span style="font-size:11px;color:var(--muted);">Hạn: ${fmtDateOnly(r.deadline)}</span>` : ''}
        <div style="margin-left:auto;display:flex;gap:6px;flex-wrap:wrap;">
          <button class="btn btn-ghost btn-xs" onclick="showDetail('${r.id}')">🔍 Chi tiết</button>
          ${canFix ? `<button class="btn btn-orange btn-xs" onclick="openKhacPhuc('${r.id}')">🔧 Khắc phục</button>` : ''}
        </div>
      </div>
    </div>`;
  }).join('');
  buildPagination('sr-pagination', srPage, filteredReports.length, pages, `(p) => { srPage=p; renderSRList(); window.scrollTo(0,200); }`);
}

function showDetail(id) {
  const r = allReports.find(x => x.id === id);
  if (!r) return;
  document.getElementById('md-title').textContent = r.id;
  document.getElementById('md-body').innerHTML = `
    <div class="detail-row"><span class="detail-lbl">Người BC</span><span class="detail-val"><b>${r.ho_ten || '—'}</b> (${r.ma_nv || '—'})</span></div>
    <div class="detail-row"><span class="detail-lbl">Bộ phận</span><span class="detail-val">${r.bo_phan || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Xưởng</span><span class="detail-val">${r.xuong || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Vị trí</span><span class="detail-val">${r.vi_tri || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Mô tả</span><span class="detail-val" style="white-space:pre-wrap;">${r.noi_dung || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Trạng thái</span><span class="detail-val">${statusChip(r.status)}</span></div>
    <div class="detail-row"><span class="detail-lbl">Phân loại</span><span class="detail-val">${r.category || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Golden Rule</span><span class="detail-val">${r.golden_rule || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Mức độ</span><span class="detail-val">${r.severity || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Khu vực</span><span class="detail-val">${r.area || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Người KP</span><span class="detail-val">${r.nguoi_kp || '—'} ${r.nkp_chuc_vu ? '(' + r.nkp_chuc_vu + ')' : ''}</span></div>
    <div class="detail-row"><span class="detail-lbl">Hạn KP</span><span class="detail-val">${fmtDateOnly(r.deadline) || '—'} ${r.days_remaining != null ? '(' + r.days_remaining + ' ngày)' : ''}</span></div>
    <div class="detail-row"><span class="detail-lbl">Ngày duyệt</span><span class="detail-val">${fmtDate(r.approved_date) || '—'}</span></div>
    ${r.status === 'fixed' ? `<hr style="margin:12px 0;border-color:var(--border);">
    <div style="font-size:11px;font-weight:700;color:var(--green);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">KẾT QUẢ KHẮC PHỤC</div>
    <div class="detail-row"><span class="detail-lbl">Người KP</span><span class="detail-val">${r.fixer || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Thời gian KP</span><span class="detail-val">${fmtDate(r.fix_time) || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Nội dung KP</span><span class="detail-val">${r.fix_note || '—'}</span></div>
    ` : ''}
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
  if (r.status !== 'fixed' && r.status !== 'rejected') {
    footer.innerHTML += `<button class="btn btn-orange btn-sm" onclick="closeModal('modal-detail');openKhacPhuc('${r.id}')">🔧 Khắc phục</button>`;
  }
  openModal('modal-detail');
}

// ── Gemba ─────────────────────────────────────────────────────
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
    if ((g.bo_phan || '').trim().toUpperCase() === 'SHE') return false;
    if (gbFlType === 'month' && gbFlMonth) {
      const d = new Date(g.created_at);
      if (d.getFullYear() !== year || (d.getMonth() + 1).toString() !== gbFlMonth) return false;
    } else if (gbFlType === 'week' && gbFlWeek) {
      const d = new Date(g.created_at);
      if (d.getFullYear() !== year || getISOWeek(d).toString() !== gbFlWeek) return false;
    }
    if (search) {
      const hay = [g.ho_ten, g.ma_nv, g.bo_phan, g.xuong].map(v => (v || '').toLowerCase()).join(' ');
      if (!hay.includes(search)) return false;
    }
    return true;
  });
}

function renderGembaPage() {
  const data = getGembaFiltered();
  document.getElementById('gb-st0').textContent = data.length;
  document.getElementById('gb-st1').textContent = new Set(data.map(g => g.ma_nv)).size;
  document.getElementById('gb-st2').textContent = data.filter(g => g.co_su_co).length;

  const wsMap = {};
  data.forEach(g => { const w = g.xuong || 'Khác'; wsMap[w] = (wsMap[w] || 0) + 1; });
  const sorted = Object.entries(wsMap).sort((a, b) => b[1] - a[1]);
  drawBarH('gemba-ws-chart', sorted.map(e => e[0]), sorted.map(e => e[1]), COLORS.denim);

  const nvMap = {};
  data.forEach(g => {
    const k = g.ma_nv || '?';
    if (!nvMap[k]) nvMap[k] = { ho_ten: g.ho_ten, ma_nv: g.ma_nv, bo_phan: g.bo_phan, luot: 0, suCo: 0 };
    nvMap[k].luot++;
    if (g.co_su_co) nvMap[k].suCo++;
  });
  document.getElementById('gb-person-tbody').innerHTML = Object.values(nvMap)
    .sort((a, b) => b.luot - a.luot).slice(0, 20)
    .map((v, i) => `<tr><td>${i + 1}</td><td><b>${v.ho_ten || '—'}</b></td><td style="font-size:11px;">${v.ma_nv || '—'}</td><td>${v.bo_phan || '—'}</td><td><span class="chip chip-blue">${v.luot}</span></td><td>${v.suCo ? `<span class="chip chip-red">${v.suCo}</span>` : '—'}</td></tr>`).join('');

  renderGembaList();
}

function renderGembaList() {
  const xuong = document.getElementById('gb-filter-xuong') ? document.getElementById('gb-filter-xuong').value : '';
  let data = getGembaFiltered();
  if (xuong) data = data.filter(g => g.xuong === xuong);
  const ck = (v) => v === 'Đạt'
    ? '<span class="chip chip-green" style="font-size:10px;padding:2px 6px;">Đạt</span>'
    : '<span class="chip chip-red" style="font-size:10px;padding:2px 6px;">KĐ</span>';
  document.getElementById('gb-list-body').innerHTML = data.length ? data.map(g => `
    <tr style="cursor:pointer;" onclick="showGembaDetail('${g.id}')">
      <td class="tbl-id" style="font-size:10px;">${g.id}</td>
      <td><b>${g.khu_vuc || '—'}</b><div class="tbl-sub">${g.phu_trach || ''}</div></td>
      <td>${g.xuong || '—'}</td>
      <td>${g.ho_ten || '—'}<div class="tbl-sub">${g.ma_nv || ''}</div></td>
      <td style="font-size:11px;">${g.bo_phan || '—'}</td>
      <td>${ck(g.interlock)}</td>
      <td>${ck(g.checan)}</td>
      <td>${ck(g.tudien)}</td>
      <td>${ck(g.s5_moitruong)}</td>
      <td>${g.co_su_co ? '<span class="chip chip-red">Có</span>' : '—'}</td>
      <td style="font-size:11px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${(g.ghi_chu || '').replace(/"/g, '&quot;')}">${g.ghi_chu || '—'}</td>
      <td style="font-size:11px;color:var(--muted);white-space:nowrap;">${fmtDate(g.created_at)}</td>
    </tr>`).join('')
    : '<tr><td colspan="12"><div class="empty"><p>Không có dữ liệu</p></div></td></tr>';
}

function showGembaDetail(id) {
  const g = allGemba.find(x => x.id === id);
  if (!g) return;
  const ck = (v) => v === 'Đạt' ? '<span class="chip chip-green">Đạt</span>' : '<span class="chip chip-red">Không đạt</span>';
  document.getElementById('md-gb-body').innerHTML = `
    <div class="detail-row"><span class="detail-lbl">ID</span><span class="detail-val"><b>${g.id}</b></span></div>
    <div class="detail-row"><span class="detail-lbl">Mã QR</span><span class="detail-val">${g.ma_qr || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Xưởng</span><span class="detail-val">${g.xuong || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Khu vực</span><span class="detail-val">${g.khu_vuc || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Phụ trách</span><span class="detail-val">${g.phu_trach || '—'}</span></div>
    <div class="detail-row"><span class="detail-lbl">Người KT</span><span class="detail-val"><b>${g.ho_ten || '—'}</b> (${g.ma_nv || '—'})</span></div>
    <div class="detail-row"><span class="detail-lbl">Bộ phận</span><span class="detail-val">${g.bo_phan || '—'}</span></div>
    <hr style="margin:12px 0;border-color:var(--border);">
    <div style="font-size:11px;font-weight:700;color:var(--navy);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">CHECKLIST</div>
    <div class="detail-row"><span class="detail-lbl">Interlock</span><span class="detail-val">${ck(g.interlock)}</span></div>
    <div class="detail-row"><span class="detail-lbl">Che chắn</span><span class="detail-val">${ck(g.checan)}</span></div>
    <div class="detail-row"><span class="detail-lbl">Tủ điện</span><span class="detail-val">${ck(g.tudien)}</span></div>
    <div class="detail-row"><span class="detail-lbl">S5 Môi trường</span><span class="detail-val">${ck(g.s5_moitruong)}</span></div>
    <div class="detail-row"><span class="detail-lbl">Sự cố</span><span class="detail-val">${g.co_su_co ? '<span class="chip chip-red">⚠ Có sự cố</span>' : '<span class="chip chip-green">Không có</span>'}</span></div>
    ${g.ghi_chu ? `<div class="detail-row"><span class="detail-lbl">Ghi chú</span><span class="detail-val">${g.ghi_chu}</span></div>` : ''}
    <div class="detail-row"><span class="detail-lbl">Thời gian</span><span class="detail-val">${fmtDate(g.created_at)}</span></div>
  `;
  openModal('modal-gb-detail');
}

// ── Page switching ────────────────────────────────────────────
function switchPage(pageId, btn) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.sb-item').forEach(b => b.classList.remove('active'));
  document.getElementById('page-' + pageId).classList.add('active');
  btn.classList.add('active');
  const sb = document.querySelector('.sidebar');
  if (sb) sb.classList.remove('open');
  const ov = document.getElementById('sb-overlay');
  if (ov) ov.classList.remove('on');
}

// ── Khắc phục — lấy link token rồi mở tab ────────────────────
async function openKhacPhuc(id) {
  try {
    const res = await fetch(`/api/baocao.php?id=${encodeURIComponent(id)}&token=1`);
    const d = await res.json();
    if (!d.link) throw new Error('Không lấy được link');
    window.open(d.link, '_blank');
  } catch (e) {
    showToast('Không mở được trang khắc phục', 'error');
  }
}

// ── Export ────────────────────────────────────────────────────
function exportSafetyCSV() {
  exportCSV(filteredReports, 'baocao-loc-' + new Date().toISOString().slice(0, 10) + '.csv');
}
