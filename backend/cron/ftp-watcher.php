<?php
// ftp-watcher.php
// Cron job: Check uploads directory and import compressed log files
// Run every 5 minutes: */5 * * * * php /var/www/html/SSHLD_002/backend/cron/ftp-watcher.php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Importer.php';

$envFile = __DIR__ . '/../.env';
$env = [];
if (file_exists($envFile)) {
    $envContent = parse_ini_file($envFile);
    $env = is_array($envContent) ? $envContent : [];
}

$dbPath = $env['DB_PATH'] ?? dirname(__DIR__) . '/../db/sshld_002.db';
$uploadDir = $env['FTP_UPLOAD_DIR'] ?? dirname(__DIR__) . '/uploads';
$accountId = $env['ACCOUNT_ID'] ?? 'default';

// Resolve absolute paths
if (strpos($dbPath, '/') !== 0) {
    $dbPath = dirname(__DIR__) . '/' . $dbPath;
}
if (strpos($uploadDir, '/') !== 0) {
    $uploadDir = dirname(__DIR__) . '/' . $uploadDir;
}

$db = new \SSHLD\Database($dbPath);
$importer = new \SSHLD\Importer($db, $uploadDir, $accountId);
$result = $importer->processUploadedFiles();

error_log("[SSHLD] Processed: {$result['processed']}, Failed: {$result['failed']}");

$db->close();
?>
