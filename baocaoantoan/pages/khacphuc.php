<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$id    = $_GET['id']    ?? '';
$token = $_GET['token'] ?? '';

// Validate token or admin session
$allowed = isAdmin() || verifyToken($id, $token);

$report = null;
$tokenErr = false;

if ($id) {
    $report = dbOne("SELECT * FROM baocao WHERE id=?", [$id]);
}
if (!$id || !$report) {
    $tokenErr = true;
} elseif (!$allowed) {
    $tokenErr = true;
}

$alreadyFixed = $report && $report['status'] === 'fixed';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nhập Kết Quả Khắc Phục — An Toàn MMB</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav>
  <div class="nav-brand">
    <div class="nav-logo">
      <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
    </div>
    <span class="nav-title">Khắc Phục Sự Cố</span>
    <span class="nav-badge">MMB</span>
  </div>
</nav>

<div class="form-wrap">

<?php if ($tokenErr): ?>
  <div class="fcard">
    <div class="fhd" style="background:linear-gradient(135deg,#7f1d1d,var(--red));">
      <div class="fhi" style="background:rgba(255,255,255,.2);">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
      </div>
      <div><h2>Liên kết không hợp lệ</h2><p>Vui lòng kiểm tra lại đường dẫn</p></div>
    </div>
    <div class="fbd" style="text-align:center;padding:30px;">
      <p style="color:var(--muted);font-size:13px;">Đường dẫn khắc phục đã hết hạn hoặc không đúng.</p>
    </div>
  </div>
