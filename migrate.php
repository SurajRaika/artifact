<?php
/**
 * Database Migration CLI Tool
 * Production-ready migration management system
 * 
 * Usage: php migrate.php <command> [arguments]
 * Run: php migrate.php help
 */

// ============= SECURITY CHECK =============
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("❌ This tool is CLI-only and cannot be accessed via browser.\n");
}

// ============= BOOTSTRAP =============
require_once __DIR__ . "/app/config.php";
require_once __DIR__ . "/app/MigrationManager.php";

class MigrationCLI {
    private $manager;
    private $commands = [
        'help' => 'Show this help message',
        'status' => 'Show migration status',
        'make:migration <name>' => 'Create a new migration file',
        'migrate' => 'Run all pending migrations',
        'migrate <target>' => 'Run migrations up to specific file',
        'migrate:dry-run' => 'Preview pending migrations without executing',
        'rollback' => 'Rollback last batch of migrations',
        'rollback <steps>' => 'Rollback specific number of batches',
        'rollback:all' => 'Rollback ALL migrations (DANGER)',
        'backup [name]' => 'Create database backup',
        'backups' => 'List all backups',
        'restore' => 'Restore from backup',
        'describe <table_name>' => 'Show schema for a database table', // <-- ADD THIS LINE
        'logs [days]' => 'Show migration logs',
        'verify' => 'Verify migration integrity',
        'reset' => 'Full reset: rollback all and delete migration tracking (DANGER)',
    ];

    public function __construct($db) {
        $this->manager = new MigrationManager($db, __DIR__ . '/app/migrations');
    }

    public function run($args) {
        try {
            $command = $args[1] ?? 'help';

            $method = 'cmd_' . str_replace([':', '-'], '_', $command);
            
            if (!method_exists($this, $method)) {
                $this->error("Unknown command: {$command}\n");
                $this->cmd_help();
                exit(1);
            }

            $this->$method($args);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            exit(1);
        }
    }

    // ============= COMMANDS =============

    private function cmd_help() {
        $this->info("╔════════════════════════════════════════════════════════════╗\n");
        $this->info("║     Database Migration Management CLI Tool                 ║\n");
        $this->info("║     Production-ready migrations with full rollback support  ║\n");
        $this->info("╚════════════════════════════════════════════════════════════╝\n\n");

        $this->info("Usage: php migrate.php <command> [arguments]\n\n");
        $this->info("Available Commands:\n");
        $this->info(str_repeat("─", 70) . "\n");

        foreach ($this->commands as $cmd => $desc) {
            printf("  %-30s %s\n", $cmd, $desc);
        }

        $this->info("\n" . str_repeat("─", 70) . "\n");
        $this->info("Examples:\n");
        $this->info("  php migrate.php make:migration CreateUsersTable\n");
        $this->info("  php migrate.php migrate\n");
        $this->info("  php migrate.php migrate:dry-run\n");
        $this->info("  php migrate.php rollback\n");
        $this->info("  php migrate.php rollback 2        (rollback 2 batches)\n");
        $this->info("  php migrate.php backup my_backup\n");
        $this->info("  php migrate.php logs 14           (show 14 days of logs)\n\n");
    }

