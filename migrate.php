<?php
// migrate.php - CLI ONLY TOOL
// This file should NOT be accessible via web browser

// ============= SECURITY CHECK =============
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("❌ This tool is CLI-only and cannot be accessed via browser.\n");
}

// Ensure you have an 'app/config.php' that creates a mysqli connection named $link
require_once "app/config.php";
require_once "app/MigrationManager.php";

class MigrationCLI {
    private $manager;
    private $commands = [
        'status' => 'Show migration status',
        'make:migration <name>' => 'Create a new, empty migration file',
        'migrate' => 'Run all pending migrations',
        'migrate:dry-run' => 'Preview migrations without executing',
        'migrate <filename>' => 'Run migrations up to a specific file (optional)',
        'rollback' => 'Rollback last batch',
        'rollback:all' => 'Rollback all applied migrations',
        'backup' => 'Create a manual database backup',
        'restore' => 'Restore from a backup file',
        'logs' => 'Show recent migration logs',
        'backups' => 'List all available backups',
        'help' => 'Show this help message'
    ];

    public function __construct($db) {
        // Assume MigrationManager.php is in 'app' and migrations folder is at the root
        $this->manager = new MigrationManager($db, __DIR__ . '/migrations');
    }

    public function run($args) {
        $command = $args[1] ?? 'help';

        // Custom handling for commands that accept arguments
        if (strpos($command, 'make:migration') === 0) {
            $command = 'make:migration';
        } elseif (isset($args[2]) && $command === 'migrate') {
            // Treat 'php migrate.php migrate <filename>' as a target migration
            $this->cmd_migrate($args);
            return;
        }
        
        $method = 'cmd_' . str_replace(':', '_', $command);
        
        if (!method_exists($this, $method)) {
            $this->error("Unknown command: $command");
            $this->cmd_help();
            return;
        }

        $this->$method($args);
    }

    // --- CLI Commands ---

    private function cmd_help() {
        $this->info("Database Migration CLI Tool\n");
        $this->info("Usage: php migrate.php <command> [arguments]\n");
        $this->info("Available commands:\n");
        foreach ($this->commands as $cmd => $desc) {
            // Format command output nicely
            $display_cmd = str_replace([' <name>', ' <filename>'], '', $cmd);
            echo "  php migrate.php $cmd" . str_repeat(" ", 25 - strlen($display_cmd)) . "- $desc\n";
        }
    }

    private function cmd_make_migration($args) {
        $migrationName = $args[2] ?? null;

        if (!$migrationName) {
            $this->error("Missing migration name. Usage: php migrate.php make:migration MyMigrationName");
            return;
        }

        // Clean up and format the name: remove path, make it PascalCase
        $className = preg_replace('/[^a-zA-Z0-9]/', '', ucwords($migrationName));
        
        try {
            $file = $this->manager->createMigrationFile($className);
            $this->success("Migration file created: app/migrations/$file");
            $this->info("Remember to fill in your 'up()' and 'down()' logic.\n");
        } catch (Exception $e) {
            $this->error("Failed to create migration file: " . $e->getMessage());
        }
    }

    private function cmd_status() {
        $status = $this->manager->getStatus();
        
        if (empty($status)) {
            $this->info("No migrations recorded in the database or found on disk.\n");
            return;
        }

        $this->info("Migration Status:\n");
        echo str_repeat("=", 80) . "\n";
        printf("| %-20s | %-12s | %-6s | %-30s |\n", "Migration", "Status", "Batch", "Executed At");
        echo str_repeat("=", 80) . "\n";

        foreach ($status as $item) {
            $statusColor = $item['status'] === 'applied' ? "\033[32m" : ($item['status'] === 'pending' ? "\033[33m" : "\033[31m");
            $resetColor = "\033[0m";

            printf(
                "| %-20s | {$statusColor}%-12s{$resetColor} | %-6s | %-30s |\n", 
                substr($item['name'], 15), // Show class name part
                $item['status'], 
                $item['batch'] ?? '-', 
                $item['executed_at'] ?? 'N/A'
            );
        }
        echo str_repeat("=", 80) . "\n";
    }

    private function cmd_migrate($args) {
        $target = $args[2] ?? null;
        
        if ($target) {
            $this->warn("Migrating up to target: " . $target . "\n");
        } else {
             $this->info("Running all pending migrations...\n");
        }
        
        $result = $this->manager->migrate($target);
        
        if ($result['ran'] > 0) {
            $this->success($result['message']);
        } else {
            $this->info($result['message']);
        }
    }
    
    private function cmd_migrate_dry_run($args) {
        $this->manager->setDryRun(true);
        $this->info("Running migrations in DRY-RUN mode...\n");
        
        $pending = $this->manager->getPendingMigrations();
        
        if (empty($pending)) {
            $this->info("No pending migrations to run. (Dry-run)\n");
            return;
        }
        
        $this->warn("The following " . count($pending) . " migrations WOULD be executed:\n");
        $this->info(implode("\n", $pending) . "\n");

        // Run the dry-run, which will only log the intentions
        $result = $this->manager->migrate();
        
        $this->success("Dry-run complete. No changes were made to the database.");
        $this->manager->setDryRun(false); // Reset
    }

