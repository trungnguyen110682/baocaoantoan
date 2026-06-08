<?php
/**
 * API endpoint: /api/data
 * GET         → trả về nội dung data.json
 * POST array  → ghi đè toàn bộ (dùng cho admin sync)
 * POST object → append 1 record mới vào đầu mảng (dùng cho baocaomoi)
 */
$DATA_FILE = __DIR__ . '/../data.json';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── GET ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!file_exists($DATA_FILE)) { echo '[]'; exit; }
    $content = file_get_contents($DATA_FILE);
    echo $content !== false ? $content : '[]';
    exit;
}

// ── POST ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Empty body']);
        exit;
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
        exit;
    }

    $tmp = $DATA_FILE . '.tmp';

    // Nếu client gửi 1 object (record đơn) → append vào đầu mảng trên server
    if (isset($data['id']) && isset($data['status'])) {
        // Đọc data hiện tại từ server (source of truth)
        $existing = [];
        if (file_exists($DATA_FILE)) {
            $content = file_get_contents($DATA_FILE);
            if ($content) $existing = json_decode($content, true) ?: [];
        }

        // Kiểm tra trùng ID (tránh double-submit)
        foreach ($existing as $r) {
            if (isset($r['id']) && $r['id'] === $data['id']) {
                echo json_encode(['ok' => true, 'count' => count($existing), 'note' => 'duplicate']);
                exit;
            }
        }

        // Thêm record mới vào đầu
        array_unshift($existing, $data);
        $out = json_encode($existing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($tmp, $out, LOCK_EX);
        rename($tmp, $DATA_FILE);
        echo json_encode(['ok' => true, 'count' => count($existing)]);
        exit;
    }

    // Nếu client gửi array → ghi đè toàn bộ (admin sync, update status...)
    if (is_array($data)) {
        $out = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($tmp, $out, LOCK_EX);
        rename($tmp, $DATA_FILE);
        echo json_encode(['ok' => true, 'count' => count($data)]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Expected array or record object']);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
