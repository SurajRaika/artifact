# Production Migration System - Complete Guide

## Overview

This is a production-ready database migration system that handles schema versioning, rollbacks, backups, and full audit trails. It's designed to prevent the common bugs found in the original implementation.

## Key Improvements Over Original

### Bug Fixes

1. **Rollback Table Tracking** - Fixed the critical bug where rollback didn't update the migrations table
   - Now properly sets `status = 'rolled_back'` and `rolled_back_at` timestamp
   - Clears batch number to prevent re-application of old migrations

2. **Migration State Management** - All migration states are now tracked correctly
   - Applied, rolled back, failed, and pending states all sync properly
   - Prevents orphaned migrations

3. **Crash Recovery** - If a migration fails mid-execution
   - Status recorded as 'failed'
   - Never attempts to re-run failed migrations
   - Manual intervention required

4. **Dry-Run Mode** - Properly simulates migrations without touching database
   - Separate environment tracking
   - Doesn't update migration table

5. **Checksum Validation** - Detects if migration files have been modified
   - Stored on first run
   - Can detect tampering or accidental changes

### New Features

- Execution time tracking (duration_ms)
- Comprehensive logging to daily files
- Backup/restore functionality
- Migration verification tool
- Detailed batch management
- Better error messages and stack traces
- Full CLI with user confirmations for dangerous operations

---

## Setup Instructions

### 1. Directory Structure

```
project/
├── app/
│   ├── config.php              # Database config
│   ├── MigrationManager.php    # Core migration logic
│   └── migrations/
│       ├── backups/            # Auto-created
│       └── logs/               # Auto-created
├── migrate.php                 # CLI tool
└── your-app-files/
```

### 2. Create app/config.php

```php
<?php
// app/config.php
$db = new mysqli(
    getenv('DB_HOST') ?? 'localhost',
    getenv('DB_USER') ?? 'root',
    getenv('DB_PASS') ?? '',
    getenv('DB_NAME') ?? 'myapp'
);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$db->set_charset("utf8mb4");

// For CLI tools to reference
$link = $db;
```

### 3. Verify Setup

```bash
php migrate.php help
```

You should see the full command list.

---

## Common Workflows

### Creating a Migration

```bash
php migrate.php make:migration CreateUsersTable
```

This creates: `app/migrations/20250305_143022_CreateUsersTable.php`

Edit the file and add your SQL:

```php
public function up() {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($this->db->query($sql) === false) {
        throw new Exception("Error: " . $this->db->error);
    }
}

public function down() {
    $sql = "DROP TABLE IF EXISTS users";
    
    if ($this->db->query($sql) === false) {
        throw new Exception("Error: " . $this->db->error);
    }
}
```

**Important Rules:**

- Always use `IF NOT EXISTS` in up()
- Always use `IF EXISTS` in down()
- Throw exceptions on errors
- Make down() idempotent (can run multiple times safely)

### Preview Before Running

```bash
php migrate.php migrate:dry-run
```

Shows what WOULD run without actually running it.

### Run Migrations

```bash
# Run all pending
php migrate.php migrate

# Run up to a specific migration
php migrate.php migrate CreateUsersTable
```

### Check Status

```bash
php migrate.php status
```

Output shows:
- ✅ Applied migrations (green)
- ⏳ Pending migrations (yellow)
- ❌ Failed migrations (red)

### Rollback

```bash
# Rollback last batch
php migrate.php rollback

# Rollback last 3 batches
php migrate.php rollback 3

# Rollback EVERYTHING (dangerous!)
php migrate.php rollback:all
```

---

## Advanced Scenarios

### Scenario 1: Migration Failed Halfway

**Problem:** Your migration crashed, database is in bad state.

**Solution:**
1. Check status: `php migrate.php status`
2. You'll see the migration marked as 'failed'
3. Fix the underlying database issue manually
4. Fix your migration file
5. Roll back: `php migrate.php rollback`
6. Edit migration and rerun: `php migrate.php migrate`

### Scenario 2: Need to Add a Column to Existing Table

**Bad approach:**
```php
public function up() {
    $sql = "ALTER TABLE users ADD COLUMN phone VARCHAR(20)";
    $this->db->query($sql);
}
```

**Good approach:**
```php
public function up() {
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20)";
    
    if ($this->db->query($sql) === false) {
        throw new Exception("Error: " . $this->db->error);
    }
}

public function down() {
    $sql = "ALTER TABLE users DROP COLUMN IF EXISTS phone";
    
    if ($this->db->query($sql) === false) {
        throw new Exception("Error: " . $this->db->error);
    }
}
```

### Scenario 3: Large Data Migration

For migrations involving data transformation:

```php
public function up() {
    // Step 1: Create new column
    $sql1 = "ALTER TABLE users ADD COLUMN email_lower VARCHAR(255)";
    if ($this->db->query($sql1) === false) {
        throw new Exception("Error: " . $this->db->error);
    }
    
    // Step 2: Populate with data
    $sql2 = "UPDATE users SET email_lower = LOWER(email)";
    if ($this->db->query($sql2) === false) {
        throw new Exception("Error: " . $this->db->error);
    }
    
    // Step 3: Add constraint
    $sql3 = "ALTER TABLE users ADD UNIQUE KEY uk_email_lower (email_lower)";
    if ($this->db->query($sql3) === false) {
        throw new Exception("Error: " . $this->db->error);
    }
}

public function down() {
    $sql = "ALTER TABLE users DROP COLUMN email_lower";
    if ($this->db->query($sql) === false) {
        throw new Exception("Error: " . $this->db->error);
    }
}
```

