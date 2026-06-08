<?php
require_once __DIR__ . '/../includes/auth.php';

$role     = in_array($_GET['role'] ?? '', ['admin','viewer']) ? $_GET['role'] : 'viewer';
$redirect = $_GET['redirect'] ?? ($role === 'admin' ? '/pages/admin.php' : '/pages/dashboard.php');
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = trim($_POST['password'] ?? '');
    if (tryLogin($role, $pass)) {
        header('Location: ' . $redirect);
        exit;
    }
    $error = 'Mật khẩu không đúng. Vui lòng thử lại.';
}

$title = $role === 'admin' ? 'Quản Trị Hệ Thống' : 'Xem Báo Cáo';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng Nhập — An Toàn MMB</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="login-page">
  <div class="login-box">
    <div class="login-hd">
      <div class="login-logo">
        <svg viewBox="0 0 24 24"><path d="M12 2L3 7v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V7L12 2zm-1 13.4l-3-3 1.4-1.4 1.6 1.6 4.6-4.6 1.4 1.4-6 6z"/></svg>
      </div>
      <h2>AN TOÀN MMB</h2>
      <p><?= htmlspecialchars($title) ?></p>
    </div>
    <div class="login-bd">
      <?php if ($error): ?>
        <div class="lerr on"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="lf">
          <label class="ll" for="password">Mật khẩu</label>
          <input type="password" id="password" name="password" class="li" placeholder="Nhập mật khẩu..." autofocus required>
        </div>
        <button type="submit" class="btn btn-or btn-full" style="padding:12px;">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
          Đăng Nhập
        </button>
      </form>
      <?php if ($role === 'viewer'): ?>
      <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--muted);">
        <a href="/pages/login.php?role=admin" style="color:var(--denim);font-weight:600;">Đăng nhập Admin →</a>
      </div>
      <?php else: ?>
      <div style="text-align:center;margin-top:16px;font-size:12px;color:var(--muted);">
        <a href="/pages/login.php?role=viewer" style="color:var(--denim);font-weight:600;">← Quay về</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
