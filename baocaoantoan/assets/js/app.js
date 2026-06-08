/* An Toàn MMB — Shared JS */

// ── Toast ──────────────────────────────────────────────────
function showToast(msg, type = 'success', duration = 3500) {
  let t = document.getElementById('toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'toast';
    t.className = 'toast';
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.className = 'toast on toast-' + type;
  clearTimeout(t._timer);
  t._timer = setTimeout(() => { t.className = 'toast'; }, duration);
}

// ── Modal ──────────────────────────────────────────────────
function openModal(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.add('on'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) { el.classList.remove('on'); document.body.style.overflow = ''; }
}
// Close on overlay click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-ov')) {
    e.target.classList.remove('on');
    document.body.style.overflow = '';
  }
});

// ── Lightbox ────────────────────────────────────────────────
function openLightbox(src) {
  let lb = document.getElementById('lightbox');
  if (!lb) {
    lb = document.createElement('div');
    lb.id = 'lightbox';
    lb.innerHTML = '<button id="lightbox-close" onclick="closeLightbox()">&times;</button><img id="lb-img" src="" alt="">';
    document.body.appendChild(lb);
  }
  document.getElementById('lb-img').src = src;
  lb.classList.add('on');
}
function closeLightbox() {
  const lb = document.getElementById('lightbox');
  if (lb) lb.classList.remove('on');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeLightbox(); } });

// ── Image Upload Helper ─────────────────────────────────────
function initImageUpload({ areaId, cameraId, libId, previewId, inputId, urlField }) {
  const area     = document.getElementById(areaId);
  const cameraBtn= document.getElementById(cameraId);
  const libBtn   = document.getElementById(libId);
  const preview  = document.getElementById(previewId);
  const fileInput= document.getElementById(inputId);

  function handleFile(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      const img = preview.querySelector('img');
      img.src = e.target.result;
      preview.style.display = 'block';
      area.classList.add('has-file');
      area.classList.remove('req-err');
      if (urlField) document.getElementById(urlField).value = '';
    };
    reader.readAsDataURL(file);
  }

  if (cameraBtn) {
    cameraBtn.addEventListener('click', () => {
      fileInput.setAttribute('capture', 'environment');
      fileInput.accept = 'image/*';
      fileInput.click();
    });
  }
  if (libBtn) {
    libBtn.addEventListener('click', () => {
      fileInput.removeAttribute('capture');
      fileInput.accept = 'image/*';
      fileInput.click();
    });
  }

  fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) handleFile(fileInput.files[0]);
  });

  // Drag & drop
  area.addEventListener('dragover', e => { e.preventDefault(); area.style.borderColor = 'var(--denim)'; });
  area.addEventListener('dragleave', () => { area.style.borderColor = ''; });
  area.addEventListener('drop', e => {
    e.preventDefault(); area.style.borderColor = '';
    if (e.dataTransfer.files[0]) { fileInput.files = e.dataTransfer.files; handleFile(e.dataTransfer.files[0]); }
  });

  // Remove
  const xbtn = preview.querySelector('.xbtn');
  if (xbtn) {
    xbtn.addEventListener('click', () => {
      fileInput.value = '';
      preview.style.display = 'none';
      preview.querySelector('img').src = '';
      area.classList.remove('has-file');
      if (urlField) document.getElementById(urlField).value = '';
    });
  }
}

// ── Employee Lookup ─────────────────────────────────────────
async function lookupEmployee(manv, { nameId, deptId, xuongId, badgeId } = {}) {
  if (!manv || manv.length < 5) return;
  try {
    const res = await fetch('/api/thanhvien.php?action=lookup&manv=' + encodeURIComponent(manv));
    const d = await res.json();
    const badge = document.getElementById(badgeId);
    if (d.found) {
      if (nameId)  document.getElementById(nameId).value  = d.data.ho_ten  || '';
      if (deptId)  document.getElementById(deptId).value  = d.data.bo_phan || '';
      if (xuongId) document.getElementById(xuongId).value = d.data.xuong   || '';
      if (badge) { badge.className = 'lookup-badge found'; badge.textContent = '✓ ' + d.data.ho_ten; }
    } else {
      if (nameId)  document.getElementById(nameId).value  = '';
      if (deptId)  document.getElementById(deptId).value  = '';
      if (xuongId) document.getElementById(xuongId).value = '';
      if (badge) { badge.className = 'lookup-badge notfound'; badge.textContent = '✗ Không tìm thấy mã NV'; }
    }
  } catch(e) { console.error('Lookup error', e); }
}

// ── Status Chip ─────────────────────────────────────────────
function statusChip(status) {
  const map = {
    pending:  ['chip-yellow', 'Chờ duyệt'],
    approved: ['chip-blue',   'Đã duyệt'],
    rejected: ['chip-red',    'Từ chối'],
    fixed:    ['chip-green',  'Đã KP'],
  };
  const [cls, lbl] = map[status] || ['chip-muted', status];
  return `<span class="chip ${cls}">${lbl}</span>`;
}

// ── Format date ─────────────────────────────────────────────
function fmtDate(dt) {
  if (!dt) return '—';
  const d = new Date(dt.replace(' ', 'T'));
  if (isNaN(d)) return dt;
  return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
}
function fmtDateOnly(dt) {
  if (!dt) return '—';
  const d = new Date(dt);
  if (isNaN(d)) return dt;
  return d.toLocaleDateString('vi-VN');
}

// ── Pagination helper ────────────────────────────────────────
function buildPagination(containerId, current, total, pages, onPage) {
  const c = document.getElementById(containerId);
  if (!c) return;
  if (pages <= 1) { c.innerHTML = ''; return; }
  let html = `<span class="pg-info">Trang ${current}/${pages} (${total} bản ghi)</span>`;
  html += `<button class="pg-btn" ${current===1?'disabled':''} onclick="(${onPage})(${current-1})">‹</button>`;
  for (let i = 1; i <= pages; i++) {
    if (i===1 || i===pages || Math.abs(i-current)<=2) {
      html += `<button class="pg-btn ${i===current?'active':''}" onclick="(${onPage})(${i})">${i}</button>`;
    } else if (Math.abs(i-current)===3) {
      html += `<span class="pg-info">…</span>`;
    }
  }
  html += `<button class="pg-btn" ${current===pages?'disabled':''} onclick="(${onPage})(${current+1})">›</button>`;
  c.innerHTML = html;
}

// ── Sidebar toggle (mobile) ──────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const sb   = document.querySelector('.sidebar');
  const ov   = document.getElementById('sb-overlay');
  const toggle = document.getElementById('sb-toggle');
  if (toggle && sb) {
    toggle.addEventListener('click', () => {
      sb.classList.toggle('open');
      if (ov) ov.classList.toggle('on');
    });
  }
  if (ov) {
    ov.addEventListener('click', () => {
      if (sb) sb.classList.remove('open');
      ov.classList.remove('on');
    });
  }
});

// ── Export CSV ───────────────────────────────────────────────
function exportCSV(data, filename) {
  if (!data.length) { showToast('Không có dữ liệu để xuất', 'error'); return; }
  const keys = Object.keys(data[0]);
  const rows = [keys.join(',')];
  data.forEach(r => {
    rows.push(keys.map(k => {
      const v = (r[k] ?? '').toString().replace(/"/g, '""');
      return v.includes(',') || v.includes('"') || v.includes('\n') ? `"${v}"` : v;
    }).join(','));
  });
  const blob = new Blob(['﻿' + rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = filename;
  a.click();
}
