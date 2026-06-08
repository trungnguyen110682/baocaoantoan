<?php
require_once __DIR__ . '/../config/app.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gemba Walk — An Toàn MMB</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav>
  <div class="nav-brand">
    <div class="nav-logo">
      <svg viewBox="0 0 24 24"><path d="M12 2L3 7v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V7L12 2zm-1 13.4l-3-3 1.4-1.4 1.6 1.6 4.6-4.6 1.4 1.4-6 6z"/></svg>
    </div>
    <span class="nav-title">Gemba Walk</span>
    <span class="nav-badge">MMB</span>
  </div>
</nav>

<div class="form-wrap">

  <!-- SUCCESS STATE -->
  <div id="gb-success" style="display:none;">
    <div class="success-card" style="border-color:var(--teal-bd);">
      <div class="success-hd" style="background:linear-gradient(135deg,#064e3b,var(--teal));">
        <div class="success-icon"><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg></div>
        <h2>Bạn đã hoàn thành Gemba Walk!</h2>
        <p>Kết quả kiểm tra đã được ghi nhận</p>
      </div>
      <div class="success-bd">
        <div style="text-align:center;padding:12px 0;font-size:15px;color:var(--green);font-weight:600;">✓ Tất cả hạng mục đều Đạt</div>
      </div>
    </div>
  </div>

  <!-- FORM -->
  <div id="gb-form-wrap">

    <!-- QR / Khu vực -->
    <div class="fcard">
      <div class="fhd" style="background:linear-gradient(135deg,var(--navy-dark),var(--denim));">
        <div class="fhi">
          <svg viewBox="0 0 24 24"><path d="M9.5 6.5v3h-3v-3h3M11 5H5v6h6V5zm-1.5 9.5v3h-3v-3h3M11 13H5v6h6v-6zm6.5-6.5v3h-3v-3h3M22 5h-6v6h6V5zm-6 8h1.5v1.5H16V13z"/></svg>
        </div>
        <div><h2>GEMBA WALK AN TOÀN</h2><p>Kiểm tra an toàn khu vực sản xuất</p></div>
      </div>
      <div class="fbd">

        <!-- Mã QR -->
        <div class="fs">
          <label class="fl" for="gb-qr">Mã QR khu vực <span class="req">*</span></label>
          <div style="display:flex;gap:8px;">
            <input type="text" id="gb-qr" class="fc" placeholder="VD: QR-F2-TP" style="text-transform:uppercase;flex:1;"
              oninput="onQRInput(this.value.toUpperCase())">
          </div>
          <div class="ferr" id="err-qr">Vui lòng nhập mã QR hợp lệ</div>
        </div>

        <!-- Area info (auto-fill) -->
        <div id="qr-info" style="display:none;background:var(--teal-bg);border:1px solid var(--teal-bd);border-radius:9px;padding:12px 14px;margin-bottom:12px;">
          <div style="font-size:11px;font-weight:700;color:var(--teal);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">📍 Thông tin khu vực</div>
          <div class="frow">
            <div class="sc-field"><div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Xưởng</div><div class="val" id="qi-xuong" style="font-size:13px;font-weight:600;">—</div></div>
            <div class="sc-field"><div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Khu vực</div><div class="val" id="qi-kv" style="font-size:13px;font-weight:600;">—</div></div>
          </div>
          <div class="sc-field"><div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Phụ trách</div><div class="val" id="qi-pt" style="font-size:13px;">—</div></div>
        </div>

        <!-- Mã NV -->
        <div class="fs">
          <label class="fl" for="gb-manv">Mã NV người kiểm tra <span class="req">*</span></label>
          <input type="text" id="gb-manv" class="fc" placeholder="VD: 17MB01275" maxlength="15" style="text-transform:uppercase;"
            oninput="onNVInput(this.value)">
          <div id="gb-nv-badge" class="lookup-badge"></div>
          <div class="ferr" id="err-manv">Vui lòng nhập mã NV hợp lệ</div>
        </div>

        <!-- Hidden fields -->
        <input type="hidden" id="gb-hoten">
        <input type="hidden" id="gb-bophan">
        <input type="hidden" id="gb-xuong-val">
        <input type="hidden" id="gb-kv-val">
        <input type="hidden" id="gb-pt-val">

      </div>
    </div>

    <!-- Checklist -->
    <div class="fcard" id="checklist-card" style="display:none;">
      <div class="fhd" style="background:var(--navy);padding:14px 18px;">
        <div style="font-size:13px;font-weight:700;color:white;">✓ CHECKLIST AN TOÀN</div>
      </div>
      <div style="padding:0;">
        <div class="cklist">
          <!-- Interlock -->
          <div class="cklist-item">
            <div>
              <div class="cklist-label">1. Interlock</div>
              <div class="cklist-sub">Thiết bị khóa liên động an toàn</div>
            </div>
            <div class="pass-fail">
              <button type="button" class="pf-btn pass" data-key="interlock" onclick="setPF('interlock','Đạt',this)">Đạt</button>
              <button type="button" class="pf-btn fail" data-key="interlock" onclick="setPF('interlock','Không đạt',this)">Không đạt</button>
            </div>
          </div>
          <!-- Che chắn -->
          <div class="cklist-item">
            <div>
              <div class="cklist-label">2. Che chắn</div>
              <div class="cklist-sub">Bộ phận chuyển động được che chắn đúng quy định</div>
            </div>
            <div class="pass-fail">
              <button type="button" class="pf-btn pass" onclick="setPF('checan','Đạt',this)">Đạt</button>
              <button type="button" class="pf-btn fail" onclick="setPF('checan','Không đạt',this)">Không đạt</button>
            </div>
          </div>
          <!-- Tủ điện -->
          <div class="cklist-item">
            <div>
              <div class="cklist-label">3. Tủ điện</div>
              <div class="cklist-sub">Tủ điện đóng kín, không có nguy cơ điện giật</div>
            </div>
            <div class="pass-fail">
              <button type="button" class="pf-btn pass" onclick="setPF('tudien','Đạt',this)">Đạt</button>
              <button type="button" class="pf-btn fail" onclick="setPF('tudien','Không đạt',this)">Không đạt</button>
            </div>
          </div>
          <!-- S5 Môi trường -->
          <div class="cklist-item">
            <div>
              <div class="cklist-label">4. S5 — Môi trường</div>
              <div class="cklist-sub">5S khu vực gọn gàng, sạch sẽ, đúng tiêu chuẩn</div>
            </div>
            <div class="pass-fail">
              <button type="button" class="pf-btn pass" onclick="setPF('s5_moitruong','Đạt',this)">Đạt</button>
              <button type="button" class="pf-btn fail" onclick="setPF('s5_moitruong','Không đạt',this)">Không đạt</button>
            </div>
          </div>
        </div>
      </div>
      <div style="padding:16px 18px;border-top:1px solid var(--border);">
        <div class="ferr" id="err-checklist" style="margin-bottom:10px;">Vui lòng đánh giá tất cả 4 hạng mục</div>

        <!-- Ghi chú -->
        <div class="fs">
          <label class="fl" for="gb-ghichu">Ghi chú</label>
          <textarea id="gb-ghichu" class="fc" rows="3" placeholder="Ghi chú thêm (nếu có)..."></textarea>
        </div>

        <button class="btn btn-full" id="gb-submit-btn" onclick="submitGemba()"
          style="background:var(--navy);color:white;padding:13px;font-size:14px;">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
          Hoàn Thành Kiểm Tra
        </button>
      </div>
    </div>
  </div>

