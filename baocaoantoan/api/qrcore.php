<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$maQR = trim($_GET['ma_qr'] ?? '');
if (!$maQR) {
    $rows = dbAll("SELECT * FROM qrcore ORDER BY xuong, khu_vuc");
    jsonResponse($rows);
}
$row = dbOne("SELECT * FROM qrcore WHERE ma_qr = ?", [$maQR]);
if (!$row) { jsonResponse(['error' => 'QR không tìm thấy'], 404); }
jsonResponse($row);
