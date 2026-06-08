<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];

// ── GET ───────────────────────────────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'lookup';

    if ($action === 'lookup') {
        $manv = trim($_GET['manv'] ?? '');
        if (!$manv) { jsonResponse(['error' => 'Thiếu manv'], 422); }
        $row = dbOne("SELECT * FROM thanhvien WHERE ma_nv = ? LIMIT 1", [$manv]);
        if (!$row) { jsonResponse(['found' => false]); }
        jsonResponse(['found' => true, 'data' => $row]);
    }

    if ($action === 'list') {
        $where = ['1=1']; $params = [];
        if (!empty($_GET['vai_tro'])) { $where[] = 'vai_tro = ?'; $params[] = $_GET['vai_tro']; }
        if (!empty($_GET['bo_phan'])) { $where[] = 'bo_phan = ?'; $params[] = $_GET['bo_phan']; }
        if (!empty($_GET['q'])) {
            $kw = '%' . $_GET['q'] . '%';
            $where[] = '(ho_ten LIKE ? OR ma_nv LIKE ? OR bo_phan LIKE ?)';
            array_push($params, $kw, $kw, $kw);
        }
        $rows = dbAll("SELECT * FROM thanhvien WHERE " . implode(' AND ', $where) . " ORDER BY ho_ten", $params);
        jsonResponse($rows);
    }

    if ($action === 'nguoikhacphuc') {
        $rows = dbAll("SELECT ho_ten, chuc_vu, email FROM thanhvien WHERE vai_tro='nguoikhacphuc' ORDER BY ho_ten");
        jsonResponse($rows);
    }

    if ($action === 'quanly') {
        $rows = dbAll("SELECT ho_ten, chuc_vu, bo_phan, email FROM thanhvien WHERE vai_tro='quanly' ORDER BY ho_ten");
        jsonResponse($rows);
    }

    if ($action === 'search') {
        $kw = '%' . trim($_GET['q'] ?? '') . '%';
        $rows = dbAll("SELECT ma_nv, ho_ten, bo_phan, xuong FROM thanhvien WHERE ho_ten LIKE ? OR ma_nv LIKE ? LIMIT 20", [$kw, $kw]);
        jsonResponse($rows);
    }

    jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

// ── POST: tạo mới hoặc import hàng loạt (admin only) ─────────
if ($method === 'POST') {
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // ── Import hàng loạt ─────────────────────────────────────
    if (($data['action'] ?? '') === 'import') {
        $rows   = $data['rows'] ?? [];
        $mode   = $data['mode'] ?? 'upsert'; // 'upsert' | 'skip'
        if (!is_array($rows) || empty($rows)) {
            jsonResponse(['error' => 'Không có dữ liệu để import'], 422);
        }

        $inserted = 0; $updated = 0; $skipped = 0; $errors = [];
        $validRoles = ['nhanvien', 'nguoikhacphuc', 'quanly'];

        foreach ($rows as $i => $r) {
            $hoTen = trim($r['ho_ten'] ?? '');
            if ($hoTen === '') { $errors[] = "Dòng " . ($i+2) . ": Thiếu họ tên"; continue; }

            $maNV   = trim($r['ma_nv']   ?? '') ?: null;
            $boPhan = trim($r['bo_phan'] ?? '') ?: null;
            $xuong  = trim($r['xuong']   ?? '') ?: null;
            $chucVu = trim($r['chuc_vu'] ?? '') ?: null;
            $email  = trim($r['email']   ?? '') ?: null;
            $vaiTro = in_array($r['vai_tro'] ?? '', $validRoles) ? $r['vai_tro'] : 'nhanvien';

            // Kiểm tra tồn tại theo ma_nv (nếu có)
            $exists = $maNV ? dbOne("SELECT id FROM thanhvien WHERE ma_nv = ?", [$maNV]) : null;

            if ($exists) {
                if ($mode === 'skip') { $skipped++; continue; }
                // upsert — cập nhật
                dbExec(
                    "UPDATE thanhvien SET ho_ten=?, bo_phan=?, xuong=?, chuc_vu=?, email=?, vai_tro=? WHERE ma_nv=?",
                    [$hoTen, $boPhan, $xuong, $chucVu, $email, $vaiTro, $maNV]
                );
                $updated++;
            } else {
                dbExec(
                    "INSERT INTO thanhvien (ma_nv, ho_ten, bo_phan, xuong, chuc_vu, email, vai_tro) VALUES (?,?,?,?,?,?,?)",
                    [$maNV, $hoTen, $boPhan, $xuong, $chucVu, $email, $vaiTro]
                );
                $inserted++;
            }
        }

        jsonResponse([
            'success'  => true,
            'inserted' => $inserted,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'errors'   => $errors,
        ]);
    }

    // ── Tạo 1 thành viên ─────────────────────────────────────
    if (empty($data['ho_ten'])) { jsonResponse(['error' => 'Thiếu họ tên'], 422); }
    $vaiTro = in_array($data['vai_tro'] ?? '', ['nhanvien','nguoikhacphuc','quanly']) ? $data['vai_tro'] : 'nhanvien';
    dbExec("INSERT INTO thanhvien (ma_nv, ho_ten, bo_phan, xuong, chuc_vu, email, vai_tro) VALUES (?,?,?,?,?,?,?)", [
        $data['ma_nv']   ?? null,
        $data['ho_ten'],
        $data['bo_phan'] ?? null,
        $data['xuong']   ?? null,
        $data['chuc_vu'] ?? null,
        $data['email']   ?? null,
        $vaiTro,
    ]);
    $id  = dbLastId();
    $row = dbOne("SELECT * FROM thanhvien WHERE id=?", [$id]);
    jsonResponse(['success' => true, 'record' => $row], 201);
}

// ── PUT: cập nhật (admin only) ────────────────────────────────
if ($method === 'PUT') {
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = $data['id'] ?? null;
    if (!$id) { jsonResponse(['error' => 'Thiếu id'], 422); }
    if (empty($data['ho_ten'])) { jsonResponse(['error' => 'Thiếu họ tên'], 422); }
    $vaiTro = in_array($data['vai_tro'] ?? '', ['nhanvien','nguoikhacphuc','quanly']) ? $data['vai_tro'] : 'nhanvien';
    dbExec("UPDATE thanhvien SET ma_nv=?, ho_ten=?, bo_phan=?, xuong=?, chuc_vu=?, email=?, vai_tro=? WHERE id=?", [
        $data['ma_nv']   ?? null,
        $data['ho_ten'],
        $data['bo_phan'] ?? null,
        $data['xuong']   ?? null,
        $data['chuc_vu'] ?? null,
        $data['email']   ?? null,
        $vaiTro,
        (int)$id,
    ]);
    $row = dbOne("SELECT * FROM thanhvien WHERE id=?", [(int)$id]);
    jsonResponse(['success' => true, 'record' => $row]);
}

// ── DELETE (admin only) ───────────────────────────────────────
if ($method === 'DELETE') {
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $id = $_GET['id'] ?? null;
    if (!$id) { jsonResponse(['error' => 'Thiếu id'], 422); }
    dbExec("DELETE FROM thanhvien WHERE id=?", [(int)$id]);
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Method not allowed'], 405);