</div>

<div class="toast" id="toast"></div>

<script src="/assets/js/app.js"></script>
<script>
let checklist = { interlock: null, checan: null, tudien: null, s5_moitruong: null };
let foundNV = false;
let qrData  = null;
let pendingQR = '';

let qrTimer;
function onQRInput(v) {
  qrData = null;
  document.getElementById('qr-info').style.display = 'none';
  document.getElementById('checklist-card').style.display = 'none';
  clearTimeout(qrTimer);
  if (v.length >= 4) {
    qrTimer = setTimeout(() => loadQR(v), 600);
  }
}

async function loadQR(maQR) {
  try {
    const res = await fetch('/api/qrcore.php?ma_qr=' + encodeURIComponent(maQR));
    const d = await res.json();
    if (d.ma_qr) {
      qrData = d;
      document.getElementById('qi-xuong').textContent = d.xuong  || '—';
      document.getElementById('qi-kv').textContent    = d.khu_vuc|| '—';
      document.getElementById('qi-pt').textContent    = d.phu_trach || '—';
      document.getElementById('gb-xuong-val').value = d.xuong  || '';
      document.getElementById('gb-kv-val').value    = d.khu_vuc|| '';
      document.getElementById('gb-pt-val').value    = d.phu_trach || '';
      document.getElementById('qr-info').style.display = 'block';
      document.getElementById('err-qr').classList.remove('on');
      checkShowChecklist();
    } else {
      document.getElementById('err-qr').textContent = 'Mã QR không tồn tại trong hệ thống';
      document.getElementById('err-qr').classList.add('on');
    }
  } catch(e) {}
}

let nvTimer;
function onNVInput(v) {
  foundNV = false;
  document.getElementById('gb-nv-badge').className = 'lookup-badge';
  document.getElementById('checklist-card').style.display = 'none';
  clearTimeout(nvTimer);
  if (v.length >= 5) {
    nvTimer = setTimeout(() => {
      lookupEmployee(v, { nameId:'gb-hoten', deptId:'gb-bophan', badgeId:'gb-nv-badge' });
      setTimeout(() => {
        foundNV = document.getElementById('gb-nv-badge').classList.contains('found');
        checkShowChecklist();
      }, 800);
    }, 500);
  }
}

