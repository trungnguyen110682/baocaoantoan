<?php
require_once __DIR__ . '/../config/app.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Báo Cáo An Toàn — MMB</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav>
  <div class="nav-brand">
    <div class="nav-logo">
      <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
    </div>
    <span class="nav-title">Báo Cáo An Toàn</span>
    <span class="nav-badge">MMB</span>
  </div>
</nav>

<div class="form-wrap">

  <!-- SUCCESS STATE -->
  <div id="success-state" style="display:none;">
    <div class="success-card">
      <div class="success-hd">
        <div class="success-icon">
          <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
        </div>
        <h2>Báo Cáo Đã Gửi!</h2>
        <p>Cảm ơn bạn đã báo cáo sự cố an toàn</p>
      </div>
      <div class="success-bd">
        <div class="sc-id" id="sc-id">—</div>
        <div class="sc-field"><div class="lbl">Họ và tên</div><div class="val" id="sc-name">—</div></div>
        <div class="sc-field"><div class="lbl">Xưởng</div><div class="val" id="sc-ws">—</div></div>
        <div class="sc-field"><div class="lbl">Vị trí</div><div class="val" id="sc-vitri">—</div></div>
        <div class="sc-field"><div class="lbl">Nội dung</div><div class="val" id="sc-nd">—</div></div>
        <div id="sc-img-wrap" style="display:none;margin-top:12px;">
          <div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Hình ảnh</div>
          <img id="sc-img" src="" alt="" style="width:100%;max-height:200px;object-fit:cover;border-radius:9px;border:1px solid var(--border);" onclick="openLightbox(this.src)">
        </div>
        <button class="btn btn-navy btn-full" style="margin-top:18px;" onclick="resetForm()">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l7.59-7.59L21 8l-9 9z"/></svg>
          Gửi Báo Cáo Mới
        </button>
      </div>
    </div>
  </div>

  <!-- FORM -->
  <div id="form-state">
    <div class="fcard">
      <div class="fhd">
        <div class="fhi">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8 13h8v2H8v-2zm0 4h5v2H8v-2zm0-8h3v2H8V9z"/></svg>
        </div>
        <div>
          <h2>BÁO CÁO SỰ CỐ AN TOÀN</h2>
          <p>Điền đầy đủ thông tin để gửi báo cáo</p>
        </div>
      </div>
      <div class="fbd">

        <!-- Mã NV -->
        <div class="fs" id="manv-wrap">
          <label class="fl" for="manv">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            Mã Nhân Viên <span class="req" id="manv-req">*</span>
          </label>
          <input type="text" id="manv" class="fc" placeholder="VD: 17MB01275" maxlength="15" style="text-transform:uppercase;"
            oninput="onMaNVInput(this.value)">
          <div id="manv-badge" class="lookup-badge"></div>
          <div class="ferr" id="err-manv">Vui lòng nhập mã nhân viên hợp lệ</div>
        </div>

        <!-- Tên + Bộ phận -->
        <div class="frow">
          <div class="fs">
            <label class="fl">Họ và tên</label>
            <input type="text" id="hoten" class="fc" readonly placeholder="Tự động điền">
          </div>
          <div class="fs">
            <label class="fl">Bộ phận</label>
            <input type="text" id="bophan" class="fc" readonly placeholder="Tự động điền">
          </div>
        </div>

        <!-- Xưởng -->
        <div class="fs">
          <label class="fl" for="xuong">Xưởng <span class="req" id="xuong-req">*</span></label>
          <select id="xuong" class="fc">
            <option value="">-- Chọn xưởng --</option>
            <option>Xuong F0</option>
            <option>Xuong F1</option>
            <option>Xuong F2</option>
            <option>Xuong F3</option>
            <option>Kho van</option>
            <option>Utility</option>
            <option>ADM</option>
          </select>
          <div class="ferr" id="err-xuong">Vui lòng chọn xưởng</div>
        </div>

        <!-- Vị trí -->
        <div class="fs">
          <label class="fl" for="vitri">Vị trí sự cố <span class="req" id="vitri-req">*</span></label>
          <input type="text" id="vitri" class="fc" placeholder="VD: Khu A, dây chuyền số 3">
          <div class="ferr" id="err-vitri">Vui lòng nhập vị trí</div>
        </div>

        <!-- Nội dung -->
        <div class="fs">
          <label class="fl" for="noidung">Mô tả sự cố <span class="req">*</span></label>
          <textarea id="noidung" class="fc" rows="4" placeholder="Mô tả chi tiết tình trạng không an toàn..."></textarea>
          <div class="ferr" id="err-noidung">Vui lòng mô tả sự cố</div>
        </div>

        <!-- Hình ảnh -->
        <div class="fs">
          <label class="fl">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M20 5h-3.17L15 3H9L7.17 5H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-8 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5z"/></svg>
            Hình ảnh <span class="req">*</span>
          </label>
          <div class="uarea" id="uarea">
            <div class="ubtn-row">
              <button type="button" class="ubtn" id="btn-camera">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 5h-3.17L15 3H9L7.17 5H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-8 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.65 0-3 1.35-3 3s1.35 3 3 3 3-1.35 3-3-1.35-3-3-3z"/></svg>
                Chụp ảnh
              </button>
              <button type="button" class="ubtn" id="btn-lib">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                Chọn ảnh
              </button>
            </div>
            <p>Kéo thả hoặc nhấn nút bên trên để chọn ảnh (.jpg, .png)</p>
            <input type="file" id="file-input" accept="image/*" style="display:none">
            <div id="img-preview" class="iprev">
              <img src="" alt="preview">
              <button type="button" class="xbtn" title="Xóa ảnh">&times;</button>
            </div>
          </div>
          <div class="ferr" id="err-img">Vui lòng chọn hình ảnh</div>
        </div>

        <button class="btn btn-or btn-full" id="submit-btn" onclick="submitBC()" style="padding:13px;font-size:14px;margin-top:4px;">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
          Gửi Báo Cáo
        </button>

      </div>
    </div>
  </div><!-- /form-state -->

