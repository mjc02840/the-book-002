<?php
// Importer.php
// Handles importing compressed JSON log files from agent uploads

namespace SSHLD;

class Importer {
    private $db;
    private $uploadDir;
    private $accountId;

    public function __construct(Database $db, $uploadDir, $accountId = 'default') {
        $this->db = $db;
        $this->uploadDir = $uploadDir;
        $this->accountId = $accountId;
    }

    public function processUploadedFiles() {
        if (!is_dir($this->uploadDir)) {
            return ['processed' => 0, 'failed' => 0, 'message' => 'Upload directory not found'];
        }

        $files = glob($this->uploadDir . '/*.json.gz');
        $processed = 0;
        $failed = 0;

        foreach ($files as $file) {
            try {
                $this->importFile($file);
                unlink($file);  // Delete after import
                $processed++;
            } catch (\Exception $e) {
                error_log("Import failed for $file: " . $e->getMessage());
                $failed++;
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }

    private function importFile($filePath) {
        // Decompress
        $gzipped = file_get_contents($filePath);
        $json = gzdecode($gzipped);

        if ($json === false) {
            throw new \Exception("Failed to decompress file");
        }

        $events = json_decode($json, true);

        if (!is_array($events)) {
            throw new \Exception("Invalid JSON in: $filePath");
        }

        // Insert into DB
        if (!empty($events)) {
            $this->db->insertEvents($events, $this->accountId);

            // Generate daily stats for the events' dates
            foreach ($events as $event) {
                if (isset($event['timestamp'])) {
                    $date = substr($event['timestamp'], 0, 10);
                    $this->db->generateDailyStats($this->accountId, $date);
                }
            }
        }
    }
}
?>
