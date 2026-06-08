<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

header('Content-Type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'webhooks';
    if ($action === 'webhooks') {
        $rows = dbAll("SELECT id, ten_key, url, url2, mo_ta FROM webhook ORDER BY id");
        jsonResponse($rows);
    }
    jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $data['action'] ?? '';

    if ($action === 'save_webhook') {
        $id = $data['id'] ?? null;
        if (!$id) { jsonResponse(['error' => 'Thiếu id'], 422); }
        dbExec("UPDATE webhook SET url=?, url2=? WHERE id=?", [
            $data['url'] ?? null,
            $data['url2'] ?? null,
            (int)$id,
        ]);
        jsonResponse(['success' => true]);
    }

    if ($action === 'change_password') {
        $role    = $data['role'] ?? '';
        $newpass = $data['password'] ?? '';
        if (!in_array($role, ['admin', 'viewer'])) { jsonResponse(['error' => 'Role không hợp lệ'], 422); }
        if (strlen($newpass) < 4) { jsonResponse(['error' => 'Mật khẩu quá ngắn (tối thiểu 4 ký tự)'], 422); }
        $key = ($role === 'admin') ? 'admin_pass' : 'viewer_pass';
        dbExec("UPDATE caidat SET gia_tri=? WHERE ten_key=?", [md5($newpass), $key]);
        jsonResponse(['success' => true]);
    }

    jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

jsonResponse(['error' => 'Method not allowed'], 405);