    private function cmd_status() {
        $status = $this->manager->getStatus();

        if (empty($status)) {
            $this->info("No migrations found.\n");
            return;
        }

        $this->info("Migration Status:\n");
        $this->info(str_repeat("═", 100) . "\n");
        printf("| %-35s | %-12s | %-6s | %-30s |\n", "Migration", "Status", "Batch", "Executed");
        $this->info(str_repeat("═", 100) . "\n");

        foreach ($status as $item) {
            $statusColor = match($item['status']) {
                'applied' => "\033[32m",           // Green
                'pending' => "\033[33m",           // Yellow
                'failed' => "\033[31m",            // Red
                default => "\033[36m"              // Cyan
            };
            
            $resetColor = "\033[0m";
            $displayName = substr($item['name'], 16);

            printf(
                "| %-35s | {$statusColor}%-12s{$resetColor} | %-6s | %-30s |\n",
                substr($displayName, 0, 35),
                $item['status'],
                $item['batch'] ?? '-',
                'N/A'
            );
        }

        $this->info(str_repeat("═", 100) . "\n\n");

        // Summary
        $applied = count(array_filter($status, fn($s) => $s['status'] === 'applied'));
        $pending = count(array_filter($status, fn($s) => $s['status'] === 'pending'));
        $failed = count(array_filter($status, fn($s) => strpos($s['status'], 'failed') !== false));

        $this->success("Summary: {$applied} applied, {$pending} pending" . ($failed ? ", {$failed} failed" : ""));
    }
private function cmd_describe($args) {
        $tableName = $args[2] ?? null;

        if (!$tableName) {
            $this->error("Missing table name.\n");
            $this->info("Usage: php migrate.php describe users\n");
            return;
        }

        $this->info("Describing table: {$tableName}\n");

        try {
            $description = $this->manager->describeTable($tableName);

            if (empty($description)) {
                $this->warn("⚠️  Table '{$tableName}' found no columns or does not exist.\n");
                return;
            }

            $this->info(str_repeat("═", 100) . "\n");
            printf("| %-25s | %-20s | %-8s | %-5s | %-10s | %-20s |\n", "Field", "Type", "Null", "Key", "Default", "Extra");
            $this->info(str_repeat("═", 100) . "\n");

            foreach ($description as $column) {
                printf(
                    "| %-25s | %-20s | %-8s | %-5s | %-10s | %-20s |\n",
                    substr($column['Field'], 0, 25),
                    substr($column['Type'], 0, 20),
                    $column['Null'],
                    $column['Key'],
                    $column['Default'] ?? 'NULL', // Use 'NULL' for display if PHP returns null
                    $column['Extra']
                );
            }

            $this->info(str_repeat("═", 100) . "\n");
            $this->success("✓ Table description complete.\n");

        } catch (Exception $e) {
            $this->error("Failed to describe table: " . $e->getMessage() . "\n");
        }
    }
    private function cmd_make_migration($args) {
        $name = $args[2] ?? null;

        if (!$name) {
            $this->error("Missing migration name\n");
            $this->info("Usage: php migrate.php make:migration CreateUsersTable\n");
            return;
        }

        $className = preg_replace('/[^a-zA-Z0-9]/', '', ucwords($name));
        
        if (empty($className)) {
            $this->error("Invalid migration name. Use only alphanumeric characters.\n");
            return;
        }

        try {
            $file = $this->manager->createMigrationFile($className);
            $this->success("✓ Migration created: app/migrations/{$file}\n");
            $this->info("Edit the file to add your up() and down() logic.\n");
        } catch (Exception $e) {
            $this->error("Failed to create migration: " . $e->getMessage() . "\n");
        }
    }

    private function cmd_migrate($args) {




        // --- ADDED PRE-MIGRATION BACKUP ---
        try {
            $this->info("Creating pre-migration database backup...\n");
            $backupResult = $this->manager->backupDatabase('pre_migrate');
            if ($backupResult['success']) {
                $this->success("✓ Backup created: " . $backupResult['file'] . " (" . $this->formatBytes($backupResult['size']) . ")\n");
            } else {
                $this->warn("⚠️  WARNING: Failed to create pre-migration backup. Continuing migration. Error: " . ($backupResult['error'] ?? 'Unknown') . "\n");
            }
        } catch (Exception $e) {
            $this->warn("⚠️  WARNING: Backup error: " . $e->getMessage() . ". Continuing migration.\n");
        }
        // ------------------------------------

        $target = $args[2] ?? null;
        
        if ($target) {
            $this->warn("Migrating up to: {$target}\n");
        } else {
            $this->info("Running pending migrations...\n");
        }

        try {
            $result = $this->manager->migrate($target);

            if ($result['ran'] === 0) {
                $this->info($result['message'] . "\n");
            } else {
                $this->success("✓ {$result['message']}\n");
                $this->info("Batch: " . $result['batch'] . " | Migrations run: " . $result['ran'] . "\n");

                foreach ($result['results'] as $r) {
                    if ($r['success']) {
                        $duration = $r['duration_ms'] ?? 0;
                        $this->success("  ✓ {$r['migration']} ({$duration}ms)\n");
                    } else {
                        $this->error("  ✗ {$r['migration']}: {$r['error']}\n");
                    }
                }
            }
        } catch (Exception $e) {
            $this->error("Migration failed: " . $e->getMessage() . "\n");
        }
    }