    private function cmd_rollback() {
        $this->confirm("Are you sure you want to rollback the LAST batch of migrations?");
        $this->info("Rolling back last batch...\n");

        $result = $this->manager->rollback(1);
        
        if ($result['rolled_back'] > 0) {
            $this->success($result['message']);
        } else {
            $this->info($result['message']);
        }
    }

    private function cmd_rollback_all() {
        $this->confirm("DANGER: Are you sure you want to rollback ALL applied migrations?");
        $this->info("Rolling back ALL migrations...\n");

        $result = $this->manager->rollbackAll();
        
        if ($result['rolled_back'] > 0) {
            $this->success($result['message']);
        } else {
            $this->info($result['message']);
        }
    }

    private function cmd_backup() {
        $this->info("Creating database backup...\n");
        $result = $this->manager->backupDatabase();
        
        if ($result['success']) {
            $this->success("Backup successful. File: " . $result['file']);
            $this->warn("NOTE: Ensure you have implemented the actual 'mysqldump' or 'pg_dump' command in MigrationManager.php::backupDatabase() for production use.");
        } else {
            $this->error("Backup failed: " . ($result['error'] ?? 'Unknown error'));
        }
    }
    
    private function cmd_backups() {
        $backups = $this->manager->getBackups();
        
        if (empty($backups)) {
            $this->info("No backup files found.\n");
            return;
        }

        $this->info("Available Backups (Newest first):\n");
        echo str_repeat("=", 80) . "\n";
        printf("| %-4s | %-30s | %-12s | %-20s |\n", "ID", "Filename", "Size (KB)", "Created At");
        echo str_repeat("=", 80) . "\n";
        
        foreach ($backups as $index => $b) {
            printf(
                "| %-4d | %-30s | %-12.2f | %-20s |\n", 
                $index + 1, 
                $b['file'], 
                $b['size'] / 1024,
                date('Y-m-d H:i:s', $b['created'])
            );
        }
        echo str_repeat("=", 80) . "\n";
    }

    private function cmd_restore() {
        $backups = $this->manager->getBackups();
        
        if (empty($backups)) {
            $this->error("No backup files available to restore.");
            return;
        }

        $this->cmd_backups();
        
        echo "\n\033[33mEnter the ID of the backup to restore: \033[0m";
        $choice = trim(fgets(STDIN));
        $index = (int)$choice - 1;

        if (!isset($backups[$index])) {
            $this->error("Invalid backup ID.");
            return;
        }

        $this->confirm("DANGER: This will OVERWRITE your current database with data from " . $backups[$index]['file'] . ".");

        $result = $this->manager->restoreBackup($backups[$index]['path']);

        if ($result['success']) {
            $this->success($result['message']);
            $this->warn("NOTE: Ensure you have implemented the actual database restore command in MigrationManager.php::restoreBackup() for production use.");
        } else {
            $this->error("Restore failed: " . $result['error']);
        }
    }

    private function cmd_logs() {
        $logsDir = $this->manager->logsDir; // Accessing the log directory location
        $files = array_filter(scandir($logsDir), fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'log');

        if (empty($files)) {
            $this->info("No logs available.\n");
            return;
        }

        rsort($files);
        $this->info("Recent logs (Last 5 files):\n");
        
        foreach (array_slice($files, 0, 5) as $file) {
            echo "\n📄 \033[36m" . $file . "\033[0m\n";
            echo str_repeat("-", 80) . "\n";
            // Show only the last 20 lines of the file
            $content = file_get_contents($logsDir . '/' . $file);
            $lines = explode("\n", $content);
            echo implode("\n", array_slice($lines, -20));
            echo "\n";
        }
    }

    // --- Output Helpers ---

    private function info($msg) {
        echo "\033[36m" . $msg . "\033[0m"; // Cyan
    }

    private function success($msg) {
        echo "\033[32m✓ " . $msg . "\033[0m\n"; // Green
    }

    private function error($msg) {
        echo "\033[31m✗ " . $msg . "\033[0m\n"; // Red
    }

    private function warn($msg) {
        echo "\033[33m" . $msg . "\033[0m\n"; // Yellow
    }

    private function confirm($msg) {
        echo "\n\033[41m\033[37m" . $msg . "\033[0m\033[33m (Type 'yes' to proceed): \033[0m";
        $input = trim(fgets(STDIN));
        if (strtolower($input) !== 'yes') {
            $this->error("Operation cancelled by user.");
            exit(1);
        }
    }
}

// ============= RUN CLI =============
// NOTE: $link is expected to be defined in app/config.php (your database connection)
$cli = new MigrationCLI($link);
$cli->run($argv);