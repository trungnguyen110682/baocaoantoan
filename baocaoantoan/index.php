<?php
require_once __DIR__ . '/includes/auth.php';

if (isAdmin()) {
    header('Location: /pages/admin.php');
} elseif (isViewer()) {
    header('Location: /pages/dashboard.php');
} else {
    header('Location: /pages/login.php');
}
exit;