<?php elseif ($alreadyFixed): ?>
  <!-- Already fixed -->
  <div class="success-card" style="background:white;border:1px solid var(--green-bd);">
    <div class="success-hd">
      <div class="success-icon">
        <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
      </div>
      <h2>Đã Khắc Phục</h2>
      <p>Sự cố này đã được xử lý</p>
    </div>
    <div class="success-bd">
      <div class="sc-id"><?= e($report['id']) ?></div>
      <div class="sc-field"><div class="lbl">Người KP</div><div class="val"><?= e($report['fixer'] ?: $report['nguoi_kp']) ?></div></div>
      <div class="sc-field"><div class="lbl">Thời gian</div><div class="val"><?= formatDate($report['fix_time']) ?></div></div>
      <div class="sc-field"><div class="lbl">Ghi chú</div><div class="val"><?= e($report['fix_note']) ?></div></div>
      <?php if ($report['fix_img']): ?>
      <div style="margin-top:12px;">
        <div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Hình ảnh sau KP</div>
        <img src="<?= e($report['fix_img']) ?>" alt="" style="width:100%;max-height:200px;object-fit:cover;border-radius:9px;border:1px solid var(--border);cursor:pointer;" onclick="openLightbox(this.src)">
      </div>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <!-- FORM -->
  <!-- Report info card -->
  <div class="fcard">
    <div class="fhd">
      <div class="fhi">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/></svg>
      </div>
      <div>
        <h2>THÔNG TIN SỰ CỐ</h2>
        <p><?= e($report['id']) ?></p>
      </div>
    </div>
    <div class="fbd">
      <div class="frow">
        <div class="sc-field"><div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Người báo cáo</div><div style="font-size:13px;font-weight:600;"><?= e($report['ho_ten']) ?></div></div>
        <div class="sc-field"><div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Xưởng</div><div style="font-size:13px;font-weight:600;"><?= e($report['xuong']) ?></div></div>
      </div>
      <div class="sc-field" style="margin-top:8px;"><div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Vị trí</div><div style="font-size:13px;"><?= e($report['vi_tri']) ?></div></div>
      <div class="sc-field" style="margin-top:8px;"><div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:2px;">Mô tả</div><div style="font-size:13px;line-height:1.5;"><?= e($report['noi_dung']) ?></div></div>
      <?php if ($report['hinh_anh']): ?>
      <div style="margin-top:12px;">
        <div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Hình ảnh sự cố</div>
        <img src="<?= e($report['hinh_anh']) ?>" alt="" style="width:100%;max-height:180px;object-fit:cover;border-radius:9px;border:1px solid var(--border);cursor:pointer;" onclick="openLightbox(this.src)">
      </div>
      <?php endif; ?>
      <?php if ($report['deadline']): ?>
      <div style="margin-top:12px;">
        <?php $days = daysRemaining($report['deadline']); ?>
        <span class="chip <?= $days < 0 ? 'chip-red' : ($days <= 1 ? 'chip-orange' : 'chip-blue') ?>">
          Hạn KP: <?= formatDateOnly($report['deadline']) ?>
          <?= $days !== null ? ($days < 0 ? ' (Quá hạn '.abs($days).' ngày)' : " ($days ngày còn lại)") : '' ?>
        </span>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Success state (post-submit) -->
  <div id="kp-success" style="display:none;">
    <div class="success-card">
      <div class="success-hd">
        <div class="success-icon">
          <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
        </div>
        <h2>Khắc Phục Hoàn Thành!</h2>
        <p>Thông tin đã được lưu thành công</p>
      </div>
      <div class="success-bd">
        <div class="sc-id"><?= e($report['id']) ?></div>
        <div class="sc-field"><div class="lbl">Người KP</div><div class="val" id="kp-fixer-shown">—</div></div>
        <div class="sc-field"><div class="lbl">Thời gian</div><div class="val" id="kp-time-shown">—</div></div>
        <div class="sc-field"><div class="lbl">Ghi chú</div><div class="val" id="kp-note-shown">—</div></div>
        <div id="kp-img-wrap" style="display:none;margin-top:12px;">
          <div class="lbl" style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Hình ảnh sau KP</div>
          <img id="kp-img-shown" src="" alt="" style="width:100%;max-height:200px;object-fit:cover;border-radius:9px;border:1px solid var(--border);cursor:pointer;" onclick="openLightbox(this.src)">
        </div>
      </div>
    </div>
  </div>

  <!-- KP Form -->
  <div id="kp-form">
    <div class="fcard">
      <div class="fhd" style="background:linear-gradient(135deg,#064e3b,var(--teal));">
        <div class="fhi">
          <svg viewBox="0 0 24 24"><path d="M12 2L3 7v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V7L12 2zm-1 13.4l-3-3 1.4-1.4 1.6 1.6 4.6-4.6 1.4 1.4-6 6z"/></svg>
        </div>
        <div><h2>NHẬP KẾT QUẢ KHẮC PHỤC</h2><p>Điền thông tin sau khi hoàn thành</p></div>
      </div>
      <div class="fbd">

        <!-- Thời gian KP -->
        <div class="fs">
          <label class="fl">Thời gian KP</label>
          <input type="text" id="kp-time" class="fc" readonly value="" placeholder="Tự động ghi nhận khi gửi">
        </div>

        <!-- Ghi chú -->
        <div class="fs">
          <label class="fl" for="kp-note">Nội dung khắc phục <span class="req">*</span></label>
          <textarea id="kp-note" class="fc" rows="4" placeholder="Mô tả chi tiết biện pháp khắc phục đã thực hiện..."></textarea>
          <div class="ferr" id="err-kp-note">Vui lòng nhập nội dung khắc phục</div>
        </div>

        <!-- Hình ảnh sau KP -->
        <div class="fs">
          <label class="fl">Hình ảnh sau KP <span class="req">*</span></label>
          <div class="uarea" id="kp-uarea">
            <div class="ubtn-row">
              <button type="button" class="ubtn" id="kp-btn-camera">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 5h-3.17L15 3H9L7.17 5H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-8 13c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.65 0-3 1.35-3 3s1.35 3 3 3 3-1.35 3-3-1.35-3-3-3z"/></svg>
                Chụp ảnh
              </button>
              <button type="button" class="ubtn" id="kp-btn-lib">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                Chọn ảnh
              </button>
            </div>
            <p>Kéo thả hoặc nhấn nút bên trên để chọn ảnh sau khi KP</p>
            <input type="file" id="kp-file-input" accept="image/*" style="display:none">
            <div id="kp-img-preview" class="iprev">
              <img src="" alt="preview">
              <button type="button" class="xbtn">&times;</button>
            </div>
          </div>
          <div class="ferr" id="err-kp-img">Vui lòng chọn hình ảnh sau khắc phục</div>
        </div>

        <button class="btn btn-full" id="kp-submit-btn" onclick="submitKP()"
          style="background:var(--teal);color:white;padding:13px;font-size:14px;margin-top:4px;">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
          Xác Nhận Hoàn Thành
        </button>
      </div>
    </div>
  </div>
