#!/usr/bin/env php
<?php
// migrate.php - Run from project root: php migrate.php <command> [options]
// SECURITY: Only allow CLI execution

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/404.php';
    exit;
}

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/MigrationManager.php';

class MigrateCLI {
    private $manager;
    private $pdo;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4";
            $this->pdo = new PDO(
                $dsn,
                getenv('DB_USER'),
                getenv('DB_PASS'),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );

            $migrationsPath = __DIR__ . '/app/migrations';
            $this->manager = new MigrationManager($this->pdo, $migrationsPath);
        } catch (Exception $e) {
            $this->error("Connection failed: " . $e->getMessage());
            $this->error("\nTroubleshooting:");
            $this->error("1. Check .env file exists in project root");
            $this->error("2. Verify MySQL is running: sudo service mysql status");
            $this->error("3. Test connection: mysql -u " . getenv('DB_USER') . " -p -e \"SHOW DATABASES;\"");
            exit(1);
        }
    }

    public function run($args) {
        $command = $args[1] ?? 'help';
        $option = $args[2] ?? null;

        switch ($command) {
            case 'make':
                $this->make($option);
                break;

            case 'up':
            case 'run':
                $this->up();
                break;

            case 'down':
            case 'rollback':
                $this->down($option);
                break;

            case 'status':
                $this->status();
                break;

            case 'backups':
                $this->listBackups();
                break;

            case 'restore':
                $this->restore($option);
                break;

            case 'refresh':
                $this->refresh();
                break;

            case 'reset':
                $this->reset();
                break;

            case 'help':
            default:
                $this->showHelp();
        }
    }

    private function make($name) {
        if (!$name) {
            $this->error("Migration name required: php migrate.php make <name>");
            exit(1);
        }

        $name = preg_replace('/[^a-z0-9_]/i', '_', $name);
        $this->manager->createMigration($name);
    }

    private function up() {
        $this->info("\n🔄 Running pending migrations...\n");
        
        if ($this->manager->runMigrations()) {
            $this->success("\n✓ All migrations completed successfully!\n");
        } else {
            $this->error("\n✗ Migration failed. Database rolled back.\n");
            exit(1);
        }
    }

    private function down($batch = null) {
        $this->warning("\n⚠️  Are you sure you want to rollback? (yes/no): ");
        
        $input = trim(fgets(STDIN));
        if ($input !== 'yes') {
            $this->info("Rollback cancelled.\n");
            return;
        }

        $this->info("\n🔄 Rolling back migrations...\n");
        
        if ($this->manager->rollbackBatch($batch)) {
            $this->success("\n✓ Rollback completed successfully!\n");
        } else {
            $this->error("\n✗ Rollback failed.\n");
            exit(1);
        }
    }

    private function status() {
        $migrations = $this->manager->getStatus();

        $this->info("\n📊 Migration Status:\n");

        if (empty($migrations)) {
            $this->warning("No migrations found.\n");
            return;
        }

        printf("%-20s %-12s %-8s %-8s %-20s %-10s\n", 
            "Migration", "Status", "Batch", "Time(s)", "Executed At", "Error");
        printf("%-20s %-12s %-8s %-8s %-20s %-10s\n", 
            str_repeat("-", 20), str_repeat("-", 12), 
            str_repeat("-", 8), str_repeat("-", 8), 
            str_repeat("-", 20), str_repeat("-", 10));

        foreach ($migrations as $migration) {
            printf(
                "%-20s %-12s %-8s %-8.4f %-20s %s\n",
                substr($migration['migration_name'], 0, 20),
                $migration['status'],
                $migration['batch'],
                $migration['execution_time'],
                $migration['executed_at'],
                $migration['error_message'] ? '❌ ' . substr($migration['error_message'], 0, 20) : ''
            );
        }
        
        $this->info("\n");
    }

    private function listBackups() {
        $backups = $this->manager->listBackups();

        $this->info("\n💾 Available Backups:\n");

        if (empty($backups)) {
            $this->warning("No backups available.\n");
            return;
        }

        printf("%-50s %-15s %-20s\n", 
            "Filename", "Size", "Created");
        printf("%-50s %-15s %-20s\n", 
            str_repeat("-", 50), str_repeat("-", 15), str_repeat("-", 20));

        foreach ($backups as $backup) {
            printf(
                "%-50s %-15s %-20s\n",
                substr($backup['filename'], 0, 50),
                $this->formatBytes($backup['size']),
                $backup['created_date']
            );
        }
        
        $this->info("\n");
    }

    private function restore($backupFile) {
        if (!$backupFile) {
            $this->error("Backup filename required: php migrate.php restore <filename>");
            exit(1);
        }

        $this->warning("\n⚠️  This will overwrite your database. Are you sure? (yes/no): ");
        
        $input = trim(fgets(STDIN));
        if ($input !== 'yes') {
            $this->info("Restore cancelled.\n");
            return;
        }

        $this->info("\n🔄 Restoring from backup...\n");
        
        if ($this->manager->restoreBackup($backupFile)) {
            $this->success("\n✓ Database restored successfully!\n");
        } else {
            $this->error("\n✗ Restore failed.\n");
            exit(1);
        }
    }

    private function refresh() {
        $this->warning("\n⚠️  This will rollback all migrations and run them again. Continue? (yes/no): ");
        
        $input = trim(fgets(STDIN));
        if ($input !== 'yes') {
            $this->info("Refresh cancelled.\n");
            return;
        }

        $this->info("\n🔄 Refreshing migrations...\n");
        
        $this->manager->rollbackBatch();
        $this->up();
    }

    private function reset() {
        $this->error("\n🚨 DESTRUCTIVE: This will rollback ALL migrations. Continue? (type 'reset' to confirm): ");
        
        $input = trim(fgets(STDIN));
        if ($input !== 'reset') {
            $this->info("Reset cancelled.\n");
            return;
        }

        $this->info("\n🔄 Resetting all migrations...\n");
        
        while (true) {
            $lastBatch = $this->manager->getStatus()[0]['batch'] ?? 0;
            if ($lastBatch === 0) break;
            $this->manager->rollbackBatch($lastBatch);
        }

        $this->success("\n✓ All migrations reset!\n");
    }

    private function showHelp() {
        echo <<<HELP

╔════════════════════════════════════════════════════════════════╗
║              DATABASE MIGRATION SYSTEM - CLI HELP              ║
╚════════════════════════════════════════════════════════════════╝

BASIC COMMANDS:

  php migrate.php make <name>
    Create a new migration file
    Example: php migrate.php make create_users_table

  php migrate.php up
    Run all pending migrations
    This will create a backup before running

  php migrate.php down
    Rollback the last migration batch
    You'll be prompted to confirm

  php migrate.php status
    Show migration history and status

BACKUP & RESTORE:

  php migrate.php backups
    List all available backups

  php migrate.php restore <filename>
    Restore database from backup
    Example: php migrate.php restore before_batch_1_2024_12_01_143022.sql

ADVANCED:

  php migrate.php refresh
    Rollback all and run again (useful for testing migrations)

  php migrate.php reset
    Rollback ALL migrations (destructive!)

WORKFLOW EXAMPLES:

1. Creating a new table:
   $ php migrate.php make create_products_table
   (Edit app/migrations/20240101120000_create_products_table.php)
   (Add your SQL in up() method)
   $ php migrate.php up

2. If something goes wrong:
   $ php migrate.php down
   (Or restore from backup: php migrate.php restore <filename>)

3. Check what you've done:
   $ php migrate.php status

HELP;
    }

    private function info($message) {
        echo "\033[36m{$message}\033[0m";
    }

    private function success($message) {
        echo "\033[32m{$message}\033[0m";
    }

    private function warning($message) {
        echo "\033[33m{$message}\033[0m";
    }

    private function error($message) {
        echo "\033[31m{$message}\033[0m";
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

$cli = new MigrateCLI();
$cli->run($argv);
?>