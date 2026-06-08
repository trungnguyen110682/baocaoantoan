<?php
// Deploy webhook - chỉ cho phép gọi với đúng token
define('DEPLOY_TOKEN', 'msi-deploy-2026-secret');
define('GITHUB_REPO', 'trungnguyen110682/baocaoantoan');
define('DEPLOY_DIR',  __DIR__);

header('Content-Type: text/plain; charset=utf-8');

// Kiểm tra token
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== DEPLOY_TOKEN) {
    http_response_code(403);
    die("Unauthorized");
}

echo "=== Deploy started: " . date('Y-m-d H:i:s') . " ===\n";

// Tải source code từ GitHub (zip archive)
$zipUrl  = "https://github.com/" . GITHUB_REPO . "/archive/refs/heads/main.zip";
$zipFile = sys_get_temp_dir() . '/deploy_' . time() . '.zip';

echo "Downloading from GitHub...\n";
$ctx = stream_context_create(['http' => [
    'timeout'       => 60,
    'user_agent'    => 'Deploy-Script/1.0',
    'follow_location' => true,
]]);
$data = file_get_contents($zipUrl, false, $ctx);
if ($data === false) {
    http_response_code(500);
    die("ERROR: Cannot download from GitHub");
}
file_put_contents($zipFile, $data);
echo "Downloaded " . round(strlen($data)/1024) . " KB\n";

// Giải nén
$zip = new ZipArchive();
if ($zip->open($zipFile) !== true) {
    http_response_code(500);
    die("ERROR: Cannot open zip");
}

$extracted = 0;
$skipped   = 0;
$prefix    = 'baocaoantoan-main/baocaoantoan/'; // path inside zip

for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (strpos($name, $prefix) !== 0) continue; // bỏ qua file ngoài baocaoantoan/

    $rel  = substr($name, strlen($prefix)); // path tương đối
    if ($rel === '' || $rel === 'deploy.php') continue; // không ghi đè chính nó

    $dest = DEPLOY_DIR . '/' . $rel;

    if (substr($name, -1) === '/') {
        // Thư mục
        if (!is_dir($dest)) mkdir($dest, 0755, true);
    } else {
        // File
        $dir = dirname($dest);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dest, $zip->getFromIndex($i));
        $extracted++;
    }
}
$zip->close();
unlink($zipFile);

echo "Extracted: $extracted files\n";
echo "=== Deploy completed successfully ===\n";
