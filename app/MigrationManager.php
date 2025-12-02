<?php
// app/MigrationManager.php

// Try to load env from the same 'app' directory
if (!isset($db_env) && file_exists(__DIR__ . '/env.php')) {
    $db_env = require __DIR__ . '/env.php';
}

class MigrationManager
{
    private $db;
    private $migrationsDir;
    // private $backupsDir; // REMOVED
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
        // Correct path assumption: migrations folder is in the project root, one level up from 'app'
        $this->migrationsDir = $migrationsDir ?? __DIR__ . "/migrations";
        // $this->backupsDir = $this->migrationsDir . "/backups"; // REMOVED
        $this->logsDir = $this->migrationsDir . "/logs";

        $this->ensureDirectories();
        $this->createMigrationsTable();
        $this->setEnvironment();
    }

    private function ensureDirectories()
    {
        // Only ensure migrations and logs directories (backups dir removed)
        foreach ([$this->migrationsDir, $this->logsDir] as $dir) { 
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
        // Force refresh for web interface consistency
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

    /**
     * Replaces exec("php -l...") for hosts that disable shell commands.
     * WARNING: A reliable syntax check is impossible without 'exec'.
     * This function now acts as a placeholder, relying on 'require_once' 
     * in applyMigration to catch errors.
     */
    private function checkPhpSyntax($file)
    {
        return true; 
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
            // Silently fail logging if permissions are bad, to avoid breaking the app
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

    public function __construct(\$db) {
        \$this->db = \$db;
    }

    public function up() {
        // \$sql = \"CREATE TABLE IF NOT EXISTS example (id INT AUTO_INCREMENT PRIMARY KEY)\";
        // if (!\$this->db->query(\$sql)) throw new Exception(\$this->db->error);
    }

    public function down() {
        // \$sql = \"DROP TABLE IF EXISTS example\";
        // if (!\$this->db->query(\$sql)) throw new Exception(\$this->db->error);
    }
}";

        if (@file_put_contents($filePath, $template) === false) {
            throw new Exception("Could not write migration file: {$filePath}. Check directory permissions.");
        }

        $this->log("Migration created: {$fileName}");
        return $fileName;
    }

    public function getStatus()
    {
        $this->appliedMigrationCache = []; // Clear cache
        $fileMigrations = $this->getMigrationFiles();
        $appliedMigrations = $this->getAppliedMigrations();

        $status = [];

        foreach ($fileMigrations as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            $applied = $appliedMigrations[$migrationName] ?? null;

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

        // Display migrations recorded in DB but missing file on disk (for cleanup prompt)
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
    
    // Function to delete records for migration files that don't exist
    public function cleanupMissingMigrations()
    {
        $allDbMigrations = $this->db->query("SELECT migration FROM " . self::MIGRATION_TABLE);
        if (!$allDbMigrations) {
            throw new Exception("Database query failed during cleanup: " . $this->db->error);
        }
        
        $fileMigrations = array_map(fn($f) => pathinfo($f, PATHINFO_FILENAME), $this->getMigrationFiles());
        $deletedCount = 0;
        
        while ($row = $allDbMigrations->fetch_assoc()) {
            $migrationName = $row['migration'];
            
            if (!in_array($migrationName, $fileMigrations)) {
                $stmt = $this->db->prepare("DELETE FROM " . self::MIGRATION_TABLE . " WHERE migration = ?");
                $stmt->bind_param("s", $migrationName);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $this->log("Cleaned up missing migration file: {$migrationName}", 'info');
                    $deletedCount += $stmt->affected_rows;
                }
                $stmt->close();
            }
        }
        
        $this->appliedMigrationCache = [];
        return ['success' => true, 'deleted' => $deletedCount, 'message' => "Successfully removed {$deletedCount} records for missing migration files from the database."];
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
            return ['success' => false, 'migration' => $migrationName, 'error' => "File not found", 'direction' => $direction];
        }
        
        // Using simplified syntax check due to disabled exec
        if (!$this->checkPhpSyntax($filePath)) {
            return ['success' => false, 'migration' => $migrationName, 'error' => "PHP Syntax Check Failed (Disabled: Cannot confirm)", 'direction' => $direction];
        }

        $className = $this->extractClassName($filePath);
        if (!$className) {
            return ['success' => false, 'migration' => $migrationName, 'error' => "Class not found in file", 'direction' => $direction];
        }

        $checksum = hash_file('sha256', $filePath);

        if ($this->isDryRun) {
            return [
                'success' => true,
                'migration' => $migrationName,
                'direction' => $direction,
                'dry_run' => true
            ];
        }

        try {
            // PHP's require_once will throw a fatal error if syntax is severely broken
            require_once $filePath; 

            if (!class_exists($className)) {
                throw new Exception("Class {$className} not found");
            }

            $migration = new $className($this->db);

            if (!method_exists($migration, $direction)) {
                throw new Exception("Method {$direction}() not found");
            }

            $migration->$direction();
            $duration = round((microtime(true) - $startTime) * 1000);

            $this->recordMigration($migrationName, $batch, $direction, $checksum, $duration);
            $this->log("Executed {$migrationName}->{$direction}() in {$duration}ms", 'success');

            return [
                'success' => true,
                'migration' => $migrationName,
                'direction' => $direction,
                'duration_ms' => $duration
            ];
        } catch (Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            $errorMsg = $e->getMessage();
            $this->log("Failed {$migrationName}->{$direction}: {$errorMsg}", 'error', $e);

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
        catch (\Throwable $e) { 
             $duration = round((microtime(true) - $startTime) * 1000);
             $errorMsg = "PHP Error: " . $e->getMessage();
             $this->log("Failed {$migrationName}->{$direction}: {$errorMsg}", 'error', $e);

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
                    batch = ?, status = 'applied', checksum = ?, executed_at = NOW(), 
                    rolled_back_at = NULL, error_message = NULL, duration_ms = ?
            ");
            $stmt->bind_param("sisisis", $migrationName, $batch, $checksum, $duration, $batch, $checksum, $duration);
            $stmt->execute();
            $stmt->close();
        } else { // down
            $stmt = $this->db->prepare("UPDATE " . self::MIGRATION_TABLE . " SET status = 'rolled_back', rolled_back_at = NOW() WHERE migration = ?");
            $stmt->bind_param("s", $migrationName);
            $stmt->execute();
            $stmt->close();
        }
        $this->appliedMigrationCache = [];
    }

    private function recordMigrationFailure($migrationName, $batch, $checksum, $error, $duration)
    {
        $stmt = $this->db->prepare("
            INSERT INTO " . self::MIGRATION_TABLE . " 
            (migration, batch, status, checksum, executed_at, error_message, duration_ms) 
            VALUES (?, ?, 'failed', ?, NOW(), ?, ?)
            ON DUPLICATE KEY UPDATE 
                batch = ?, status = 'failed', checksum = ?, executed_at = NOW(), 
                error_message = ?, duration_ms = ?
        ");
        
        // FIX: The query has 10 placeholders: 
        // 1. migration (s)
        // 2. batch (i)
        // 3. checksum (s)
        // 4. error (s)
        // 5. duration (i)
        // 6. batch (i)
        // 7. checksum (s)
        // 8. error (s)
        // 9. duration (i)
        // 10. (NOW() is not a bind variable)
        
        // The original code was missing parameters for the UPDATE clause.
        // We need 9 variables: $migrationName, $batch, $checksum, $error, $duration (for INSERT)
        // followed by: $batch, $checksum, $error, $duration (for UPDATE)
        // NOTE: The `executed_at = NOW()` and `status = 'failed'` are literal values, not bound.
        // Let's re-count placeholders:
        // INSERT (5): migration, batch, checksum, error_message, duration_ms
        // UPDATE (4): batch, checksum, error_message, duration_ms
        // TOTAL BIND VARIABLES = 5 + 4 = 9
        
        // New type string: (s, i, s, s, i) for INSERT, (i, s, s, i) for UPDATE -> "sissiisii"
        // Let's check the SQL again:
        // INSERT (5): ?, ?, 'failed', ?, NOW(), ?, ? 
        //             ^ ^          ^       ^  ^
        //             1 2          3       4  5
        // UPDATE (4): batch = ?, status = 'failed', checksum = ?, executed_at = NOW(), error_message = ?, duration_ms = ?
        //                         ^          ^                     ^          ^
        //                         6          7                     8          9
        // TOTAL = 9 placeholders.

        $stmt->bind_param(
            "sissisisi", // 9 variables: s(migration), i(batch), s(checksum), s(error), i(duration), i(batch), s(checksum), s(error), i(duration)
            $migrationName, 
            $batch, 
            $checksum, 
            $error, 
            $duration,
            // UPDATE values:
            $batch, 
            $checksum, 
            $error, 
            $duration
        );
        
        $stmt->execute();
        $stmt->close();
        $this->appliedMigrationCache = [];
    }

    public function rollback($steps = 1)
    {
        if ($steps < 1) throw new Exception("Steps must be at least 1");

        // Rollback last N batches
        $stmt = $this->db->prepare("SELECT DISTINCT batch FROM " . self::MIGRATION_TABLE . " WHERE status = 'applied' ORDER BY batch DESC LIMIT ?");
        $stmt->bind_param('i', $steps);
        $stmt->execute();
        $result = $stmt->get_result();

        $batches = [];
        while ($row = $result->fetch_assoc()) $batches[] = $row['batch'];
        $stmt->close();

        if (empty($batches)) {
            $this->log("No applied migrations to rollback");
            return ['success' => true, 'message' => 'No migrations to rollback.', 'rolled_back' => 0, 'results' => []];
        }

        return $this->executRollback($batches, 'partial');
    }

    public function rollbackAll()
    {
        $result = $this->db->query("SELECT DISTINCT batch FROM " . self::MIGRATION_TABLE . " WHERE status = 'applied' ORDER BY batch DESC");
        $batches = [];
        while ($row = $result->fetch_assoc()) $batches[] = $row['batch'];

        if (empty($batches)) return ['success' => true, 'message' => 'No migrations to rollback.', 'rolled_back' => 0, 'results' => []];

        return $this->executRollback($batches, 'full');
    }

    private function executRollback($batches, $type = 'partial')
    {
        $batchesPlaceholder = implode(',', array_fill(0, count($batches), '?'));
        $sql = "SELECT migration FROM " . self::MIGRATION_TABLE . " WHERE status = 'applied' AND batch IN ({$batchesPlaceholder}) ORDER BY executed_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(str_repeat('i', count($batches)), ...$batches);
        $stmt->execute();
        $result = $stmt->get_result();

        $toRollback = [];
        while ($row = $result->fetch_assoc()) $toRollback[] = $row['migration'] . '.php';
        $stmt->close();

        $results = [];
        foreach ($toRollback as $file) {
            $result = $this->applyMigration($file, 0, 'down');
            $results[] = $result;
            if (!$result['success']) break;
        }

        $rolledBack = count(array_filter($results, fn($r) => $r['success']));
        $this->log("Rollback completed: {$rolledBack} migrations");

        return [
            'success' => true,
            'message' => "Successfully rolled back {$rolledBack} migrations.",
            'rolled_back' => $rolledBack,
            'results' => $results
        ];
    }
    
    /**
     * Retrieves the schema/columns for a given table using pure MySQLi.
     */
    public function describeTable($tableName)
    {
        if (empty($tableName)) throw new Exception("Invalid table name.");
        // Use real_escape_string to prevent SQL injection in the table name
        $tableName = $this->db->real_escape_string($tableName); 
        
        // Using SHOW FULL COLUMNS for comprehensive details
        $result = $this->db->query("SHOW FULL COLUMNS FROM `{$tableName}`");
        
        if ($result === false) {
             throw new Exception("SQL Error: " . $this->db->error);
        }
        
        if ($result->num_rows > 0) return $result->fetch_all(MYSQLI_ASSOC);
        
        throw new Exception("Table '{$tableName}' not found or no columns retrieved.");
    }

    public function getLogs($days = 7)
    {
        $logs = [];
        $now = time();
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', $now - ($i * 86400));
            $logFile = $this->logsDir . '/' . $date . '.log';
            if (file_exists($logFile)) $logs[$date] = file_get_contents($logFile);
        }
        return $logs;
    }

    private function getMigrationFiles()
    {
        $files = @scandir($this->migrationsDir) ?: [];
        $migrations = array_filter($files, fn($f) => preg_match(self::MIGRATION_PATTERN, $f) && is_file($this->migrationsDir . '/' . $f));
        sort($migrations);
        return array_values($migrations);
    }

    private function extractClassName($filePath)
    {
        $content = file_get_contents($filePath);
        if (preg_match('/class\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*{/', $content, $matches)) return $matches[1];
        return null;
    }
}