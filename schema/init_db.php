<?php
// init_db.php
// Initialize SQLite3 database from schema.sql

$dbPath = __DIR__ . '/../db/sshld_002.db';
$schemaPath = __DIR__ . '/schema.sql';

// Create db directory if not exists
if (!is_dir(dirname($dbPath))) {
    mkdir(dirname($dbPath), 0755, true);
}

try {
    $db = new SQLite3($dbPath);
    $db->busyTimeout(5000);

    // Read and execute schema
    $schema = file_get_contents($schemaPath);
    $db->exec($schema);

    echo "✓ Database initialized at: $dbPath\n";
    echo "✓ Tables created successfully\n";

    $db->close();
} catch (Exception $e) {
    die("✗ Error: " . $e->getMessage() . "\n");
}
?>