</div><!-- /form-wrap -->

<div class="toast" id="toast"></div>

<script src="/assets/js/app.js"></script>
<script>
let foundEmployee = false;
let fromGemba = false;

// Pre-fill từ Gemba nếu có URL params
(function prefillFromGemba() {
  const p = new URLSearchParams(location.search);
  const manv = p.get('manv'), hoten = p.get('hoten'), bophan = p.get('bophan'),
        xuong = p.get('xuong'), vitri = p.get('vitri');
  if (!manv) return;

  fromGemba = true;
  foundEmployee = true;

  const manvEl = document.getElementById('manv');
  manvEl.value = manv;
  manvEl.readOnly = true;
  document.getElementById('manv-badge').style.display = 'none';
  document.getElementById('manv-req').style.display = 'none';

  document.getElementById('hoten').value = hoten || '';
  document.getElementById('bophan').value = bophan || '';

  if (xuong) {
    const sel = document.getElementById('xuong');
    // Tìm option khớp (case-insensitive)
    const opt = Array.from(sel.options).find(o => o.value.toLowerCase() === xuong.toLowerCase());
    if (opt) { opt.selected = true; }
    else {
      // Thêm option mới nếu chưa có
      const newOpt = new Option(xuong, xuong, true, true);
      sel.appendChild(newOpt);
    }
    sel.disabled = true;
    document.getElementById('xuong-req').style.display = 'none';
  }

  if (vitri) {
    const vitriEl = document.getElementById('vitri');
    vitriEl.value = vitri;
    vitriEl.readOnly = true;
    document.getElementById('vitri-req').style.display = 'none';
  }
})();

// Init image upload
initImageUpload({ areaId:'uarea', cameraId:'btn-camera', libId:'btn-lib', previewId:'img-preview', inputId:'file-input' });

// MaNV input debounce
let lookupTimer;
function onMaNVInput(v) {
  foundEmployee = false;
  document.getElementById('manv-badge').className = 'lookup-badge';
  clearTimeout(lookupTimer);
  if (v.length >= 5) {
    lookupTimer = setTimeout(() => {
      lookupEmployee(v, { nameId:'hoten', deptId:'bophan', badgeId:'manv-badge' });
    }, 500);
  }
}