function checkShowChecklist() {
  if (qrData && foundNV) {
    document.getElementById('checklist-card').style.display = 'block';
  }
}

// Watch badge for NV
const nvBadge = document.getElementById('gb-nv-badge');
new MutationObserver(() => {
  foundNV = nvBadge.classList.contains('found');
  checkShowChecklist();
}).observe(nvBadge, { attributes:true, attributeFilter:['class'] });

function setPF(key, val, btn) {
  checklist[key] = val;
  // Update button styles
  const row = btn.closest('.pass-fail');
  row.querySelectorAll('.pf-btn').forEach(b => b.classList.remove('sel'));
  btn.classList.add('sel');
}

async function submitGemba() {
  let ok = true;

  if (!qrData) {
    document.getElementById('err-qr').textContent = 'Vui lòng nhập mã QR hợp lệ';
    document.getElementById('err-qr').classList.add('on'); ok = false;
  }
  if (!foundNV) {
    document.getElementById('err-manv').classList.add('on'); ok = false;
  }
  const allFilled = Object.values(checklist).every(v => v !== null);
  if (!allFilled) {
    document.getElementById('err-checklist').classList.add('on'); ok = false;
  } else {
    document.getElementById('err-checklist').classList.remove('on');
  }
  if (!ok) { showToast('Vui lòng hoàn thành đầy đủ thông tin', 'error'); return; }

  const btn = document.getElementById('gb-submit-btn');
  btn.disabled = true; btn.textContent = 'Đang lưu...';

  const manv   = document.getElementById('gb-manv').value.trim().toUpperCase();
  const hoten  = document.getElementById('gb-hoten').value;
  const bophan = document.getElementById('gb-bophan').value;
  const ghichu = document.getElementById('gb-ghichu').value.trim();
  const xuong  = document.getElementById('gb-xuong-val').value;
  const khuVuc = document.getElementById('gb-kv-val').value;

  try {
    const res = await fetch('/api/gemba.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        ma_qr:  document.getElementById('gb-qr').value.trim().toUpperCase(),
        xuong:  xuong,
        khu_vuc: khuVuc,
        phu_trach: document.getElementById('gb-pt-val').value,
        ma_nv: manv, ho_ten: hoten, bo_phan: bophan,
        ...checklist,
        ghi_chu: ghichu,
        co_su_co: false,
      }),
    });
    const d = await res.json();
    if (!d.success) throw new Error(d.error || 'Gửi thất bại');

    const hasFail = Object.values(checklist).some(v => v === 'Không đạt');

    if (hasFail) {
      // Chuyển thẳng sang trang báo cáo mới với thông tin pre-fill
      const params = new URLSearchParams({
        manv:   manv,
        hoten:  hoten,
        bophan: bophan,
        xuong:  xuong,
        vitri:  khuVuc,
      });
      window.location.href = '/pages/baocaomoi.php?' + params.toString();
    } else {
      // Tất cả đạt — hiện thông báo hoàn thành
      document.getElementById('gb-form-wrap').style.display = 'none';
      document.getElementById('gb-success').style.display = 'block';
      window.scrollTo(0,0);
    }

  } catch(e) {
    showToast(e.message || 'Có lỗi xảy ra', 'error');
    btn.disabled = false;
    btn.innerHTML = '<svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg> Hoàn Thành Kiểm Tra';
  }
}

function resetGemba() {
  checklist = { interlock:null, checan:null, tudien:null, s5_moitruong:null };
  foundNV = false; qrData = null;
  document.getElementById('gb-qr').value = '';
  document.getElementById('gb-manv').value = '';
  document.getElementById('gb-ghichu').value = '';
  document.getElementById('gb-nv-badge').className = 'lookup-badge';
  document.getElementById('qr-info').style.display = 'none';
  document.getElementById('checklist-card').style.display = 'none';
  document.querySelectorAll('.pf-btn').forEach(b => b.classList.remove('sel'));
  document.getElementById('gb-success').style.display = 'none';
  document.getElementById('gb-form-wrap').style.display = 'block';
  const btn = document.getElementById('gb-submit-btn');
  btn.disabled = false;
  btn.innerHTML = '<svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg> Hoàn Thành Kiểm Tra';
  window.scrollTo(0,0);
}

// Check URL param for pre-filled QR
const urlQR = new URLSearchParams(location.search).get('qr');
if (urlQR) {
  document.getElementById('gb-qr').value = urlQR.toUpperCase();
  loadQR(urlQR.toUpperCase());
}
</script>
</body>
</html>
