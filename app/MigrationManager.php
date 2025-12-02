<?php

class MigrationManager {
    private $db;
    private $migrationsDir;
    private $backupsDir;
    private $logsDir;
    private $isDryRun = false;
    // Set a default environment to distinguish dry-run logs/status
    private $environment = 'default'; 

    // --- Core Setup ---

    public function __construct($db, $migrationsDir = __DIR__ . "/migrations") {
        $this->db = $db;
        
        // Assume 'app' is the root of the project structure where config and manager live
        // Migrations will be placed in /migrations relative to this file's location
        $this->migrationsDir = str_replace(['/app', '\\app'], '', $migrationsDir);
        $this->backupsDir = $this->migrationsDir . "/backups";
        $this->logsDir = $this->migrationsDir . "/logs";

        $this->ensureDirectories();
        $this->createMigrationsTable();
    }

    private function ensureDirectories() {
        foreach ([$this->migrationsDir, $this->backupsDir, $this->logsDir] as $dir) {
            if (!is_dir($dir)) {
                // Suppress errors for mkdir and log the issue if it fails
                if (!@mkdir($dir, 0755, true)) {
                    throw new Exception("Failed to create directory: " . $dir);
                }
            }
        }
    }

    private function createMigrationsTable() {
        // Ensure the table for tracking migrations exists
        $query = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INT NOT NULL,
            status ENUM('applied', 'rolled_back', 'failed') DEFAULT 'applied',
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            rolled_back_at TIMESTAMP NULL,
            checksum VARCHAR(64),
            error_message TEXT
        )";