// Update foundEmployee flag when badge changes
const badge = document.getElementById('manv-badge');
const obs = new MutationObserver(() => {
  foundEmployee = badge.classList.contains('found');
});
obs.observe(badge, { attributes: true, attributeFilter: ['class'] });

async function submitBC() {
  let ok = true;
  const show = (id, show) => { const el=document.getElementById(id); if(el){ el.className='ferr'+(show?' on':''); } };
  const fc   = (id) => { const el=document.getElementById(id); if(el) el.classList.add('err'); };
  const fc2  = (id) => { const el=document.getElementById(id); if(el) el.classList.remove('err'); };

  const manv   = document.getElementById('manv').value.trim().toUpperCase();
  const hoten  = document.getElementById('hoten').value.trim();
  const xuongEl = document.getElementById('xuong');
  const xuong  = xuongEl.options[xuongEl.selectedIndex]?.value || '';
  const vitri  = document.getElementById('vitri').value.trim();
  const noidung= document.getElementById('noidung').value.trim();
  const file   = document.getElementById('file-input').files[0];

  if (!fromGemba && (!manv || !foundEmployee)) { show('err-manv',true); fc('manv'); ok=false; } else { show('err-manv',false); fc2('manv'); }
  if (!fromGemba && !xuong) { show('err-xuong',true); fc('xuong'); ok=false; } else { show('err-xuong',false); fc2('xuong'); }
  if (!fromGemba && !vitri) { show('err-vitri',true); fc('vitri'); ok=false; } else { show('err-vitri',false); fc2('vitri'); }
  if (!noidung) { show('err-noidung',true); fc('noidung'); ok=false; } else { show('err-noidung',false); fc2('noidung'); }
  if (!file) { show('err-img',true); document.getElementById('uarea').classList.add('req-err'); ok=false; }
  else { show('err-img',false); document.getElementById('uarea').classList.remove('req-err'); }

  if (!ok) { showToast('Vui lòng điền đầy đủ thông tin', 'error'); return; }

  const btn = document.getElementById('submit-btn');
  btn.disabled = true; btn.textContent = 'Đang gửi...';

  try {
    // Upload image first
    const fd = new FormData();
    fd.append('image', file);
    const upRes = await fetch('/api/upload.php', { method: 'POST', body: fd });
    const upData = await upRes.json();
    if (!upData.success) throw new Error(upData.error || 'Upload thất bại');

    // Submit report
    const bophan = document.getElementById('bophan').value.trim();
    const res = await fetch('/api/baocao.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_nv:manv, ho_ten:hoten, bo_phan:bophan, xuong, vi_tri:vitri, noi_dung:noidung, hinh_anh:upData.url }),
    });
    const d = await res.json();
    if (!d.success) throw new Error(d.error || 'Gửi báo cáo thất bại');

    // Show success
    document.getElementById('sc-id').textContent = d.id;
    document.getElementById('sc-name').textContent = hoten;
    document.getElementById('sc-ws').textContent = xuong;
    document.getElementById('sc-vitri').textContent = vitri;
    document.getElementById('sc-nd').textContent = noidung;
    const scImg = document.getElementById('sc-img');
    scImg.src = upData.url;
    document.getElementById('sc-img-wrap').style.display = 'block';

    document.getElementById('form-state').style.display = 'none';
    document.getElementById('success-state').style.display = 'block';
    window.scrollTo(0,0);

  } catch(e) {
    showToast(e.message || 'Có lỗi xảy ra', 'error');
    btn.disabled = false; btn.innerHTML = '<svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg> Gửi Báo Cáo';
  }
}

function resetForm() {
  document.getElementById('form-state').style.display = 'block';
  document.getElementById('success-state').style.display = 'none';
  ['manv','hoten','bophan','vitri','noidung'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
  document.getElementById('xuong').value = '';
  document.getElementById('file-input').value = '';
  document.getElementById('img-preview').style.display = 'none';
  document.getElementById('uarea').classList.remove('has-file');
  document.getElementById('manv-badge').className = 'lookup-badge';
  foundEmployee = false;
  const btn = document.getElementById('submit-btn');
  btn.disabled = false;
  btn.innerHTML = '<svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg> Gửi Báo Cáo';
  window.scrollTo(0,0);
}
</script>
</body>
</html>
