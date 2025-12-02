<?php
// app/migrations/MigrationManager.php
require __DIR__ . '/../vendor/autoload.php';


class MigrationManager
{
    private $pdo;
    private $migrationsPath;
    private $backupsPath;
    private $logsPath;

    public function __construct(PDO $pdo, $migrationsPath = __DIR__)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = $migrationsPath;
        $this->backupsPath = dirname($migrationsPath) . '/backups';
        $this->logsPath = dirname($migrationsPath) . '/logs';

        $this->ensureDirectoriesExist();
        $this->initializeMigrationTable();
    }

    private function ensureDirectoriesExist()
    {
        foreach ([$this->backupsPath, $this->logsPath] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    private function initializeMigrationTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('pending', 'completed', 'failed', 'rolled_back') DEFAULT 'pending',
                error_message TEXT,
                execution_time FLOAT DEFAULT 0
            )
        ";
        $this->pdo->exec($sql);
    }

    public function createMigration($name)
    {
        $timestamp = date('Y_m_d_His');
        $className = 'Migration_' . $timestamp . '_' . ucfirst($name);
        $filename = $timestamp . '_' . $name . '.php';
        $filepath = $this->migrationsPath . '/' . $filename;

        $template = <<<PHP
<?php
// Migration: {$timestamp}_{$name}
// Description: {$name}

class {$className} {
    protected \$pdo;

    public function __construct(\$pdo) {
        \$this->pdo = \$pdo;
    }

    /**
     * Run the migration
     * Executed during: php migrate up
     */
    public function up() {
        // Write your migration code here
        // Example:
        // \$sql = "ALTER TABLE users ADD COLUMN new_column VARCHAR(255)";
        // \$this->pdo->exec(\$sql);
    }

    /**
     * Rollback the migration
     * Executed during: php migrate down
     */
    public function down() {
        // Write your rollback code here
        // IMPORTANT: This should undo exactly what up() does
        // Example:
        // \$sql = "ALTER TABLE users DROP COLUMN new_column";
        // \$this->pdo->exec(\$sql);
    }

    /**
     * Optional: Get description of what this migration does
     */
    public function getDescription() {
        return "{$name}";
    }
}
?>
PHP;

        if (file_put_contents($filepath, $template)) {
            $this->log("✓ Migration created: $filename", 'success');
            return $filepath;
        } else {
            $this->log("✗ Failed to create migration: $filename", 'error');
            return false;
        }
    }

    public function runMigrations($batch = null)
    {
        $migrations = $this->getPendingMigrations();

        if (empty($migrations)) {
            $this->log("✓ No pending migrations to run", 'info');
            return true;
        }

        if ($batch === null) {
            $batch = $this->getNextBatch();
        }

        $this->createBackup("before_batch_{$batch}");
        $this->log("Starting migration batch: $batch", 'info');

        $success = true;
        foreach ($migrations as $migration) {
            if (!$this->runMigration($migration, $batch)) {
                $success = false;
                $this->log("✗ Migration failed: {$migration['name']}", 'error');
                break;
            }
        }

        if ($success) {
            $this->log("✓ Batch $batch completed successfully", 'success');
        } else {
            $this->log("✗ Batch $batch failed. Rolling back...", 'error');
            $this->rollbackBatch($batch);
        }

        return $success;
    }

    private function runMigration($migration, $batch)
    {
        try {
            require_once $this->migrationsPath . '/' . $migration['file'];
            $className = 'Migration_' . $migration['migration_name'];

            if (!class_exists($className)) {
                throw new Exception("Migration class not found: $className");
            }

            $startTime = microtime(true);
            $migrationObj = new $className($this->pdo);

            $this->pdo->beginTransaction();
            $migrationObj->up();
            $this->pdo->commit();

            $executionTime = microtime(true) - $startTime;

            $this->updateMigrationStatus(
                $migration['migration_name'],
                'completed',
                $batch,
                $executionTime
            );

            $this->log(
                "✓ Executed: {$migration['migration_name']} ({$executionTime}s)",
                'success'
            );

            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->updateMigrationStatus(
                $migration['migration_name'],
                'failed',
                $batch,
                0,
                $e->getMessage()
            );
            $this->log("✗ Error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    public function rollbackBatch($batch = null)
    {
        if ($batch === null) {
            $batch = $this->getLastBatch();
        }

        $migrations = $this->getExecutedMigrationsByBatch($batch);

        if (empty($migrations)) {
            $this->log("✓ No migrations to rollback in batch $batch", 'info');
            return true;
        }

        $this->createBackup("before_rollback_batch_{$batch}");
        $this->log("Rolling back batch: $batch", 'info');

        // Rollback in reverse order
        $migrations = array_reverse($migrations);

        $success = true;
        foreach ($migrations as $migration) {
            if (!$this->rollbackMigration($migration)) {
                $success = false;
                break;
            }
        }

        if ($success) {
            $this->log("✓ Batch $batch rolled back successfully", 'success');
        } else {
            $this->log("✗ Rollback failed for batch $batch", 'error');
        }

        return $success;
    }

    private function rollbackMigration($migration)
    {
        try {
            require_once $this->migrationsPath . '/' . $migration['file'];
            $className = 'Migration_' . $migration['migration_name'];

            $startTime = microtime(true);
            $migrationObj = new $className($this->pdo);

            $this->pdo->beginTransaction();
            $migrationObj->down();
            $this->pdo->commit();

            $executionTime = microtime(true) - $startTime;

            $this->updateMigrationStatus(
                $migration['migration_name'],
                'rolled_back',
                $migration['batch'],
                $executionTime
            );

            $this->log(
                "✓ Rolled back: {$migration['migration_name']} ({$executionTime}s)",
                'success'
            );

            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $this->log("✗ Rollback error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    private function createBackup($backupName)
    {
        try {
            $dbname = $this->pdo->query("SELECT DATABASE()")->fetchColumn();
            $timestamp = date('Y_m_d_His');
            $filename = "{$backupName}_{$timestamp}.sql";
            $filepath = $this->backupsPath . '/' . $filename;

            $command = sprintf(
                "mysqldump -u%s -p%s %s > %s 2>/dev/null",
                escapeshellarg($_ENV['DB_USER'] ?? 'root'),
                escapeshellarg($_ENV['DB_PASS'] ?? ''),
                escapeshellarg($dbname),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($filepath)) {
                $this->log("✓ Backup created: $filename", 'success');
                return true;
            } else {
                $this->log("✗ Backup creation failed", 'error');
                return false;
            }
        } catch (Exception $e) {
            $this->log("✗ Backup error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    public function restoreBackup($backupFile)
    {
        try {
            $filepath = $this->backupsPath . '/' . $backupFile;

            if (!file_exists($filepath)) {
                throw new Exception("Backup file not found: $backupFile");
            }

            $dbname = $this->pdo->query("SELECT DATABASE()")->fetchColumn();

            $command = sprintf(
                "mysql -u%s -p%s %s < %s 2>/dev/null",
                escapeshellarg($_ENV['DB_USER'] ?? 'root'),
                escapeshellarg($_ENV['DB_PASS'] ?? ''),
                escapeshellarg($dbname),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                $this->log("✓ Database restored from: $backupFile", 'success');
                return true;
            } else {
                throw new Exception("Restore command failed");
            }
        } catch (Exception $e) {
            $this->log("✗ Restore error: " . $e->getMessage(), 'error');
            return false;
        }
    }

    public function getStatus()
    {
        $stmt = $this->pdo->query("
            SELECT * FROM migrations 
            ORDER BY id DESC 
            LIMIT 20
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPendingMigrations()
    {
        $executed = $this->pdo->query(
            "SELECT migration_name FROM migrations"
        )->fetchAll(PDO::FETCH_COLUMN);

        $files = glob($this->migrationsPath . '/*.php');
        $pending = [];

        foreach ($files as $file) {
            preg_match('/(\d{14}_\w+)\.php/', basename($file), $matches);
            if (isset($matches[1]) && !in_array($matches[1], $executed)) {
                $pending[] = [
                    'migration_name' => $matches[1],
                    'file' => basename($file),
                    'path' => $file
                ];
            }
        }

        usort($pending, function ($a, $b) {
            return strcmp($a['migration_name'], $b['migration_name']);
        });

        return $pending;
    }

    private function getExecutedMigrationsByBatch($batch)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM migrations 
            WHERE batch = ? AND status = 'completed'
            ORDER BY id DESC
        ");
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getNextBatch()
    {
        $result = $this->pdo->query(
            "SELECT MAX(batch) as max_batch FROM migrations"
        )->fetch(PDO::FETCH_ASSOC);

        return ($result['max_batch'] ?? 0) + 1;
    }

    private function getLastBatch()
    {
        $result = $this->pdo->query(
            "SELECT MAX(batch) as max_batch FROM migrations 
             WHERE status IN ('completed', 'rolled_back')"
        )->fetch(PDO::FETCH_ASSOC);

        return $result['max_batch'] ?? 0;
    }

    private function updateMigrationStatus(
        $migrationName,
        $status,
        $batch,
        $executionTime = 0,
        $errorMessage = null
    ) {
        $stmt = $this->pdo->prepare("
            INSERT INTO migrations 
            (migration_name, batch, status, error_message, execution_time)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            status = VALUES(status), 
            error_message = VALUES(error_message),
            execution_time = VALUES(execution_time)
        ");

        $stmt->execute([
            $migrationName,
            $batch,
            $status,
            $errorMessage,
            $executionTime
        ]);
    }

    private function log($message, $type = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logFile = $this->logsPath . '/migrations.log';
        $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;

        file_put_contents($logFile, $logMessage, FILE_APPEND);

        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }

    public function listBackups()
    {
        $files = glob($this->backupsPath . '/*.sql');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'created' => filemtime($file),
                'created_date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }

        usort($backups, function ($a, $b) {
            return $b['created'] - $a['created'];
        });

        return $backups;
    }
}
