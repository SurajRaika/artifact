<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ================= CONFIGURATION =================
// CHANGE THIS PASSWORD!
$WEB_PASSWORD = 'admin123'; 
// =================================================

// 1. Authentication Check
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: migrate.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_password'])) {
    if ($_POST['login_password'] === $WEB_PASSWORD) {
        $_SESSION['auth_migration'] = true;
        header("Location: migrate.php");
        exit;
    } else {
        $login_error = "Invalid Password";
    }
}




if (!isset($_SESSION['auth_migration'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Migration Login</title>
        <style>
            body { font-family: monospace; display: flex; justify-content: center; align-items: center; height: 100vh; background: #222; color: #eee; }
            form { background: #333; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
            input { padding: 8px; background: #444; border: 1px solid #555; color: white; width: 200px; }
            button { padding: 8px 16px; background: #007bff; color: white; border: none; cursor: pointer; }
            button:hover { background: #0056b3; }
            .error { color: #ff6b6b; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <form method="POST">
            <h3 style="margin-top:0">Migration Tool</h3>
            <?php if(isset($login_error)) echo "<div class='error'>$login_error</div>"; ?>
            <input type="password" name="login_password" placeholder="Enter Password" autofocus required>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// 2. Bootstrap & Action Handling
$output_log = "";
$message_type = "info"; // info, success, error
$processed_action = false; // Flag to track if an action was performed

// Handle Flash Messages (POST-Redirect-GET)
if (isset($_SESSION['action_result'])) {
    $output_log = $_SESSION['action_result']['message'];
    $message_type = $_SESSION['action_result']['type'];
    unset($_SESSION['action_result']);
}

try {
    // Load Env and DB Connection - CHECKING 'app/' DIRECTORY AS REQUESTED
    if (file_exists('app/env.php')) {
        $db_env = require 'app/env.php';
    } elseif (file_exists('app/config.php')) {
        require 'app/config.php'; // Should provide $link
    }

    // Attempt to establish connection
    if (!isset($link)) {
        if (isset($db_env)) {
             // Assuming DB_HOST, DB_USER, DB_PASS, DB_NAME are available via $db_env
             $link = new mysqli($db_env['DB_HOST'], $db_env['DB_USER'], $db_env['DB_PASS'], $db_env['DB_NAME']);
             if ($link->connect_error) throw new Exception("DB Connect Error: " . $link->connect_error);
        } else {
            throw new Exception("Could not find env.php or app/config.php to establish database connection.");
        }
    }

    // REQUIRE MigrationManager from 'app/' DIRECTORY AS REQUESTED
    require_once 'app/MigrationManager.php';
    $manager = new MigrationManager($link);

    // Handle POST Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        $res = null;
        $log_message = "";
        $log_type = "info";
        $processed_action = true; // Set flag

        switch ($action) {
            case 'migrate':
                $res = $manager->migrate();
                $log_message = "MIGRATION RESULTS:\n" . print_r($res, true);
                $log_type = $res['success'] ? 'success' : 'error';
                break;
            
            case 'migrate_dry':
                $manager->setDryRun(true);
                $res = $manager->migrate();
                $log_message = "DRY RUN RESULTS:\n" . print_r($res, true);
                break;

            case 'rollback_last':
                $res = $manager->rollback(1);
                $log_message = "ROLLBACK LAST BATCH:\n" . print_r($res, true);
                $log_type = $res['rolled_back'] > 0 ? 'success' : 'info';
                break;
                
            case 'rollback_steps':
                $steps = (int)$_POST['steps'];
                $res = $manager->rollback($steps);
                $log_message = "ROLLBACK {$steps} BATCH(ES):\n" . print_r($res, true);
                $log_type = $res['rolled_back'] > 0 ? 'success' : 'info';
                break;

            case 'rollback_all':
                $res = $manager->rollbackAll();
                $log_message = "ROLLBACK ALL MIGRATIONS:\n" . print_r($res, true);
                $log_type = $res['rolled_back'] > 0 ? 'success' : 'info';
                break;

            case 'create_file':
                $name = preg_replace('/[^a-zA-Z0-9]/', '', ucwords($_POST['classname']));
                if(!$name) throw new Exception("Invalid Name");
                $file = $manager->createMigrationFile($name);
                $log_message = "Created file: " . $file;
                $log_type = 'success';
                break; 

            case 'cleanup':
                $res = $manager->cleanupMissingMigrations();
                $log_message = print_r($res, true);
                $log_type = $res['deleted'] > 0 ? 'success' : 'info';
                break;
                
            case 'describe':
                $tableName = trim($_POST['table_name']);
                if(!$tableName) throw new Exception("Table name cannot be empty.");
                
                $res = $manager->describeTable($tableName);
                
                // Format output for console log
                $log_message = "DESCRIBE TABLE: {$tableName}\n";
                if (!empty($res)) {
                    // Use a simple text-based table format for the console
                    $log_message .= str_pad("Field", 20) . str_pad("Type", 20) . str_pad("Null", 5) . str_pad("Key", 5) . str_pad("Default", 10) . "Extra\n";
                    $log_message .= str_repeat('-', 60) . "\n";
                    foreach($res as $col) {
                        $log_message .= str_pad($col['Field'], 20) 
                                     . str_pad($col['Type'], 20) 
                                     . str_pad($col['Null'], 5) 
                                     . str_pad($col['Key'], 5) 
                                     . str_pad($col['Default'] ?? 'NULL', 10)
                                     . $col['Extra'] . "\n";
                    }
                } else {
                    $log_message .= "Table not found or no columns.";
                }
                $log_type = 'info';
                break;
        }
        
        // POST-Redirect-GET (PRG) Pattern Implementation
        if ($processed_action) {
            $_SESSION['action_result'] = [
                'message' => $log_message,
                'type' => $log_type
            ];
            header("Location: migrate.php");
            exit; 
        }
    }

    // Get Data for View
    $status = $manager->getStatus();
    $logs = $manager->getLogs(3); // Last 3 days

} catch (Exception $e) {
    $output_log = "CRITICAL ERROR: " . $e->getMessage();
    $message_type = "error";
    $status = [];
    $logs = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Migration Manager</title>
    <style>
        :root { --bg: #1a1a1a; --panel: #2d2d2d; --border: #444; --text: #e0e0e0; --green: #4caf50; --red: #ff5252; --blue: #2196f3; --yellow: #ffc107; }
        body { font-family: 'Segoe UI', monospace; background: var(--bg); color: var(--text); margin: 0; padding: 20px; font-size: 14px; }
        
        .container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr; gap: 20px; } /* Default single column for mobile */
        .header { grid-column: 1 / -1; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 1.5em; }
        .header a { margin-top: 5px; }
        
        .panel { background: var(--panel); border: 1px solid var(--border); border-radius: 6px; padding: 15px; margin-bottom: 20px; }
        h2 { margin-top: 0; border-bottom: 1px solid var(--border); padding-bottom: 10px; font-size: 1.2em; color: var(--blue); }
        
        table { width: 100%; border-collapse: collapse; font-size: 0.9em; table-layout: fixed; } /* Added fixed layout for better column control */
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #444; word-wrap: break-word; } /* Added word-wrap */
        th { background: #333; }
        
        .status-applied { color: var(--green); }
        .status-pending { color: var(--yellow); }
        .status-failed { color: var(--red); font-weight: bold; }
        .status-missing { color: #f90; font-weight: bold; } 
        
        button { background: #444; border: 1px solid #555; color: white; padding: 6px 12px; cursor: pointer; border-radius: 3px; transition: background 0.2s; }
        button:hover { background: #555; }
        button.primary { background: var(--blue); border-color: var(--blue); }
        button.primary:hover { background: #0056b3; }
        button.danger { background: var(--red); border-color: var(--red); }
        button.danger:hover { background: #d94343; }
        button.success { background: var(--green); border-color: var(--green); }
        button.success:hover { background: #418c44; }

        input[type="text"], input[type="number"] { background: #333; border: 1px solid #555; color: white; padding: 6px; border-radius: 3px; width: 100%; box-sizing: border-box; }
        
        .actions-grid { display: grid; grid-template-columns: 1fr; gap: 15px; } /* Default single column for actions */
        .form-group { margin-bottom: 0; }
        
        pre.console { background: #000; padding: 15px; border-radius: 4px; border: 1px solid #444; overflow-x: auto; white-space: pre-wrap; font-family: 'Consolas', monospace; max-height: 400px; overflow-y: auto; font-size: 0.9em; }
        .console.error { border-color: var(--red); color: #ffbaba; }
        .console.success { border-color: var(--green); color: #d4edda; }
        .console.info { color: #ccc; }

        /* --- DESKTOP/TABLET STYLES (Min-width 768px) --- */
        @media (min-width: 768px) {
            .container {
                grid-template-columns: 3fr 2fr; /* Restore original layout */
            }
            .header {
                flex-direction: row;
                align-items: center;
            }
            .header a {
                 margin-top: 0;
            }
            
            .actions-grid {
                grid-template-columns: 1fr 1fr; /* Restore original action layout */
            }
            
            input[type="text"], input[type="number"] {
                width: auto; /* Allow inputs to size naturally in flex/grid context */
            }

            /* Fix rollback steps input width on desktop */
            .actions-grid .form-group input[type="number"] {
                width: 60px;
            }
        }
        
    </style>
</head>
<body>

<div class="header">
    <h1>Database Migration Tool</h1>
    <a href="?logout=1" style="color: #ff5252; text-decoration: none;">[ Logout ]</a>
</div>

<div class="container">
    
    <div>
        <div class="panel">
            <h2>Migration Status</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50%;">Migration Name</th>
                            <th style="width: 15%;">Batch</th>
                            <th style="width: 35%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($status)): ?>
                            <tr><td colspan="3">No migrations found.</td></tr>
                        <?php else: ?>
                            <?php foreach($status as $m): 
                                $status_text = strtoupper($m['status']);
                                $status_class = strtolower($m['status']);
                                if (strpos($status_text, 'FAILED') !== false) {
                                    $status_class = 'failed';
                                } elseif (strpos($status_text, 'MISSING FILE') !== false) {
                                    $status_class = 'missing';
                                }
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($m['name'], 16)); ?> <br><small style="color:#666; font-size: 0.7em;"><?php echo $m['name']; ?></small></td>
                                    <td><?php echo $m['batch'] ?: '-'; ?></td>
                                    <td class="status-<?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <h2>Console Output</h2>
            <pre class="console <?php echo $message_type; ?>"><?php echo htmlspecialchars($output_log ?: "Ready..."); ?></pre>
        </div>
    </div>

    <div>
        <div class="panel">
            <h2>Migration Controls</h2>
            <div class="actions-grid">
                
                <form method="POST" style="grid-column: 1 / -1;">
                    <input type="hidden" name="action" value="migrate">
                    <div class="form-group">
                        <button type="submit" class="primary" style="width:100%" onclick="return confirm('Run all pending migrations?')">Run Migrations (UP)</button>
                    </div>
                </form>

                <form method="POST">
                    <input type="hidden" name="action" value="migrate_dry">
                    <div class="form-group">
                        <button type="submit" style="width:100%">Dry Run (Simulate)</button>
                    </div>
                </form>
                
                <form method="POST">
                    <input type="hidden" name="action" value="rollback_last">
                    <div class="form-group">
                        <button type="submit" class="danger" style="width:100%" onclick="return confirm('Are you sure you want to rollback the LAST BATCH?')">Rollback Last Batch</button>
                    </div>
                </form>


                <form method="POST" style="grid-column: 1 / -1;">
                    <input type="hidden" name="action" value="rollback_steps">
                    <div class="form-group" style="display:flex; gap:10px;">
                        <input type="number" name="steps" value="1" min="1" style="width: 60px" title="Number of batches to rollback">
                        <button type="submit" class="danger" style="flex:1" onclick="return confirm('Are you sure you want to rollback the specified number of batches?')">Rollback N Batches</button>
                    </div>
                </form>
            </div>

            <div style="margin-top: 10px; border-top: 1px solid #444; padding-top: 10px; display:flex; gap: 10px; flex-wrap: wrap;">
                 <form method="POST" style="flex:1; min-width: 45%;">
                    <input type="hidden" name="action" value="rollback_all">
                    <button type="submit" class="danger" style="width:100%" onclick="return confirm('DANGER: This will revert ALL migrations. Are you absolutely sure?')">Rollback ALL (Reset)</button>
                </form>
                 <form method="POST" style="flex:1; min-width: 45%;">
                    <input type="hidden" name="action" value="cleanup">
                    <button type="submit" class="danger" style="width:100%; background: #9c27b0;" onclick="return confirm('Cleanup Missing Files? This deletes DB records for files not found on disk.')">Cleanup Missing</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <h2>Create Migration File</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create_file">
                <div class="form-group" style="display:flex; gap:10px;">
                    <input type="text" name="classname" placeholder="e.g. CreateUsersTable" required style="flex:1">
                    <button type="submit" class="success">Create</button>
                </div>
            </form>
        </div>
        
          <div class="panel">
            <h2>Describe Table</h2>
             <form method="POST">
                <input type="hidden" name="action" value="describe">
                <div class="form-group" style="display:flex; gap:10px;">
                    <input type="text" name="table_name" placeholder="Table Name" required style="flex:1">
                    <button type="submit">View Schema</button>
                </div>
            </form>
        </div>

          <div class="panel">
            <h2>Logs</h2>
            <div style="max-height: 200px; overflow-y: auto;">
                <?php foreach($logs as $date => $content): ?>
                    <details>
                        <summary style="cursor:pointer; color: var(--blue)"><?php echo $date; ?></summary>
                        <pre style="font-size: 0.8em; white-space: pre-wrap; color: #999;"><?php echo htmlspecialchars($content); ?></pre>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>