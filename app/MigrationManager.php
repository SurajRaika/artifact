<?php
$db_env = require 'env.php';

class MigrationManager
{
    private $db;
    private $migrationsDir;
    private $backupsDir;
    private $logsDir;
    private $isDryRun = false;
    private $environment = 'production';
    private $appliedMigrationCache = [];
    const MIGRATION_TABLE = 'migrations';
    const MIGRATION_PATTERN = '/^(\d{8})_(\d{6})_(.+)\.php$/';

    public function __construct($db, $migrationsDir = null)
    {
        
        if (!$db) {
            throw new Exception("Database connection is required");
        }

        $this->db = $db;
        $this->migrationsDir = $migrationsDir ?? __DIR__ . "/migrations";
        $this->backupsDir = $this->migrationsDir . "/backups";
        $this->logsDir = $this->migrationsDir . "/logs";

        $this->ensureDirectories();
        $this->createMigrationsTable();
        $this->setEnvironment();
    }

    private function ensureDirectories()
    {
        foreach ([$this->migrationsDir, $this->backupsDir, $this->logsDir] as $dir) {
            if (!is_dir($dir)) {
                if (!@mkdir($dir, 0755, true)) {
                    throw new Exception("Failed to create directory: {$dir}. Check permissions.");
                }
            }
            if (!is_writable($dir)) {
                throw new Exception("Directory not writable: {$dir}. Check permissions.");
            }
        }
    }

