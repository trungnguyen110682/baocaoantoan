<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$stats = [
  'total'   => dbValue("SELECT COUNT(*) FROM baocao"),
  'pending' => dbValue("SELECT COUNT(*) FROM baocao WHERE status='pending'"),
  'approved'=> dbValue("SELECT COUNT(*) FROM baocao WHERE status='approved'"),
  'fixed'   => dbValue("SELECT COUNT(*) FROM baocao WHERE status='fixed'"),
  'overdue' => dbValue("SELECT COUNT(*) FROM baocao WHERE deadline < CURDATE() AND status NOT IN ('fixed','rejected')"),
];
$nguoiKP = dbAll("SELECT ho_ten, chuc_vu, email FROM thanhvien WHERE vai_tro='nguoikhacphuc' ORDER BY ho_ten");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — An Toàn MMB</title>
<link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<style>
/* ── Workshop chart grid ── */
.ws-chart-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px; }
.ws-chart-box { background:var(--bg); border-radius:8px; padding:10px 12px; border:1px solid var(--border); }
.ws-chart-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; margin-bottom:8px; color:var(--navy); }
.ws-chart-wrap { position:relative; height:160px; }
/* ── Top NV list ── */
.top-row { display:flex; align-items:center; gap:10px; padding:7px 0; border-bottom:1px solid var(--border); }
.top-row:last-child { border-bottom:none; }
.top-rank { width:26px; height:26px; border-radius:50%; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.top-r1 { background:#fef3c7; color:#d97706; }
.top-r2 { background:#f1f5f9; color:#475569; }
.top-r3 { background:#fce7f3; color:#be185d; }
.top-rn { background:var(--bg); color:var(--muted); }
.top-bar-wrap { flex:1; height:6px; background:var(--bg); border-radius:3px; }
.top-bar-fill { height:100%; background:var(--orange); border-radius:3px; transition:width .4s; }
.top-count { font-size:13px; font-weight:700; color:var(--orange); min-width:36px; text-align:right; }
/* ── Settings page ── */
.set-section { background:white; border-radius:10px; border:1px solid var(--border); padding:20px; margin-bottom:16px; }
.set-section-title { font-size:14px; font-weight:700; color:var(--navy); margin-bottom:14px; padding-bottom:10px; border-bottom:1px solid var(--border); }
.wh-row { display:flex; align-items:flex-start; gap:10px; padding:12px 0; border-bottom:1px solid var(--border); flex-wrap:wrap; }
.wh-row:last-child { border-bottom:none; }
.wh-key { font-size:12px; font-weight:700; color:var(--navy); background:var(--bg); padding:3px 9px; border-radius:5px; white-space:nowrap; flex-shrink:0; }
.wh-desc { font-size:12px; color:var(--muted); white-space:nowrap; flex-shrink:0; min-width:140px; }
.wh-urls { flex:1; min-width:200px; }
.wh-url-val { font-size:12px; color:var(--denim); word-break:break-all; margin-bottom:2px; }
.wh-url-empty { font-size:12px; color:var(--muted); font-style:italic; }
.pass-form { display:grid; grid-template-columns:1fr 1fr; gap:12px; align-items:end; }
@media(max-width:600px) {
  .ws-chart-grid { grid-template-columns:1fr; }
  .pass-form { grid-template-columns:1fr; }
}
/* ── Gemba stat cards ── */
.gb-stat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
.gb-sc { background:white; border-radius:10px; border:1px solid var(--border); padding:12px 14px; text-align:center; }
.gb-sn { font-size:24px; font-weight:700; color:var(--navy); }
.gb-sl { font-size:11px; color:var(--muted); margin-top:2px; }
/* ── Filter bar extras ── */
.fsel-sm { padding:5px 9px; border:1.5px solid var(--border); border-radius:7px; font-size:12px; background:var(--bg); cursor:pointer; }
.fsel-sm:focus { outline:none; border-color:var(--navy); }
.fl-row { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.fl-lbl { font-size:11px; color:var(--orange); font-weight:700; padding:4px 10px; background:var(--yellow-bg); border-radius:6px; white-space:nowrap; }
</style>
</head>
<body style="display:flex;flex-direction:column;min-height:100vh;">

<nav>
  <div class="nav-brand">
    <button class="sb-toggle" id="sb-toggle">☰</button>
    <div class="nav-logo" style="margin-left:4px;">
      <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
    </div>
    <span class="nav-title">An Toàn MMB</span>
    <span class="nav-badge">ADMIN</span>
  </div>
  <div class="nav-right">
    <span id="nav-time" style="font-size:12px;"></span>
    <a href="/logout.php"><button class="logout-btn">Đăng xuất</button></a>
  </div>
</nav>
<div id="sb-overlay" class="sb-overlay"></div>

<div class="app-shell">
<aside class="sidebar" id="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-title">HRE SYSTEM</div>
    <div class="sb-logo-sub">Quản trị an toàn</div>
  </div>
  <div class="sb-section">Tổng quan</div>
  <button class="sb-item active" onclick="switchPage('dashboard',this)"><span class="sb-icon">📊</span> Dashboard</button>
  <div class="sb-section">Báo cáo</div>
  <button class="sb-item" onclick="switchPage('safety',this)"><span class="sb-icon">⚠️</span> Safety Report <span class="sb-badge" id="sb-pending-cnt"><?= $stats['pending'] ?></span></button>
  <button class="sb-item" onclick="switchPage('gemba',this)"><span class="sb-icon">🔍</span> Gemba Walk</button>
  <div class="sb-divider"></div>
  <div class="sb-section">Phân tích</div>
  <button class="sb-item" onclick="switchPage('phanloai',this)"><span class="sb-icon">📂</span> Phân loại</button>
  <button class="sb-item" onclick="switchPage('goldenrule',this)"><span class="sb-icon">🏆</span> Golden Rule</button>
  <button class="sb-item" onclick="switchPage('mucdo',this)"><span class="sb-icon">📈</span> Mức độ</button>
  <button class="sb-item" onclick="switchPage('khuvuc',this)"><span class="sb-icon">🗺️</span> Khu vực</button>
  <div class="sb-divider"></div>
  <div class="sb-section">Khác</div>
  <button class="sb-item" onclick="switchPage('hopdauca',this)"><span class="sb-icon">📋</span> Họp đầu ca</button>
  <button class="sb-item" onclick="switchPage('qrmanager',this)"><span class="sb-icon">📷</span> QR Manager</button>
  <button class="sb-item" onclick="switchPage('thanhvien',this)"><span class="sb-icon">👥</span> Thành viên</button>
  <button class="sb-item" onclick="switchPage('settings',this)"><span class="sb-icon">⚙️</span> Cài đặt</button>
  <div class="sb-bottom">
    <div class="sb-user">
      <div class="sb-user-av">A</div>
      <div><div class="sb-user-name">Admin</div><div class="sb-user-role">Quản trị viên</div></div>
    </div>
  </div>
</aside>

<div class="main-content" id="main-content">

<?php include __DIR__ . '/admin/_dashboard.php'; ?>
<?php include __DIR__ . '/admin/_safety.php'; ?>
<?php include __DIR__ . '/admin/_gemba.php'; ?>
<?php include __DIR__ . '/admin/_analysis.php'; ?>
<?php include __DIR__ . '/admin/_qrmanager.php'; ?>
<?php include __DIR__ . '/admin/_thanhvien.php'; ?>
<?php include __DIR__ . '/admin/_settings.php'; ?>

</div><!-- /main-content -->
</div><!-- /app-shell -->

<?php include __DIR__ . '/admin/_modals.php'; ?>

<script src="/assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/charts.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/admin.js?v=<?php echo time(); ?>"></script>
</body>
</html>
