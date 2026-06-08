<?php
require_once __DIR__ . '/../config/app.php';

function generateBaocaoId(): string {
    return 'BC-' . date('Ymd') . '-' . date('His') . rand(10, 99);
}

function generateGembaId(): string {
    return 'GB-' . date('Ymd') . '-' . date('His') . rand(10, 99);
}

function formatDate(?string $dt): string {
    if (!$dt) return '—';
    $ts = strtotime($dt);
    return $ts ? date('d/m/Y H:i', $ts) : $dt;
}

function formatDateOnly(?string $dt): string {
    if (!$dt) return '—';
    $ts = strtotime($dt);
    return $ts ? date('d/m/Y', $ts) : $dt;
}

function daysRemaining(?string $deadline): ?int {
    if (!$deadline) return null;
    $ts = strtotime($deadline);
    if (!$ts) return null;
    $diff = $ts - strtotime(date('Y-m-d'));
    return (int) round($diff / 86400);
}

function statusLabel(string $status): string {
    $map = [
        'pending'  => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
        'fixed'    => 'Đã KP',
    ];
    return isset($map[$status]) ? $map[$status] : $status;
}

function statusClass(string $status): string {
    $map = [
        'pending'  => 'yellow',
        'approved' => 'blue',
        'rejected' => 'red',
        'fixed'    => 'green',
    ];
    return isset($map[$status]) ? $map[$status] : 'muted';
}

function uploadImage(array $file): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload lỗi: ' . $file['error']);
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        throw new RuntimeException('Định dạng ảnh không hợp lệ');
    }
    $name = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $dest = UPLOAD_DIR . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Không thể lưu ảnh');
    }
    return UPLOAD_URL . $name;
}

function sendWebhook(string $eventKey, array $payload): void {
    require_once __DIR__ . '/db.php';
    $rows = dbAll("SELECT url, url2 FROM webhook WHERE ten_key = ?", [$eventKey]);
    foreach ($rows as $row) {
        foreach ([$row['url'], $row['url2']] as $url) {
            if (!$url) continue;
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }
}

function jsonResponse($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function e($v): string {
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