    private function createMigrationsTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS " . self::MIGRATION_TABLE . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            status ENUM('applied', 'rolled_back', 'failed', 'pending') DEFAULT 'pending',
            checksum VARCHAR(64),
            executed_at TIMESTAMP NULL,
            rolled_back_at TIMESTAMP NULL,
            error_message LONGTEXT,
            duration_ms INT DEFAULT 0,
            INDEX idx_batch (batch),
            INDEX idx_status (status),
            INDEX idx_executed_at (executed_at)
        )";

        if ($this->db->query($query) === false) {
            throw new Exception("Failed to create migrations table: " . $this->db->error);
        }
    }

    private function setEnvironment()
    {
        $this->environment = getenv('MIGRATION_ENV') ?: 'production';
    }

    public function setDryRun($isDryRun)
    {
        $this->isDryRun = (bool)$isDryRun;
    }

    private function getNextBatch()
    {
        $result = $this->db->query("SELECT MAX(batch) as max_batch FROM " . self::MIGRATION_TABLE . " WHERE status = 'applied'");

        if (!$result) {
            throw new Exception("Database query failed: " . $this->db->error);
        }

        $row = $result->fetch_assoc();
        return (int)($row['max_batch'] ?? 0) + 1;
    }

    private function getAppliedMigrations()
    {
        if (!empty($this->appliedMigrationCache)) {
            return $this->appliedMigrationCache;
        }

        $result = $this->db->query("SELECT migration, batch, status FROM " . self::MIGRATION_TABLE);

        if (!$result) {
            throw new Exception("Failed to fetch applied migrations: " . $this->db->error);
        }

        $applied = [];
        while ($row = $result->fetch_assoc()) {
            $applied[$row['migration']] = $row;
        }

        $this->appliedMigrationCache = $applied;
        return $applied;
    }

    private function checkPhpSyntax($file)
    {
        $output = [];
        $return = 0;
        exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return);
        return $return === 0;
    }

    private function log($message, $type = 'info', $exception = null)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$type}] {$message}";

        if ($exception) {
            $logEntry .= " | Exception: " . $exception->getMessage();
        }

        $logEntry .= "\n";
        $logFile = $this->logsDir . '/' . date('Y-m-d') . '.log';

        if (!@file_put_contents($logFile, $logEntry, FILE_APPEND)) {
            error_log("Failed to write migration log: {$logFile}");
        }
    }

    public function createMigrationFile($className)
    {
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $className)) {
            throw new Exception("Invalid class name. Must start with uppercase letter and contain only alphanumeric characters.");
        }

        $timestamp = date('Ymd_His');
        $fileName = $timestamp . '_' . $className . '.php';
        $filePath = $this->migrationsDir . '/' . $fileName;

        if (file_exists($filePath)) {
            throw new Exception("Migration file '$fileName' already exists.");
        }

        $template = "<?php

            class {$className} {
            private \$db;
            private \$table = ''; // Set your table name here

            public function __construct(\$db) {
                \$this->db = \$db;
            }

            /**
             * Run the migration - apply schema changes.
             * Always check if table/column exists before creating.
             */
            public function up() {
                // Example:
                // \$sql = \"CREATE TABLE IF NOT EXISTS users (
                //     id INT AUTO_INCREMENT PRIMARY KEY,
                //     name VARCHAR(255) NOT NULL,
                //     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                // )\";
                // 
                // if (\$this->db->query(\$sql) === false) {
                //     throw new Exception(\"Error: \" . \$this->db->error);
                // }
            }

            /**
             * Reverse the migration - undo schema changes.
             * Must be able to run safely even if up() partially failed.
             * Don't drop tables - use ALTER or DROP COLUMN instead for safety.
             */
            public function down() {
                // Example:
                // \$sql = \"DROP TABLE IF EXISTS users\";
                // 
                // if (\$this->db->query(\$sql) === false) {
                //     throw new Exception(\"Error: \" . \$this->db->error);
                // }
            }
        }
        ";

        if (@file_put_contents($filePath, $template) === false) {
            throw new Exception("Could not write migration file: {$filePath}. Check directory permissions.");
        }

        $this->log("Migration created: {$fileName}");
        return $fileName;
    }

    public function getStatus()
    {
        // Always refresh cache to get latest state
        $this->appliedMigrationCache = [];

        $fileMigrations = $this->getMigrationFiles();
        $appliedMigrations = $this->getAppliedMigrations();

        $status = [];

        foreach ($fileMigrations as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            $applied = $appliedMigrations[$migrationName] ?? null;

            // Only show as applied if status is actually 'applied'
            $displayStatus = 'pending';
            $batch = null;

            if ($applied) {
                $displayStatus = $applied['status'];
                $batch = $applied['batch'] ?? null;
            }

            $status[] = [
                'name' => $migrationName,
                'file' => $file,
                'status' => $displayStatus,
                'batch' => $batch,
                'file_exists' => true,
            ];
        }

        // Show missing files (in DB but not on disk)
        foreach ($appliedMigrations as $name => $data) {
            if (!in_array($name . '.php', $fileMigrations)) {
                $status[] = [
                    'name' => $name,
                    'file' => 'N/A',
                    'status' => $data['status'] . ' (MISSING FILE)',
                    'batch' => $data['batch'],
                    'file_exists' => false,
                ];
            }
        }

        usort($status, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $status;
    }

    public function getPendingMigrations()
    {
        $fileMigrations = $this->getMigrationFiles();
        $applied = $this->getAppliedMigrations();

        $pending = [];
        foreach ($fileMigrations as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            $isApplied = isset($applied[$migrationName]) && $applied[$migrationName]['status'] === 'applied';

            if (!$isApplied) {
                $pending[] = $file;
            }
        }

        return $pending;
    }

    public function migrate($target = null)
    {
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            $this->log("No pending migrations");
            return ['success' => true, 'message' => 'No pending migrations.', 'ran' => 0, 'results' => []];
        }

        $batch = $this->getNextBatch();
        $results = [];

        foreach ($pending as $file) {
            $result = $this->applyMigration($file, $batch, 'up');
            $results[] = $result;

            if (!$result['success']) {
                $this->log("Migration halted due to failure in {$file}", 'warn');
                break;
            }

            if ($target && pathinfo($file, PATHINFO_FILENAME) === $target) {
                break;
            }
        }

        $ran = count(array_filter($results, fn($r) => $r['success']));
        $this->log("Migration batch {$batch} completed: {$ran} migrations applied");

        return [
            'success' => true,
            'message' => "Successfully applied {$ran} migrations.",
            'ran' => $ran,
            'batch' => $batch,
            'results' => $results
        ];
    }

    private function applyMigration($file, $batch, $direction)
    {
        $filePath = $this->migrationsDir . '/' . $file;
        $migrationName = pathinfo($file, PATHINFO_FILENAME);
        $startTime = microtime(true);

        if (!file_exists($filePath)) {
            $errorMsg = "Migration file not found: {$file}";
            $this->log($errorMsg, 'error');
            return ['success' => false, 'migration' => $migrationName, 'error' => $errorMsg, 'direction' => $direction];
        }

        if (!$this->checkPhpSyntax($filePath)) {
            $errorMsg = "PHP syntax error in {$file}";
            $this->log($errorMsg, 'error');
            return ['success' => false, 'migration' => $migrationName, 'error' => $errorMsg, 'direction' => $direction];
        }

        $className = $this->extractClassName($filePath);
        if (!$className) {
            $errorMsg = "Could not find class definition in {$file}";
            $this->log($errorMsg, 'error');
            return ['success' => false, 'migration' => $migrationName, 'error' => $errorMsg, 'direction' => $direction];
        }

        $checksum = hash_file('sha256', $filePath);

        if ($this->isDryRun) {
            $this->log("DRY RUN: Would execute {$migrationName}->{$direction}() in batch {$batch}", 'info');
            return [
                'success' => true,
                'migration' => $migrationName,
                'direction' => $direction,
                'dry_run' => true
            ];
        }

        try {
            require_once $filePath;

            if (!class_exists($className)) {
                throw new Exception("Class {$className} not found in {$file}");
            }

            $migration = new $className($this->db);

            if (!method_exists($migration, $direction)) {
                throw new Exception("Method {$direction}() not found in {$className}");
            }

            $migration->$direction();
            $duration = round((microtime(true) - $startTime) * 1000);

            // Record in database
            $this->recordMigration($migrationName, $batch, $direction, $checksum, $duration);

            $this->log("Successfully executed {$migrationName}->{$direction}() in {$duration}ms", 'success');

            return [
                'success' => true,
                'migration' => $migrationName,
                'direction' => $direction,
                'duration_ms' => $duration
            ];
        } catch (Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $errorMsg = $e->getMessage();
            $this->log("Failed executing {$migrationName}->{$direction}(): {$errorMsg}", 'error', $e);

            if ($direction === 'up') {
                $this->recordMigrationFailure($migrationName, $batch, $checksum, $errorMsg, $duration);
            }

            return [
                'success' => false,
                'migration' => $migrationName,
                'error' => $errorMsg,
                'direction' => $direction,
                'duration_ms' => $duration
            ];
        }
    }

    private function recordMigration($migrationName, $batch, $direction, $checksum, $duration)
    {
        if ($direction === 'up') {
            $stmt = $this->db->prepare("
                INSERT INTO " . self::MIGRATION_TABLE . " 
                (migration, batch, status, checksum, executed_at, duration_ms) 
                VALUES (?, ?, 'applied', ?, NOW(), ?)
                ON DUPLICATE KEY UPDATE 
                    batch = ?, 
                    status = 'applied', 
                    checksum = ?, 
                    executed_at = NOW(), 
                    rolled_back_at = NULL,
                    error_message = NULL,
                    duration_ms = ?
            ");

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            if (!$stmt->bind_param("sisisis", $migrationName, $batch, $checksum, $duration, $batch, $checksum, $duration)) {
                throw new Exception("Bind failed: " . $stmt->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $stmt->close();
        } else { // down
            $stmt = $this->db->prepare("
                UPDATE " . self::MIGRATION_TABLE . " 
                SET status = 'rolled_back', 
                    rolled_back_at = NOW(),
                    error_message = NULL
                WHERE migration = ?
            ");

            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            if (!$stmt->bind_param("s", $migrationName)) {
                throw new Exception("Bind failed: " . $stmt->error);
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            $stmt->close();
        }

        // Clear cache
        $this->appliedMigrationCache = [];
    }

    private function recordMigrationFailure($migrationName, $batch, $checksum, $error, $duration)
    {
        $stmt = $this->db->prepare("
            INSERT INTO " . self::MIGRATION_TABLE . " 
            (migration, batch, status, checksum, executed_at, error_message, duration_ms) 
            VALUES (?, ?, 'failed', ?, NOW(), ?, ?)
            ON DUPLICATE KEY UPDATE 
                batch = ?, 
                status = 'failed', 
                checksum = ?, 
                executed_at = NOW(), 
                error_message = ?,
                duration_ms = ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->bind_param("sissis", $migrationName, $batch, $checksum, $error, $duration, $batch, $checksum, $error, $duration)) {
            throw new Exception("Bind failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        $this->appliedMigrationCache = [];
    }

    public function rollback($steps = 1)
    {
        if ($steps < 1) {
            throw new Exception("Steps must be at least 1");
        }

        $stmt = $this->db->prepare("
            SELECT DISTINCT batch FROM " . self::MIGRATION_TABLE . " 
            WHERE status = 'applied' 
            ORDER BY batch DESC 
            LIMIT ?
        ");

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param('i', $steps);
        $stmt->execute();
        $result = $stmt->get_result();

        $batches = [];
        while ($row = $result->fetch_assoc()) {
            $batches[] = $row['batch'];
        }
        $stmt->close();

        if (empty($batches)) {
            $this->log("No applied migrations to rollback");
            return ['success' => true, 'message' => 'No migrations to rollback.', 'rolled_back' => 0, 'results' => []];
        }

        return $this->executRollback($batches, 'partial');
    }

    public function rollbackAll()
    {
        $result = $this->db->query("
            SELECT DISTINCT batch FROM " . self::MIGRATION_TABLE . " 
            WHERE status = 'applied' 
            ORDER BY batch DESC
        ");

        if (!$result) {
            throw new Exception("Query failed: " . $this->db->error);
        }

        $batches = [];
        while ($row = $result->fetch_assoc()) {
            $batches[] = $row['batch'];
        }

        if (empty($batches)) {
            $this->log("No migrations to rollback");
            return ['success' => true, 'message' => 'No migrations to rollback.', 'rolled_back' => 0, 'results' => []];
        }

        return $this->executRollback($batches, 'full');
    }

    private function executRollback($batches, $type = 'partial')
    {
        $batchesPlaceholder = implode(',', array_fill(0, count($batches), '?'));

        $sql = "
            SELECT migration FROM " . self::MIGRATION_TABLE . " 
            WHERE status = 'applied' AND batch IN ({$batchesPlaceholder}) 
            ORDER BY executed_at DESC
        ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param(str_repeat('i', count($batches)), ...$batches);
        $stmt->execute();
        $result = $stmt->get_result();

        $toRollback = [];
        while ($row = $result->fetch_assoc()) {
            $toRollback[] = $row['migration'] . '.php';
        }
        $stmt->close();

        $results = [];
        foreach ($toRollback as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            $result = $this->applyMigration($file, 0, 'down');
            $results[] = $result;

            if (!$result['success']) {
                $this->log("Rollback failed for {$file}. Aborting remaining rollbacks.", 'error');
                break;
            }
        }

        $rolledBack = count(array_filter($results, fn($r) => $r['success']));
        $typeStr = $type === 'full' ? 'Full rollback' : 'Rollback';
        $this->log("{$typeStr} completed: {$rolledBack} migrations rolled back");

        return [
            'success' => true,
            'message' => "Successfully rolled back {$rolledBack} migrations.",
            'rolled_back' => $rolledBack,
            'results' => $results
        ];
    }

    public function backupDatabase($backupName = null)
    {
        $timestamp = date('Ymd_His');
        $backupName = $backupName ?? "backup_{$timestamp}";
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $backupName) . "_{$timestamp}.sql";
        $filePath = $this->backupsDir . '/' . $fileName;

        try {
            $dbName = $this->getDatabase();
            $backupCmd = $this->buildBackupCommand($dbName, $filePath);

            exec($backupCmd, $output, $return);
            if ($return !== 0) {
                throw new Exception("Backup command failed: " . implode("\n", $output));
            }

            if (!file_exists($filePath)) {
                throw new Exception("Backup file not created at {$filePath}");
            }

            $size = filesize($filePath);
            $this->log("Database backup created: {$fileName} (" . $this->formatBytes($size) . ")");

            return ['success' => true, 'file' => $fileName, 'path' => $filePath, 'size' => $size];
        } catch (Exception $e) {
            $this->log("Backup failed: " . $e->getMessage(), 'error', $e);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    private function buildBackupCommand($dbName, $filePath)
    {
            global $db_env;

        // Get connection details
        $host = $db_env['DB_HOST'];
        $user = $db_env['DB_USER'];
        $pass = $db_env['DB_PASS'];

        if (strpos($host, 'via TCP/IP') !== false) {
            preg_match('/^.*via TCP\/IP.*:(\d+)/', $host, $matches);
            $port = $matches[1] ?? 3306;
        } else {
            $port = 3306;
        }

        // Build mysqldump command
        $cmd = "mariadb-dump --host={$host} --port={$port} --user={$user}";

        if ($pass) {
            $cmd .= " --password=" . escapeshellarg($pass);
        }

        // Ignore the migrations table
        $cmd .= " --ignore-table=" . escapeshellarg("{$dbName}.migrations");

        // Dump the whole database except migrations
        $cmd .= " " . escapeshellarg($dbName) . " > " . escapeshellarg($filePath);

        return $cmd;
    }


    private function getDatabase()
    {
        $result = $this->db->query("SELECT DATABASE() as db");
        $row = $result->fetch_assoc();
        return $row['db'] ?? throw new Exception("Cannot determine database name");
    }

    public function getBackups()
    {
        $files = @scandir($this->backupsDir) ?: [];
        $backups = array_filter($files, fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'sql');

        rsort($backups);

        return array_map(fn($f) => [
            'file' => $f,
            'path' => $this->backupsDir . '/' . $f,
            'size' => filesize($this->backupsDir . '/' . $f),
            'created' => filemtime($this->backupsDir . '/' . $f)
        ], $backups);
    }

    public function restoreBackup($backupFile)
    {
        if (!file_exists($backupFile)) {
            throw new Exception("Backup file not found: {$backupFile}");
        }

        if (!is_readable($backupFile)) {
            throw new Exception("Backup file not readable: {$backupFile}");
        }

        try {
            $dbName = $this->getDatabase();
    global $db_env;

            $host = $db_env['DB_HOST'] ?? 'localhost';
            $user = $db_env['DB_USER'];
            $pass = $db_env['DB_PASS'] ?? '';

            // IMPORTANT:
            // - Use sh -c so that shell redirection (< file.sql) works.
            // - escapeshellarg() is used for safety.
            // - Add --password= so MariaDB does not reject login.
            $cmd = "mariadb "
                . "--host=" . escapeshellarg($host) . " "
                . "--user=" . escapeshellarg($user) . " "
                . "--password=" . escapeshellarg($pass) . " "
                . escapeshellarg($dbName)
                . " < " . escapeshellarg($backupFile);

            // Wrap inside sh -c
            $restoreCmd = "sh -c " . escapeshellarg($cmd);

            exec($restoreCmd . " 2>&1", $output, $return);

            if ($return !== 0) {
                throw new Exception("Restore failed: " . implode("\n", $output));
            }

            $this->appliedMigrationCache = [];
            $this->log("Database restored from: " . basename($backupFile));

            return ['success' => true, 'message' => 'Database restored successfully'];
        } catch (Exception $e) {
            $this->log("Restore failed: " . $e->getMessage(), 'error', $e);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Returns the description (schema) of a specified database table.
     *
     * @param string $tableName The name of the table to describe.
     * @return array An array of column descriptions.
     * @throws Exception If the table name is invalid or the table is not found.
     */
    public function describeTable($tableName)
    {
        // Basic sanitization: Ensure the table name only contains alphanumeric characters and underscores.
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new Exception("Invalid table name '{$tableName}'.");
        }

        // Use SHOW COLUMNS FROM for broad MySQL/MariaDB compatibility
        $sql = "SHOW COLUMNS FROM `{$tableName}`";

        try {
            // Assuming $this->db is a MySQLi connection object
            $result = $this->db->query($sql);

            if ($result && $result->num_rows > 0) {
                $description = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $description;
            }

            // If the query worked but returned 0 rows, the table likely doesn't exist
            if (!$result || $this->db->errno) {
                throw new Exception("Database error: " . $this->db->error);
            }

            throw new Exception("Table '{$tableName}' not found or has no columns.");
        } catch (Exception $e) {
            // Re-throw exception to be caught by CLI handler
            throw $e;
        }
    }
    public function getLogs($days = 7)
    {
        $logs = [];
        $now = time();

        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', $now - ($i * 86400));
            $logFile = $this->logsDir . '/' . $date . '.log';

            if (file_exists($logFile) && is_readable($logFile)) {
                $logs[$date] = file_get_contents($logFile);
            }
        }

        return $logs;
    }

    private function getMigrationFiles()
    {
        $files = @scandir($this->migrationsDir) ?: [];

        $migrations = array_filter($files, function ($f) {
            return preg_match(self::MIGRATION_PATTERN, $f) && is_file($this->migrationsDir . '/' . $f);
        });

        sort($migrations);
        return array_values($migrations);
    }

    private function extractClassName($filePath)
    {
        $content = file_get_contents($filePath);

        if (preg_match('/class\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*{/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    // Public getters for CLI
    public function getLogsDir()
    {
        return $this->logsDir;
    }

    public function getMigrationsDir()
    {
        return $this->migrationsDir;
    }

    public function getBackupsDir()
    {
        return $this->backupsDir;
    }
}
