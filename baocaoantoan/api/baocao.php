<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: danh sách / chi tiết ─────────────────────────────
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $row = dbOne("SELECT * FROM baocao WHERE id=?", [$_GET['id']]);
        if (!$row) { jsonResponse(['error' => 'Không tìm thấy'], 404); }
        if (!empty($_GET['token'])) {
            // Return token for KP link (admin or viewer)
            if (!isAdmin() && !isViewer()) { jsonResponse(['error' => 'Không có quyền'], 403); }
            $token = generateToken($_GET['id']);
            $link = SITE_URL . '/pages/khacphuc.php?id=' . $_GET['id'] . '&token=' . $token;
            jsonResponse(['token' => $token, 'link' => $link]);
        }
        jsonResponse($row);
    }

    $where = ['1=1'];
    $params = [];

    if (!empty($_GET['status'])) {
        $where[] = 'status = ?';
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['xuong'])) {
        $where[] = 'xuong = ?';
        $params[] = $_GET['xuong'];
    }
    if (!empty($_GET['tu']) && !empty($_GET['den'])) {
        $where[] = 'DATE(created_at) BETWEEN ? AND ?';
        $params[] = $_GET['tu'];
        $params[] = $_GET['den'];
    } elseif (!empty($_GET['tu'])) {
        $where[] = 'DATE(created_at) >= ?';
        $params[] = $_GET['tu'];
    }
    if (!empty($_GET['search'])) {
        $kw = '%' . $_GET['search'] . '%';
        $where[] = '(ho_ten LIKE ? OR vi_tri LIKE ? OR noi_dung LIKE ? OR id LIKE ?)';
        array_push($params, $kw, $kw, $kw, $kw);
    }

    $overdue = !empty($_GET['overdue']);
    if ($overdue) {
        $where[] = "deadline < CURDATE() AND status NOT IN ('fixed','rejected')";
    }

    $sql = "SELECT * FROM baocao WHERE " . implode(' AND ', $where)
         . " ORDER BY created_at DESC";

    $rows = dbAll($sql, $params);

    // Add computed fields
    foreach ($rows as &$r) {
        $r['days_remaining'] = daysRemaining($r['deadline']);
        $r['is_overdue'] = ($r['deadline'] && $r['deadline'] < date('Y-m-d') && !in_array($r['status'], ['fixed','rejected']));
    }
    jsonResponse($rows);
}

// ── POST: tạo báo cáo mới ─────────────────────────────────
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    $id = 'BC-' . date('Ymd') . '-' . date('His');
    $required = ['ma_nv','ho_ten','xuong','vi_tri','noi_dung'];
    foreach ($required as $f) {
        if (empty($data[$f])) { jsonResponse(['error' => "Thiếu trường: $f"], 422); }
    }

    dbExec("INSERT INTO baocao (id,ma_nv,ho_ten,bo_phan,xuong,vi_tri,noi_dung,hinh_anh,status,created_at)
            VALUES (?,?,?,?,?,?,?,?,'pending',NOW())", [
        $id,
        $data['ma_nv'],
        $data['ho_ten'],
        $data['bo_phan'] ?? null,
        $data['xuong'],
        $data['vi_tri'],
        $data['noi_dung'],
        $data['hinh_anh'] ?? null,
    ]);

    $row = dbOne("SELECT * FROM baocao WHERE id=?", [$id]);

    // Webhook
    sendWebhook(WH_BAOCAO_MOI, [
        'id'       => $id,
        'ho_ten'   => $data['ho_ten'],
        'xuong'    => $data['xuong'],
        'vi_tri'   => $data['vi_tri'],
        'noi_dung' => $data['noi_dung'],
        'img'      => $data['hinh_anh'] ?? null,
        'link'     => SITE_URL . '/pages/dashboard.php',
    ]);

    jsonResponse(['success' => true, 'id' => $id, 'record' => $row], 201);
}