    private function cmd_migrate_dry_run($args) {
        $this->manager->setDryRun(true);
        $this->info("Running migrations in DRY-RUN mode...\n");

        $pending = $this->manager->getPendingMigrations();

        if (empty($pending)) {
            $this->info("No pending migrations.\n");
            $this->manager->setDryRun(false);
            return;
        }

        $this->warn("The following " . count($pending) . " migration(s) WOULD be executed:\n");
        foreach ($pending as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $this->info("  • " . substr($name, 16) . "\n");
        }

        try {
            $result = $this->manager->migrate();
            $this->success("\n✓ Dry-run complete. No changes made to database.\n");
        } catch (Exception $e) {
            $this->error("Dry-run failed: " . $e->getMessage() . "\n");
        }

        $this->manager->setDryRun(false);
    }

    private function cmd_rollback($args) {
        $steps = (int)($args[2] ?? 1);

        if ($steps < 1) {
            $this->error("Steps must be at least 1.\n");
            return;
        }

        $msg = $steps === 1 ? "Rollback last batch?" : "Rollback last {$steps} batches?";
        $this->confirm("⚠️  {$msg}");

        try {
            $result = $this->manager->rollback($steps);

            if ($result['rolled_back'] === 0) {
                $this->info($result['message'] . "\n");
            } else {
                $this->success("✓ {$result['message']}\n");
                $this->info("Rolled back: " . $result['rolled_back'] . " migrations\n");

                foreach ($result['results'] as $r) {
                    if ($r['success']) {
                        $duration = $r['duration_ms'] ?? 0;
                        $this->success("  ✓ {$r['migration']} ({$duration}ms)\n");
                    } else {
                        $this->error("  ✗ {$r['migration']}: {$r['error']}\n");
                    }
                }
            }
        } catch (Exception $e) {
            $this->error("Rollback failed: " . $e->getMessage() . "\n");
        }
    }

    private function cmd_rollback_all($args) {
        $this->confirm("\n🚨 DANGER: This will rollback ALL migrations. Continue?");

        try {
            $result = $this->manager->rollbackAll();

            if ($result['rolled_back'] === 0) {
                $this->info($result['message'] . "\n");
            } else {
                $this->success("✓ {$result['message']}\n");
                $this->info("Rolled back all: " . $result['rolled_back'] . " migrations\n");
            }
        } catch (Exception $e) {
            $this->error("Rollback failed: " . $e->getMessage() . "\n");
        }
    }

    private function cmd_backup($args) {
        $backupName = $args[2] ?? null;
        $this->info("Creating database backup...\n");

        try {
            $result = $this->manager->backupDatabase($backupName);

            if ($result['success']) {
                $this->success("✓ Backup created: " . $result['file'] . "\n");
                $this->info("Size: " . $this->formatBytes($result['size']) . "\n");
            } else {
                $this->error("Backup failed: " . ($result['error'] ?? 'Unknown error') . "\n");
            }
        } catch (Exception $e) {
            $this->error("Backup error: " . $e->getMessage() . "\n");
        }
    }

    private function cmd_backups() {
        $backups = $this->manager->getBackups();

        if (empty($backups)) {
            $this->info("No backups found.\n");
            return;
        }

        $this->info("Available Backups (newest first):\n");
        $this->info(str_repeat("═", 100) . "\n");
        printf("| %-4s | %-40s | %-15s | %-20s |\n", "ID", "Filename", "Size", "Created");
        $this->info(str_repeat("═", 100) . "\n");

        foreach ($backups as $index => $backup) {
            printf(
                "| %-4d | %-40s | %-15s | %-20s |\n",
                $index + 1,
                substr($backup['file'], 0, 40),
                $this->formatBytes($backup['size']),
                date('Y-m-d H:i:s', $backup['created'])
            );
        }

        $this->info(str_repeat("═", 100) . "\n");
    }

