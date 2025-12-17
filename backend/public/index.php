<?php
// index.php
// REST API endpoints for SSHLD_002 dashboard

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Importer.php';

// Load env
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
if (!strpos($dbPath, '/') === 0) {
    $dbPath = dirname(__DIR__) . '/' . $dbPath;
}
if (!strpos($uploadDir, '/') === 0) {
    $uploadDir = dirname(__DIR__) . '/' . $uploadDir;
}

$db = new \SSHLD\Database($dbPath);

// Router
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove /sshld_002 prefix if present (for OVH deployment)
$path = preg_replace('#^/sshld_002#', '', $path);

try {
    if ($path === '/api/events') {
        $limit = $_GET['limit'] ?? 100;
        $offset = $_GET['offset'] ?? 0;
        echo json_encode($db->getEvents($accountId, (int)$limit, (int)$offset));
    }
    elseif ($path === '/api/stats') {
        echo json_encode($db->getDailyStats($accountId));
    }
    elseif ($path === '/api/import' && $method === 'POST') {
        $importer = new \SSHLD\Importer($db, $uploadDir, $accountId);
        $result = $importer->processUploadedFiles();
        echo json_encode($result);
    }
    elseif ($path === '/api/health') {
        echo json_encode(['status' => 'ok', 'timestamp' => date('c')]);
    }
    else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$db->close();
?>