        if ($this->db->query($query) === false) {
            throw new Exception("Failed to create migrations table: " . $this->db->error);
        }
    }

    // --- Utility Functions ---

    public function setDryRun($isDryRun) {
        $this->isDryRun = $isDryRun;
        $this->environment = $isDryRun ? 'dry-run' : 'default';
    }

    private function getNextBatch() {
        $result = $this->db->query("SELECT MAX(batch) as max_batch FROM migrations WHERE status = 'applied'");
        $row = $result->fetch_assoc();
        return ($row['max_batch'] ?? 0) + 1;
    }

    private function checkPhpSyntax($file) {
        $output = [];
        $return = 0;
        // Check syntax using the PHP interpreter
        exec("php -l " . escapeshellarg($file), $output, $return);
        return $return === 0;
    }

    private function log($message, $type = 'info') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}][{$this->environment}][{$type}] {$message}\n";
        
        // Log to a daily log file
        $logFile = $this->logsDir . '/' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    // --- CLI Command Implementations (Used by migrate.php) ---

    /**
     * Creates a new migration file with a timestamp prefix and template content.
     * @param string $className The desired class name (e.g., CreateUsersTable).
     * @return string The filename of the created file.
     * @throws Exception if the file creation fails.
     */
    public function createMigrationFile($className) {
        $timestamp = date('Ymd_His');
        $fileName = $timestamp . '_' . $className . '.php';
        $filePath = $this->migrationsDir . '/' . $fileName;

        if (file_exists($filePath)) {
            throw new Exception("Migration file '$fileName' already exists.");
        }

        // The template for the new migration file
        $template = <<<EOT
<?php

class $className {
    private \$db;

    public function __construct(\$db) {
        \$this->db = \$db;
    }

    /**
     * Run the migrations.
     * This method applies the schema change.
     */
    public function up() {
        // SQL Example:
        // \$sql = "
        //     CREATE TABLE users (
        //         id INT AUTO_INCREMENT PRIMARY KEY,
        //         username VARCHAR(50) NOT NULL UNIQUE,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        //     )
        // ";
        // if (\$this->db->query(\$sql) === false) {
        //     throw new Exception(\$this->db->error);
        // }
    }

    /**
     * Reverse the migrations.
     * This method reverses the schema change made in up().
     */
    public function down() {
        // SQL Example:
        // \$sql = "DROP TABLE users";
        // if (\$this->db->query(\$sql) === false) {
        //     throw new Exception(\$this->db->error);
        // }
    }
}
EOT;

        if (file_put_contents($filePath, $template) === false) {
            throw new Exception("Could not write to file: $filePath");
        }

        $this->log("New migration file created: $fileName");
        return $fileName;
    }

    /**
     * Retrieves all migration files from the directory and compares them to the database record.
     * @return array
     */
    public function getStatus() {
        $fileMigrations = $this->getMigrationFiles();
        
        // Get applied migrations from DB
        $appliedMigrations = [];
        $result = $this->db->query("SELECT migration, batch, executed_at, status FROM migrations ORDER BY executed_at ASC");
        while ($row = $result->fetch_assoc()) {
            $appliedMigrations[$row['migration']] = $row;
        }

        $status = [];
        foreach ($fileMigrations as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            $isApplied = isset($appliedMigrations[$migrationName]) && $appliedMigrations[$migrationName]['status'] == 'applied';
            
            $status[] = [
                'name' => $migrationName,
                'file' => $file,
                'status' => $isApplied ? 'applied' : 'pending',
                'batch' => $isApplied ? $appliedMigrations[$migrationName]['batch'] : null,
                'executed_at' => $isApplied ? $appliedMigrations[$migrationName]['executed_at'] : null,
            ];
        }
        
        // Add any DB records that no longer have a file (for cleanup/tracking)
        foreach ($appliedMigrations as $name => $data) {
            if (!in_array($name . '.php', $fileMigrations) && $data['status'] == 'applied') {
                 $status[] = [
                    'name' => $name . " (MISSING FILE)",
                    'file' => 'N/A',
                    'status' => 'applied (MISSING)',
                    'batch' => $data['batch'],
                    'executed_at' => $data['executed_at'],
                ];
            }
        }

        return $status;
    }

    /**
     * Gets only the pending migration files.
     * @return array List of migration filenames.
     */
    public function getPendingMigrations() {
        $fileMigrations = $this->getMigrationFiles();
        
        $appliedMigrations = [];
        $result = $this->db->query("SELECT migration FROM migrations WHERE status = 'applied'");
        while ($row = $result->fetch_assoc()) {
            $appliedMigrations[$row['migration']] = true;
        }

        $pending = [];
        foreach ($fileMigrations as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            if (!isset($appliedMigrations[$migrationName])) {
                $pending[] = $file;
            }
        }
        return $pending;
    }

    /**
     * Executes all pending migrations.
     * @param string|null $target If set, migrates only up to this file/class name.
     * @return array Results
     */
    public function migrate($target = null) {
        $pendingMigrations = $this->getPendingMigrations();
        $batch = $this->getNextBatch();
        $results = [];

        if (empty($pendingMigrations)) {
            $this->log("No pending migrations to run.");
            return ['success' => true, 'message' => 'No pending migrations.', 'ran' => 0];
        }

        foreach ($pendingMigrations as $file) {
            $result = $this->applyMigration($file, $batch, 'up');
            $results[] = $result;

            if (!$result['success'] || (isset($target) && pathinfo($file, PATHINFO_FILENAME) === $target)) {
                break; // Stop on failure or if target is reached
            }
        }
        
        $ran = count(array_filter($results, fn($r) => $r['success']));
        $this->log("Migration run completed. Ran {$ran} migrations in batch {$batch}.");

        return ['success' => true, 'message' => "Successfully applied {$ran} migrations.", 'ran' => $ran, 'results' => $results];
    }
    
    /**
     * Executes a migration's 'up' or 'down' method.
     * @param string $file The migration filename.
     * @param int $batch The batch number.
     * @param string $direction 'up' or 'down'.
     * @return array Result of the operation.
     */
    private function applyMigration($file, $batch, $direction) {
        $filePath = $this->migrationsDir . '/' . $file;
        $migrationName = pathinfo($file, PATHINFO_FILENAME);
        $className = substr($migrationName, 15);
        $checksum = hash_file('sha256', $filePath);

        if (!$this->checkPhpSyntax($filePath)) {
            $errorMsg = "Syntax error in {$file}. Aborting.";
            $this->log($errorMsg, 'error');
            return ['success' => false, 'migration' => $migrationName, 'error' => $errorMsg];
        }

        // --- Dry Run Handling ---
        if ($this->isDryRun) {
             $this->log("Dry run: Would execute {$migrationName}->{$direction}() in batch {$batch}.");
             return ['success' => true, 'migration' => $migrationName, 'dry_run' => true];
        }
        // --- End Dry Run Handling ---

        try {
            require_once $filePath;
            $migration = new $className($this->db);
            
            // Execute the migration logic (up/down)
            $migration->$direction();
            
            // Update the migrations table
            if ($direction === 'up') {
                $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch, status, checksum) VALUES (?, ?, 'applied', ?) 
                                            ON DUPLICATE KEY UPDATE batch=?, status='applied', executed_at=CURRENT_TIMESTAMP, rolled_back_at=NULL, checksum=?");
                $stmt->bind_param("sisis", $migrationName, $batch, $checksum, $batch, $checksum);
            } else { // 'down'
                 $stmt = $this->db->prepare("UPDATE migrations SET status='rolled_back', rolled_back_at=CURRENT_TIMESTAMP, batch=NULL WHERE migration=?");
                $stmt->bind_param("s", $migrationName);
            }
            
            $stmt->execute();
            $stmt->close();

            $this->log("Successfully executed {$migrationName}->{$direction}() in batch {$batch}.");
            return ['success' => true, 'migration' => $migrationName, 'direction' => $direction];
        } catch (Exception $e) {
            $errorMsg = "Failed to execute {$migrationName}->{$direction}(): " . $e->getMessage();
            $this->log($errorMsg, 'error');
            
            // Record failure in DB if migrating up
            if ($direction === 'up') {
                 $stmt = $this->db->prepare("INSERT INTO migrations (migration, batch, status, checksum, error_message) VALUES (?, ?, 'failed', ?, ?) 
                                            ON DUPLICATE KEY UPDATE batch=?, status='failed', executed_at=CURRENT_TIMESTAMP, rolled_back_at=NULL, checksum=?, error_message=?");
                $stmt->bind_param("sississ", $migrationName, $batch, $checksum, $errorMsg, $batch, $checksum, $errorMsg);
                $stmt->execute();
                $stmt->close();
            }

            return ['success' => false, 'migration' => $migrationName, 'error' => $errorMsg, 'direction' => $direction];
        }
    }


    /**
     * Rolls back a number of batches.
     * @param int $steps Number of batches to rollback.
     * @return array Results
     */
    public function rollback($steps = 1) {
        $sql = "SELECT DISTINCT batch FROM migrations WHERE status = 'applied' ORDER BY batch DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $steps);
        $stmt->execute();
        $result = $stmt->get_result();
        $batches = [];
        while ($row = $result->fetch_assoc()) {
            $batches[] = $row['batch'];
        }
        $stmt->close();
        
        if (empty($batches)) {
            $this->log("No applied migrations to rollback.");
            return ['success' => true, 'message' => 'No migrations to rollback.', 'rolled_back' => 0];
        }

        $migrationsToRollback = [];
        $batchesString = implode(', ', $batches);
        
        $sql = "SELECT migration FROM migrations WHERE status = 'applied' AND batch IN ({$batchesString}) ORDER BY executed_at DESC";
        $result = $this->db->query($sql);
        
        while ($row = $result->fetch_assoc()) {
            $migrationsToRollback[] = $row['migration'] . '.php';
        }

        $results = [];
        foreach ($migrationsToRollback as $file) {
            $result = $this->applyMigration($file, 0, 'down'); // Batch is irrelevant for rollback
            $results[] = $result;
            if (!$result['success']) {
                $this->log("Rollback failed for {$file}. Aborting subsequent rollbacks.", 'error');
                break;
            }
        }
        
        $rolledBack = count(array_filter($results, fn($r) => $r['success']));
        $this->log("Rollback run completed. Rolled back {$rolledBack} migrations.");
        
        return ['success' => true, 'message' => "Successfully rolled back {$rolledBack} migrations.", 'rolled_back' => $rolledBack, 'results' => $results];
    }
    
    /**
     * Rolls back all migrations by executing down() for all applied migrations.
     * @return array Results
     */
    public function rollbackAll() {
        // Get all applied migrations, ordered by execution time descending (reverse order)
        $sql = "SELECT migration FROM migrations WHERE status = 'applied' ORDER BY executed_at DESC";
        $result = $this->db->query($sql);
        
        $migrationsToRollback = [];
        while ($row = $result->fetch_assoc()) {
            $migrationsToRollback[] = $row['migration'] . '.php';
        }
        
        if (empty($migrationsToRollback)) {
            $this->log("No applied migrations to rollback.");
            return ['success' => true, 'message' => 'No migrations to rollback.', 'rolled_back' => 0];
        }

        $results = [];
        foreach ($migrationsToRollback as $file) {
            $result = $this->applyMigration($file, 0, 'down');
            $results[] = $result;
            if (!$result['success']) {
                $this->log("Full rollback failed for {$file}. Aborting subsequent rollbacks.", 'error');
                break;
            }
        }
        
        $rolledBack = count(array_filter($results, fn($r) => $r['success']));
        $this->log("Full rollback completed. Rolled back {$rolledBack} migrations.");
        
        return ['success' => true, 'message' => "Successfully rolled back ALL {$rolledBack} migrations.", 'rolled_back' => $rolledBack, 'results' => $results];
    }
    
    // --- Backup & Restore ---
    
    public function backupDatabase() {
        $timestamp = date('Ymd_His');
        $fileName = "backup_{$timestamp}.sql";
        $filePath = $this->backupsDir . '/' . $fileName;
        
        // This is a placeholder for a real backup script. 
        // A robust solution would use shell commands like mysqldump or pg_dump.
        // E.g., exec("mysqldump -h{$host} -u{$user} -p{$pass} {$dbName} > " . escapeshellarg($filePath));
        
        // For a simple PDO/mysqli implementation, we'll create a dummy file
        // and log a warning that the dump command needs implementation.
        
        $warning = "-- WARNING: Actual database dump logic is a placeholder. 
-- Please implement a robust shell command like 'mysqldump' or 'pg_dump' 
-- in MigrationManager.php::backupDatabase() for production use.

-- DUMMY BACKUP FILE CREATED ON: {$timestamp}
-- TABLE: migrations
SELECT * FROM migrations;
";
        file_put_contents($filePath, $warning);

        $this->log("Backup file created (DUMMY): $fileName");
        return ['success' => true, 'file' => $fileName];
    }

    public function getBackups() {
        $files = array_filter(scandir($this->backupsDir), fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'sql');
        
        // Sort by timestamp in filename to get latest first
        rsort($files);
        
        return array_map(fn($f) => [
            'file' => $f,
            'path' => $this->backupsDir . '/' . $f,
            'size' => filesize($this->backupsDir . '/' . $f),
            'created' => filemtime($this->backupsDir . '/' . $f)
        ], $files);
    }
    
    public function restoreBackup($backupFile) {
        if (!file_exists($backupFile)) {
            throw new Exception("Backup file not found: " . $backupFile);
        }
        
        // In a real application, you would use a tool like 'mysql' or 'psql' to restore the SQL file.
        // For example: exec("mysql -h{$host} -u{$user} -p{$pass} {$dbName} < " . escapeshellarg($backupFile));

        // For this simple version, we'll log a warning and return success.
        $warning = "Database restoration attempted from: {$backupFile}. (NOTE: Actual database restore command is a placeholder in MigrationManager.php::restoreBackup())";
        $this->log($warning, 'warn');

        // Since we cannot execute arbitrary SQL files reliably with just mysqli/PDO without parsing,
        // we'll simulate the operation success.
        
        return ['success' => true, 'message' => $warning];
    }


    // --- Internal Helpers ---

    private function getMigrationFiles() {
        // Find all .php files that match the timestamp naming convention
        $files = array_filter(scandir($this->migrationsDir), function($f) {
            return preg_match('/^\d{14}_.*\.php$/', $f);
        });

        // Sort them chronologically by filename (timestamp)
        sort($files);
        return $files;
    }
}