### Scenario 4: Safe Production Deploy

```bash
# 1. Backup first
php migrate.php backup production_before_deploy

# 2. Preview
php migrate.php migrate:dry-run

# 3. Run
php migrate.php migrate

# 4. Verify
php migrate.php status

# 5. Verify app works, then cleanup old backups
php migrate.php backups
```

---

## Backup & Restore

### Create Backup

```bash
# With default timestamp name
php migrate.php backup

# With custom name
php migrate.php backup my_backup_name
```

### List Backups

```bash
php migrate.php backups
```

### Restore from Backup

```bash
php migrate.php restore

# Then select the backup ID from the list
```

---

## Monitoring & Troubleshooting

### View Logs

```bash
# Last 7 days
php migrate.php logs

# Last 30 days
php migrate.php logs 30
```

Logs are in: `app/migrations/logs/YYYY-MM-DD.log`

### Verify Integrity

```bash
php migrate.php verify
```

Checks for:
- Missing files for applied migrations
- Orphaned migration files
- Failed migrations

### Database Schema

The `migrations` table tracks:

```
id              - Auto-increment ID
migration       - Migration filename (without .php)
batch           - Batch number (migrations run together)
status          - applied | rolled_back | failed | pending
checksum        - SHA256 of migration file
executed_at     - When it ran
rolled_back_at  - When it was rolled back
error_message   - Error if status is 'failed'
duration_ms     - How long migration took
```

---

## Best Practices

### DO

✅ Write idempotent migrations (safe to run multiple times)
✅ Always check `IF NOT EXISTS` / `IF EXISTS`
✅ Test migrations locally first
✅ Use dry-run before production
✅ Backup before major migrations
✅ Keep migrations small and focused
✅ Name migrations clearly (CreateUsersTable, AddPhoneToUsers)
✅ Use transactions where possible
✅ Document complex migrations

### DON'T

❌ Modify migration files after they're applied
❌ Drop tables without backup
❌ Leave migrations without down() methods
❌ Run complex app logic in migrations
❌ Assume migrations are fast
❌ Skip dry-run for production
❌ Delete or modify migration logs
❌ Name migrations with timestamps (they auto-add them)
❌ Use raw user input in migrations

---

## Example: Complete Migration Lifecycle

### Step 1: Create

```bash
php migrate.php make:migration AddStatusToOrders
```

### Step 2: Edit `app/migrations/20250305_143022_AddStatusToOrders.php`

```php
<?php

class AddStatusToOrders {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function up() {
        $sql = "ALTER TABLE orders 
                ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'pending'";
        
        if ($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
    }

    public function down() {
        $sql = "ALTER TABLE orders DROP COLUMN IF EXISTS status";
        
        if ($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
    }
}
```

### Step 3: Preview

```bash
$ php migrate.php migrate:dry-run

Running migrations in DRY-RUN mode...

The following 1 migration(s) WOULD be executed:
  • 20250305_143022_AddStatusToOrders

✓ Dry-run complete. No changes made to database.
```

### Step 4: Apply

```bash
$ php migrate.php migrate

Running pending migrations...
✓ Successfully applied 1 migrations.
Batch: 1 | Migrations run: 1
  ✓ 20250305_143022_AddStatusToOrders (45ms)
```

### Step 5: Verify

```bash
$ php migrate.php status

Migration Status:
...
| 20250305_143022_AddStatus... | applied      | 1      | ...
```

### Step 6: Rollback (if needed)

```bash
$ php migrate.php rollback

⚠️ Rollback last batch? (type 'yes' to proceed): yes

✓ Successfully rolled back 1 migrations.
Rolled back all: 1 migrations
  ✓ 20250305_143022_AddStatusToOrders (32ms)
```

---

## Troubleshooting Common Issues

### "No database connection"

Check `app/config.php` exists and `$link` is defined.

### "Failed to create directory"

Ensure the script has write permissions to `app/`.

```bash
chmod -R 755 app/
```

### "Migration syntax error"

Check the migration file PHP syntax:

```bash
php -l app/migrations/20250305_143022_YourMigration.php
```

### "Rollback didn't reverse my migration"

Ensure your `down()` method is properly implemented. It must:
- Reverse the `up()` changes
- Be idempotent (safe to run twice)
- Not fail if column/table doesn't exist

---

## Production Checklist

- [ ] Database backups configured
- [ ] `migrate.php` file permissions restricted (644)
- [ ] Migration directory writable by PHP process
- [ ] Test migration/rollback locally first
- [ ] Backup database before applying migrations
- [ ] Run dry-run first
- [ ] Have rollback plan
- [ ] Monitor logs after migration
- [ ] Document any manual steps needed