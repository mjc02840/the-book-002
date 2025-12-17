<?php
// Database.php
// SQLite3 database interface for SSHLD_002

namespace SSHLD;

class Database {
    private $db;
    private $dbPath;

    public function __construct($dbPath) {
        $this->dbPath = $dbPath;
        $this->ensureDbExists();
        $this->connect();
    }

    private function ensureDbExists() {
        // Create db directory if not exists
        $dbDir = dirname($this->dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        // Initialize database if it doesn't exist
        if (!file_exists($this->dbPath)) {
            $schemaPath = dirname(__DIR__) . '/../schema/schema.sql';
            if (file_exists($schemaPath)) {
                try {
                    $tempDb = new \SQLite3($this->dbPath);
                    $tempDb->busyTimeout(5000);
                    $schema = file_get_contents($schemaPath);
                    $tempDb->exec($schema);
                    $tempDb->close();
                } catch (\Exception $e) {
                    error_log("Database initialization error: " . $e->getMessage());
                }
            }
        }
    }

    private function connect() {
        try {
            $this->db = new \SQLite3($this->dbPath);
            $this->db->busyTimeout(5000);
        } catch (\Exception $e) {
            die("Database error: " . $e->getMessage());
        }
    }

    public function insertEvents($events, $accountId = 'default') {
        $stmt = $this->db->prepare('
            INSERT INTO events (account_id, timestamp, source_ip, username, action, service, port, details)
            VALUES (:account_id, :timestamp, :source_ip, :username, :action, :service, :port, :details)
        ');

        foreach ($events as $event) {
            $stmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);
            $stmt->bindValue(':timestamp', $event['timestamp'] ?? date('c'), \SQLITE3_TEXT);
            $stmt->bindValue(':source_ip', $event['source_ip'] ?? null, \SQLITE3_TEXT);
            $stmt->bindValue(':username', $event['username'] ?? null, \SQLITE3_TEXT);
            $stmt->bindValue(':action', $event['action'] ?? null, \SQLITE3_TEXT);
            $stmt->bindValue(':service', $event['service'] ?? null, \SQLITE3_TEXT);
            $stmt->bindValue(':port', $event['port'] ?? 22, \SQLITE3_INTEGER);
            $stmt->bindValue(':details', json_encode($event['details'] ?? []), \SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    public function getEvents($accountId = 'default', $limit = 100, $offset = 0) {
        $stmt = $this->db->prepare('
            SELECT * FROM events
            WHERE account_id = :account_id
            ORDER BY timestamp DESC
            LIMIT :limit OFFSET :offset
        ');
        $stmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);
        $stmt->bindValue(':limit', (int)$limit, \SQLITE3_INTEGER);
        $stmt->bindValue(':offset', (int)$offset, \SQLITE3_INTEGER);

        $result = $stmt->execute();
        $events = [];
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            $events[] = $row;
        }
        return $events;
    }

    public function getDailyStats($accountId = 'default') {
        $stmt = $this->db->prepare('
            SELECT * FROM daily_stats
            WHERE account_id = :account_id
            ORDER BY stat_date DESC
            LIMIT 30
        ');
        $stmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);

        $result = $stmt->execute();
        $stats = [];
        while ($row = $result->fetchArray(\SQLITE3_ASSOC)) {
            $stats[] = $row;
        }
        return $stats;
    }

    public function generateDailyStats($accountId = 'default', $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        // Check if stats already exist for this date
        $checkStmt = $this->db->prepare('
            SELECT id FROM daily_stats
            WHERE account_id = :account_id AND stat_date = :date
        ');
        $checkStmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);
        $checkStmt->bindValue(':date', $date, \SQLITE3_TEXT);
        $checkResult = $checkStmt->execute();
        if ($checkResult->fetchArray(\SQLITE3_ASSOC)) {
            return; // Already exists
        }

        // Get stats for the day
        $dateStart = $date . ' 00:00:00';
        $dateEnd = $date . ' 23:59:59';

        // Count failed attempts
        $failedStmt = $this->db->prepare('
            SELECT COUNT(*) as count FROM events
            WHERE account_id = :account_id AND timestamp >= :start AND timestamp <= :end AND action = "failed_login"
        ');
        $failedStmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);
        $failedStmt->bindValue(':start', $dateStart, \SQLITE3_TEXT);
        $failedStmt->bindValue(':end', $dateEnd, \SQLITE3_TEXT);
        $failedResult = $failedStmt->execute();
        $failedRow = $failedResult->fetchArray(\SQLITE3_ASSOC);
        $failedAttempts = $failedRow['count'] ?? 0;

        // Count successful logins
        $successStmt = $this->db->prepare('
            SELECT COUNT(*) as count FROM events
            WHERE account_id = :account_id AND timestamp >= :start AND timestamp <= :end AND action = "success"
        ');
        $successStmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);
        $successStmt->bindValue(':start', $dateStart, \SQLITE3_TEXT);
        $successStmt->bindValue(':end', $dateEnd, \SQLITE3_TEXT);
        $successResult = $successStmt->execute();
        $successRow = $successResult->fetchArray(\SQLITE3_ASSOC);
        $successAttempts = $successRow['count'] ?? 0;

        // Count unique IPs
        $ipsStmt = $this->db->prepare('
            SELECT COUNT(DISTINCT source_ip) as count FROM events
            WHERE account_id = :account_id AND timestamp >= :start AND timestamp <= :end
        ');
        $ipsStmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);
        $ipsStmt->bindValue(':start', $dateStart, \SQLITE3_TEXT);
        $ipsStmt->bindValue(':end', $dateEnd, \SQLITE3_TEXT);
        $ipsResult = $ipsStmt->execute();
        $ipsRow = $ipsResult->fetchArray(\SQLITE3_ASSOC);
        $uniqueIps = $ipsRow['count'] ?? 0;

        // Get top attacker
        $topStmt = $this->db->prepare('
            SELECT source_ip, COUNT(*) as count FROM events
            WHERE account_id = :account_id AND timestamp >= :start AND timestamp <= :end AND action = "failed_login"
            GROUP BY source_ip
            ORDER BY count DESC
            LIMIT 1
        ');
        $topStmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);
        $topStmt->bindValue(':start', $dateStart, \SQLITE3_TEXT);
        $topStmt->bindValue(':end', $dateEnd, \SQLITE3_TEXT);
        $topResult = $topStmt->execute();
        $topRow = $topResult->fetchArray(\SQLITE3_ASSOC);
        $topAttackerIp = $topRow['source_ip'] ?? null;
        $topAttackerCount = $topRow['count'] ?? 0;

        // Calculate risk score
        $riskScore = min(100, (int)(($failedAttempts / 50) * 20 + ($uniqueIps / 100) * 30));

        // Insert stats
        $insertStmt = $this->db->prepare('
            INSERT INTO daily_stats (account_id, stat_date, failed_attempts, successful_logins, unique_ips, top_attacker_ip, top_attacker_count, risk_score)
            VALUES (:account_id, :stat_date, :failed_attempts, :successful_logins, :unique_ips, :top_attacker_ip, :top_attacker_count, :risk_score)
        ');
        $insertStmt->bindValue(':account_id', $accountId, \SQLITE3_TEXT);
        $insertStmt->bindValue(':stat_date', $date, \SQLITE3_TEXT);
        $insertStmt->bindValue(':failed_attempts', $failedAttempts, \SQLITE3_INTEGER);
        $insertStmt->bindValue(':successful_logins', $successAttempts, \SQLITE3_INTEGER);
        $insertStmt->bindValue(':unique_ips', $uniqueIps, \SQLITE3_INTEGER);
        $insertStmt->bindValue(':top_attacker_ip', $topAttackerIp, \SQLITE3_TEXT);
        $insertStmt->bindValue(':top_attacker_count', $topAttackerCount, \SQLITE3_INTEGER);
        $insertStmt->bindValue(':risk_score', $riskScore, \SQLITE3_INTEGER);
        $insertStmt->execute();
    }

    public function close() {
        $this->db->close();
    }
}
?>
