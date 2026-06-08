<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

if (empty($_FILES['image'])) {
    jsonResponse(['error' => 'Không có file'], 422);
}

try {
    $url = uploadImage($_FILES['image']);
    jsonResponse(['success' => true, 'url' => $url]);
} catch (RuntimeException $e) {
    jsonResponse(['error' => $e->getMessage()], 422);
}
