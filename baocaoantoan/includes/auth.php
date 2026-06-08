<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isViewer(): bool {
    return !empty($_SESSION[SESSION_VIEWER]);
}

function isAdmin(): bool {
    return !empty($_SESSION[SESSION_ADMIN]);
}

function requireViewer(): void {
    if (!isViewer() && !isAdmin()) {
        header('Location: ' . SITE_URL . '/pages/login.php?role=viewer&redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/pages/login.php?role=admin&redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function tryLogin(string $role, string $password): bool {
    $key = ($role === 'admin') ? 'admin_pass' : 'viewer_pass';
    try {
        $row = dbOne("SELECT gia_tri FROM caidat WHERE ten_key = ?", [$key]);
    } catch (Exception $e) {
        return false;
    }
    if (!$row) return false;

    $stored = $row['gia_tri'];
    // Support both plain MD5 and plain text (fallback)
    $matched = ($stored === md5($password)) || ($stored === $password);
    if ($matched) {
        if ($role === 'admin') {
            $_SESSION[SESSION_ADMIN] = true;
            $_SESSION['role'] = 'admin';
        } else {
            $_SESSION[SESSION_VIEWER] = true;
            $_SESSION['role'] = 'viewer';
        }
        return true;
    }
    return false;
}

function logout(): void {
    session_destroy();
}

// Generate HMAC token for khacphuc links
function generateToken(string $id): string {
    return hash_hmac('sha256', $id, TOKEN_SECRET);
}

function verifyToken(string $id, string $token): bool {
    return hash_equals(generateToken($id), $token);
}
