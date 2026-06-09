<?php
@set_time_limit(120);
@ini_set('display_errors', 0);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ──────────────────────────────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';

    // Danh sách tất cả QR
    if ($action === 'list') {
        $rows = dbAll("SELECT * FROM qrcore ORDER BY xuong, khu_vuc");
        jsonResponse($rows);
    }

    // Danh sách xưởng (distinct)
    if ($action === 'xuong') {
        $rows = dbAll("SELECT DISTINCT xuong FROM qrcore ORDER BY xuong");
        jsonResponse(array_column($rows, 'xuong'));
    }

    // Khu vực theo xưởng
    if ($action === 'khuvuc') {
        $xuong = trim($_GET['xuong'] ?? '');
        if (!$xuong) { jsonResponse([]); }
        $rows = dbAll("SELECT DISTINCT khu_vuc FROM qrcore WHERE xuong = ? ORDER BY khu_vuc", [$xuong]);
        jsonResponse(array_column($rows, 'khu_vuc'));
    }

    // Lookup theo ma_qr
    $maQR = trim($_GET['ma_qr'] ?? '');
    if ($maQR) {
        $row = dbOne("SELECT * FROM qrcore WHERE ma_qr = ?", [$maQR]);
        if (!$row) { jsonResponse(['error' => 'QR không tìm thấy'], 404); }
        jsonResponse($row);
    }

    jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

// ── POST: import (admin only) ─────────────────────────────────
if ($method === 'POST') {
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    if (($data['action'] ?? '') === 'import') {
        $rows    = $data['rows'] ?? [];
        $replace = (bool)($data['replace'] ?? true); // xóa cũ và thay mới
        if (!is_array($rows) || empty($rows)) {
            jsonResponse(['error' => 'Không có dữ liệu'], 422);
        }

        if ($replace) {
            dbExec("DELETE FROM qrcore");
        }

        $inserted = 0; $errors = [];

        db()->beginTransaction();
        try {
            foreach ($rows as $i => $r) {
                $xuong   = trim($r['xuong']    ?? '');
                $khuVuc  = trim($r['khu_vuc']  ?? '');
                $phuTrach = trim($r['phu_trach'] ?? '') ?: null;

                if (!$xuong || !$khuVuc) {
                    $errors[] = "Dòng " . ($i+2) . ": Thiếu Xưởng hoặc Khu vực";
                    continue;
                }

                // Tạo ma_qr từ tên xưởng + khu vực
                $baseQR = generateQRCode($xuong, $khuVuc);

                // Đảm bảo ma_qr duy nhất
                $maQR = $baseQR;
                $suffix = 2;
                while (dbOne("SELECT id FROM qrcore WHERE ma_qr = ?", [$maQR])) {
                    $maQR = $baseQR . '-' . $suffix;
                    $suffix++;
                }

                dbExec(
                    "INSERT INTO qrcore (ma_qr, xuong, khu_vuc, phu_trach) VALUES (?,?,?,?)",
                    [$maQR, $xuong, $khuVuc, $phuTrach]
                );
                $inserted++;
            }
            db()->commit();
        } catch (Exception $ex) {
            db()->rollBack();
            jsonResponse(['error' => 'Lỗi insert: ' . $ex->getMessage()], 500);
        }

        jsonResponse(['success' => true, 'inserted' => $inserted, 'errors' => $errors]);
    }

    jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

// ── DELETE (admin only) ───────────────────────────────────────
if ($method === 'DELETE') {
    if (!isAdmin()) { jsonResponse(['error' => 'Không có quyền'], 403); }
    $id = $_GET['id'] ?? null;
    if (!$id) { jsonResponse(['error' => 'Thiếu id'], 422); }
    dbExec("DELETE FROM qrcore WHERE id = ?", [(int)$id]);
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Method not allowed'], 405);

// ── Helper: sinh mã QR từ tên ────────────────────────────────
function generateQRCode(string $xuong, string $khuVuc): string {
    // Chuyển tiếng Việt → ASCII, lấy viết tắt
    $map = [
        'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a',
        'ă'=>'a','ắ'=>'a','ặ'=>'a','ằ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'â'=>'a','ấ'=>'a','ầ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
        'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e',
        'ê'=>'e','ế'=>'e','ề'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
        'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
        'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o',
        'ô'=>'o','ố'=>'o','ồ'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o',
        'ơ'=>'o','ớ'=>'o','ờ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
        'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
        'ư'=>'u','ứ'=>'u','ừ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
        'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
        'đ'=>'d',
        'À'=>'A','Á'=>'A','Ả'=>'A','Ã'=>'A','Ạ'=>'A',
        'Ă'=>'A','Ắ'=>'A','Ặ'=>'A','Ằ'=>'A','Ẳ'=>'A','Ẵ'=>'A',
        'Â'=>'A','Ấ'=>'A','Ầ'=>'A','Ẩ'=>'A','Ẫ'=>'A','Ậ'=>'A',
        'È'=>'E','É'=>'E','Ẻ'=>'E','Ẽ'=>'E','Ẹ'=>'E',
        'Ê'=>'E','Ế'=>'E','Ề'=>'E','Ể'=>'E','Ễ'=>'E','Ệ'=>'E',
        'Ì'=>'I','Í'=>'I','Ỉ'=>'I','Ĩ'=>'I','Ị'=>'I',
        'Ò'=>'O','Ó'=>'O','Ỏ'=>'O','Õ'=>'O','Ọ'=>'O',
        'Ô'=>'O','Ố'=>'O','Ồ'=>'O','Ổ'=>'O','Ỗ'=>'O','Ộ'=>'O',
        'Ơ'=>'O','Ớ'=>'O','Ờ'=>'O','Ở'=>'O','Ỡ'=>'O','Ợ'=>'O',
        'Ù'=>'U','Ú'=>'U','Ủ'=>'U','Ũ'=>'U','Ụ'=>'U',
        'Ư'=>'U','Ứ'=>'U','Ừ'=>'U','Ử'=>'U','Ữ'=>'U','Ự'=>'U',
        'Ỳ'=>'Y','Ý'=>'Y','Ỷ'=>'Y','Ỹ'=>'Y','Ỵ'=>'Y',
        'Đ'=>'D',
    ];

    $x = strtr($xuong, $map);
    $k = strtr($khuVuc, $map);

    // Lấy phần sau dấu '-' nếu có (vd: "F5 - Tương ớt" → "Tuong ot")
    if (strpos($x, '-') !== false) {
        $parts = explode('-', $x, 2);
        $prefix = trim($parts[0]); // F5
        $suffix = trim($parts[1]); // Tuong ot
        $xCode = preg_replace('/[^A-Za-z0-9]/', '', $prefix) . '-' . preg_replace('/\s+/', '', ucwords(strtolower($suffix)));
    } else {
        $xCode = preg_replace('/[^A-Za-z0-9]/', '', $x);
    }

    // Khu vực: lấy chữ cái đầu mỗi từ hoặc rút gọn
    $kWords = preg_split('/\s+/', trim($k));
    if (count($kWords) <= 2) {
        $kCode = preg_replace('/[^A-Za-z0-9]/', '', implode('', $kWords));
    } else {
        $kCode = implode('', array_map(fn($w) => strtoupper($w[0] ?? ''), $kWords));
    }

    return 'QR-' . $xCode . '-' . $kCode;
}