    private function cmd_restore($args) {
        $backups = $this->manager->getBackups();

        if (empty($backups)) {
            $this->error("No backups available.\n");
            return;
        }

        $this->cmd_backups();
        $this->info("\nEnter backup ID to restore: ");
        $choice = trim(fgets(STDIN));
        $index = (int)$choice - 1;

        if (!isset($backups[$index])) {
            $this->error("Invalid ID.\n");
            return;
        }

        $this->confirm("\n🚨 DANGER: This will OVERWRITE your database with " . $backups[$index]['file'] . ". Continue?");

        try {
            $result = $this->manager->restoreBackup($backups[$index]['path']);

            if ($result['success']) {
                $this->success("✓ Database restored successfully.\n");
            } else {
                $this->error("Restore failed: " . $result['error'] . "\n");
            }
        } catch (Exception $e) {
            $this->error("Restore error: " . $e->getMessage() . "\n");
        }
    }

    private function cmd_logs($args) {
        $days = (int)($args[2] ?? 7);
        $logs = $this->manager->getLogs($days);

        if (empty($logs)) {
            $this->info("No logs found.\n");
            return;
        }

        $this->info("Migration Logs (last {$days} days):\n\n");

        foreach ($logs as $date => $content) {
            $this->info("📅 {$date}\n");
            $this->info(str_repeat("─", 80) . "\n");
            echo $content . "\n";
        }
    }

    private function cmd_verify($args) {
        $this->info("Verifying migration integrity...\n\n");

        $status = $this->manager->getStatus();
        $issues = [];

        foreach ($status as $item) {
            if (!$item['file_exists'] && $item['status'] === 'applied') {
                $issues[] = "Missing file for applied migration: {$item['name']}";
            }

            if (strpos($item['status'], 'MISSING') !== false) {
                $issues[] = "Migration file exists but not tracked: {$item['name']}";
            }

            if (strpos($item['status'], 'failed') !== false) {
                $issues[] = "Failed migration: {$item['name']}";
            }
        }

        if (empty($issues)) {
            $this->success("✓ All migrations verified successfully.\n");
        } else {
            $this->error("Issues found:\n");
            foreach ($issues as $issue) {
                $this->error("  • {$issue}\n");
            }
        }
    }

    private function cmd_reset($args) {
        $this->confirm("\n🚨 EXTREME DANGER: This will rollback ALL migrations and delete tracking. Continue?");
        $this->confirm("🚨 FINAL WARNING: This cannot be undone. Type 'yes' to confirm");

        try {
            $result = $this->manager->rollbackAll();
            $this->success("✓ All migrations rolled back.\n");
        } catch (Exception $e) {
            $this->error("Reset failed: " . $e->getMessage() . "\n");
        }
    }

    // ============= OUTPUT HELPERS =============

    private function info($msg) {
        echo "\033[36m{$msg}\033[0m";
    }

    private function success($msg) {
        echo "\033[32m{$msg}\033[0m";
    }

    private function error($msg) {
        echo "\033[31m✗ {$msg}\033[0m";
    }

    private function warn($msg) {
        echo "\033[33m{$msg}\033[0m";
    }

    private function confirm($msg) {
        echo "\033[41m\033[37m{$msg}\033[0m\033[33m (type 'yes' to proceed): \033[0m";
        $input = trim(fgets(STDIN));
        
        if (strtolower($input) !== 'yes') {
            $this->error("Cancelled.\n");
            exit(0);
        }
    }

    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// ============= RUN =============
if (!isset($link)) {
    die("❌ Error: Database connection \$link not found in app/config.php\n");
}

$cli = new MigrationCLI($link);
$cli->run($argv);