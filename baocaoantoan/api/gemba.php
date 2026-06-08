<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $row = dbOne("SELECT * FROM gemba WHERE id=?", [$_GET['id']]);
        if (!$row) { jsonResponse(['error' => 'Không tìm thấy'], 404); }
        jsonResponse($row);
    }

    $where = ['1=1']; $params = [];

    if (!empty($_GET['xuong'])) {
        $where[] = 'xuong = ?';
        $params[] = $_GET['xuong'];
    }
    if (!empty($_GET['manv'])) {
        $where[] = 'ma_nv = ?';
        $params[] = $_GET['manv'];
    }
    if (!empty($_GET['tu']) && !empty($_GET['den'])) {
        $where[] = 'DATE(created_at) BETWEEN ? AND ?';
        $params[] = $_GET['tu'];
        $params[] = $_GET['den'];
    } elseif (!empty($_GET['tuan'])) {
        // Current week
        $where[] = 'YEARWEEK(created_at,1) = YEARWEEK(NOW(),1)';
    } elseif (!empty($_GET['thang'])) {
        // Current month
        $where[] = 'YEAR(created_at)=YEAR(NOW()) AND MONTH(created_at)=MONTH(NOW())';
    }

    $sql = "SELECT * FROM gemba WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
    $rows = dbAll($sql, $params);
    jsonResponse($rows);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id = 'GB-' . date('Ymd') . '-' . date('His');

    if (empty($data['ma_qr']) || empty($data['ma_nv'])) {
        jsonResponse(['error' => 'Thiếu ma_qr hoặc ma_nv'], 422);
    }

    dbExec("INSERT INTO gemba
        (id,ma_qr,xuong,khu_vuc,phu_trach,ma_nv,ho_ten,bo_phan,
         interlock,checan,tudien,s5_moitruong,ghi_chu,co_su_co,created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())", [
        $id,
        $data['ma_qr'],
        $data['xuong']    ?? null,
        $data['khu_vuc']  ?? null,
        $data['phu_trach']?? null,
        $data['ma_nv'],
        $data['ho_ten']   ?? null,
        $data['bo_phan']  ?? null,
        $data['interlock']    ?? null,
        $data['checan']       ?? null,
        $data['tudien']       ?? null,
        $data['s5_moitruong'] ?? null,
        $data['ghi_chu']  ?? null,
        !empty($data['co_su_co']) ? 1 : 0,
    ]);

    jsonResponse(['success' => true, 'id' => $id], 201);
}

if ($method === 'PUT') {
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $id = $_GET['id'] ?? null;
    if (!$id) { jsonResponse(['error' => 'Thiếu id'], 422); }
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $fields = ['ma_qr','xuong','khu_vuc','phu_trach','ma_nv','ho_ten','bo_phan',
               'interlock','checan','tudien','s5_moitruong','ghi_chu','co_su_co'];
    $set = []; $params = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $data)) {
            $set[] = "$f = ?";
            $params[] = ($f === 'co_su_co') ? (int)$data[$f] : $data[$f];
        }
    }
    if (empty($set)) { jsonResponse(['error' => 'Không có trường nào để cập nhật'], 422); }
    $params[] = $id;
    dbExec("UPDATE gemba SET " . implode(', ', $set) . " WHERE id=?", $params);
    jsonResponse(['success' => true]);
}

if ($method === 'DELETE') {
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $id = $_GET['id'] ?? null;
    if (!$id) { jsonResponse(['error' => 'Thiếu id'], 422); }
    dbExec("DELETE FROM gemba WHERE id=?", [$id]);
    jsonResponse(['success' => true]);
}
