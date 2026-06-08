<?php
/**
 * API endpoint: /api/datagemba
 * GET  → trả về nội dung datagemba.json
 * POST → ghi toàn bộ array JSON vào datagemba.json
 */

$DATA_FILE = __DIR__ . '/../datagemba.json';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!file_exists($DATA_FILE)) {
        echo '[]';
        exit;
    }
    $content = file_get_contents($DATA_FILE);
    echo $content !== false ? $content : '[]';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Empty body']);
        exit;
    }

    $data = json_decode($raw);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid JSON: ' . json_last_error_msg()]);
        exit;
    }

    $tmp = $DATA_FILE . '.tmp';
    $written = file_put_contents($tmp, $raw, LOCK_EX);
    if ($written === false) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Cannot write file. Check folder permissions.']);
        exit;
    }
    rename($tmp, $DATA_FILE);

    echo json_encode(['ok' => true, 'count' => is_array($data) ? count($data) : 1]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