// ── PUT: cập nhật (approve/reject/assign/fix) ─────────────
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = $data['id'] ?? ($_GET['id'] ?? null);
    if (!$id) { jsonResponse(['error' => 'Thiếu id'], 422); }

    $action = $data['action'] ?? '';

    if ($action === 'approve') {
        // Require admin
        if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
        dbExec("UPDATE baocao SET status='approved', approved_date=NOW(),
                category=?, golden_rule=?, severity=?, area=?,
                nguoi_kp=?, nkp_chuc_vu=?, nkp_email=?, deadline=?
                WHERE id=?", [
            $data['category']   ?? null,
            $data['golden_rule'] ?? null,
            $data['severity']   ?? null,
            $data['area']       ?? null,
            $data['nguoi_kp']   ?? null,
            $data['nkp_chuc_vu'] ?? null,
            $data['nkp_email']  ?? null,
            $data['deadline']   ?? null,
            $id,
        ]);

        $row = dbOne("SELECT * FROM baocao WHERE id=?", [$id]);
        sendWebhook(WH_PHE_DUYET, [
            // Thông tin báo cáo gốc
            'id'           => $id,
            'ma_nv'        => $row['ma_nv'],
            'ho_ten'       => $row['ho_ten'],
            'bo_phan'      => $row['bo_phan'],
            'xuong'        => $row['xuong'],
            'vi_tri'       => $row['vi_tri'],
            'noi_dung'     => $row['noi_dung'],
            'hinh_anh'     => $row['hinh_anh'] ? SITE_URL . $row['hinh_anh'] : null,
            'created_at'   => $row['created_at'],
            // Thông tin phê duyệt
            'severity'     => $row['severity'],
            'area'         => $row['area'],
            'category'     => $row['category'],
            'golden_rule'  => $row['golden_rule'],
            'nguoi_kp'     => $row['nguoi_kp'],
            'nkp_chuc_vu'  => $row['nkp_chuc_vu'],
            'nkp_email'    => $row['nkp_email'],
            'deadline'     => $row['deadline'],
            'approved_date'=> $row['approved_date'],
            'link'         => SITE_URL . '/pages/khacphuc.php?id=' . $id . '&token=' . generateToken($id),
        ]);
        jsonResponse(['success' => true, 'record' => $row]);
    }

    if ($action === 'reject') {
        if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
        dbExec("UPDATE baocao SET status='rejected' WHERE id=?", [$id]);
        jsonResponse(['success' => true]);
    }

    if ($action === 'fix') {
        // Token verification for public khacphuc page
        $token = $data['token'] ?? '';
        if (!isAdmin() && !verifyToken($id, $token)) {
            jsonResponse(['error' => 'Token không hợp lệ'], 403);
        }
        dbExec("UPDATE baocao SET status='fixed', fix_time=NOW(),
                fix_note=?, fix_img=?, fixer=? WHERE id=?", [
            $data['fix_note'] ?? null,
            $data['fix_img']  ?? null,
            $data['fixer']    ?? null,
            $id,
        ]);

        $row = dbOne("SELECT * FROM baocao WHERE id=?", [$id]);
        sendWebhook(WH_DA_KP, [
            'id'       => $id,
            'ho_ten'   => $row['ho_ten'],
            'xuong'    => $row['xuong'],
            'fixer'    => $row['fixer'],
            'fix_note' => $row['fix_note'],
            'fix_img'  => $row['fix_img'] ? SITE_URL . $row['fix_img'] : null,
        ]);
        jsonResponse(['success' => true, 'record' => $row]);
    }

    // General field update (admin only)
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $allowed = ['ma_nv','ho_ten','bo_phan','xuong','vi_tri','noi_dung','hinh_anh','category','golden_rule','severity','area','nguoi_kp','nkp_chuc_vu','nkp_email','deadline','status'];
    $set = []; $vals = [];
    foreach ($allowed as $f) {
        if (array_key_exists($f, $data)) {
            $set[] = "$f = ?";
            $vals[] = $data[$f];
        }
    }
    if ($set) {
        $vals[] = $id;
        dbExec("UPDATE baocao SET " . implode(', ', $set) . " WHERE id=?", $vals);
    }
    jsonResponse(['success' => true]);
}

// ── DELETE (admin only) ───────────────────────────────────
if ($method === 'DELETE') {
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $id = $_GET['id'] ?? null;
    if (!$id) { jsonResponse(['error' => 'Thiếu id'], 422); }
    dbExec("DELETE FROM baocao WHERE id=?", [$id]);
    jsonResponse(['success' => true]);
}