<?php endif; ?>
</div>

<div id="lightbox" onclick="closeLightbox()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:500;align-items:center;justify-content:center;cursor:zoom-out;">
  <img id="lb-img" src="" alt="" style="max-width:92vw;max-height:90vh;object-fit:contain;border-radius:6px;">
</div>

<div class="toast" id="toast"></div>

<script src="/assets/js/app.js"></script>
<script>
const REPORT_ID = <?= json_encode($report['id'] ?? '') ?>;
const TOKEN     = <?= json_encode($token) ?>;

<?php if (!$tokenErr && !$alreadyFixed): ?>
initImageUpload({ areaId:'kp-uarea', cameraId:'kp-btn-camera', libId:'kp-btn-lib', previewId:'kp-img-preview', inputId:'kp-file-input' });

// Current datetime for fix time display
document.getElementById('kp-time').value = new Date().toLocaleString('vi-VN');

async function submitKP() {
  const note = document.getElementById('kp-note').value.trim();
  const file = document.getElementById('kp-file-input').files[0];
  const fixer= <?= json_encode($report['nguoi_kp'] ?? '') ?>;
  let ok = true;

  const nEl = document.getElementById('kp-note'); const nErr = document.getElementById('err-kp-note');
  const iEl = document.getElementById('kp-uarea'); const iErr = document.getElementById('err-kp-img');

  if (!note) { nEl.classList.add('err'); nErr.classList.add('on'); ok=false; }
  else { nEl.classList.remove('err'); nErr.classList.remove('on'); }
  if (!file) { iEl.classList.add('req-err'); iErr.classList.add('on'); ok=false; }
  else { iEl.classList.remove('req-err'); iErr.classList.remove('on'); }

  if (!ok) { showToast('Vui lòng điền đầy đủ thông tin', 'error'); return; }

  const btn = document.getElementById('kp-submit-btn');
  btn.disabled = true; btn.textContent = 'Đang lưu...';

  try {
    // Upload image
    const fd = new FormData(); fd.append('image', file);
    const upRes = await fetch('/api/upload.php', { method:'POST', body:fd });
    const upData = await upRes.json();
    if (!upData.success) throw new Error(upData.error || 'Upload thất bại');

    // Submit fix
    const res = await fetch('/api/baocao.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id:REPORT_ID, action:'fix', token:TOKEN, fix_note:note, fix_img:upData.url, fixer:fixer }),
    });
    const d = await res.json();
    if (!d.success) throw new Error(d.error || 'Gửi thất bại');

    // Show success
    document.getElementById('kp-fixer-shown').textContent = fixer || '—';
    document.getElementById('kp-time-shown').textContent = new Date().toLocaleString('vi-VN');
    document.getElementById('kp-note-shown').textContent = note;
    document.getElementById('kp-img-shown').src = upData.url;
    document.getElementById('kp-img-wrap').style.display = 'block';

    document.getElementById('kp-form').style.display = 'none';
    document.getElementById('kp-success').style.display = 'block';
    window.scrollTo(0,0);

  } catch(e) {
    showToast(e.message || 'Có lỗi xảy ra', 'error');
    btn.disabled = false;
    btn.innerHTML = '<svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg> Xác Nhận Hoàn Thành';
  }
}

function openLightbox(src) { const lb=document.getElementById('lightbox'); document.getElementById('lb-img').src=src; lb.style.display='flex'; }
function closeLightbox() { document.getElementById('lightbox').style.display='none'; }
<?php endif; ?>
</script>
</body>
</html>
