# Database Migration System - Complete Guide

## 📋 Table of Contents
1. [Setup](#setup)
2. [Core Concepts](#core-concepts)
3. [Workflow Guide](#workflow-guide)
4. [Command Reference](#command-reference)
5. [Best Practices](#best-practices)
6. [Troubleshooting](#troubleshooting)
7. [Production Checklist](#production-checklist)

---

## Setup

### 1. Create Migration Directories

```bash
mkdir -p app/migrations/backups
mkdir -p app/migrations/logs
chmod 755 app/migrations/backups app/migrations/logs
```

### 2. Add to Your Project

Place `MigrationManager.php` in `app/migrations/`  
Place `migrate.php` in your project root  

### 3. Make CLI Executable

```bash
chmod +x migrate.php
```

### 4. Update Your .env

```env
DB_HOST=localhost
DB_NAME=your_database
DB_USER=root
DB_PASS=your_password
```

---

## Core Concepts

### What is a Migration?

A migration is a file containing database changes (up) and how to undo them (down). Think of it like version control for your database schema.

### Batch System

Each time you run `php migrate.php up`, all pending migrations run as one "batch". If any migration fails, the entire batch rolls back.

**Why?** Atomicity - either all changes succeed or none do. No partial updates.

### Backup System

Before EVERY batch (up or down), a complete database backup is created automatically.

**Location:** `app/migrations/backups/`

---

## Workflow Guide

### Scenario 1: Adding a New Column

**Step 1: Create Migration**
```bash
php migrate.php make add_email_to_users
```

**Step 2: Edit the migration file** (e.g., `app/migrations/20240101120000_add_email_to_users.php`)

```php
public function up() {
    $sql = "ALTER TABLE users ADD COLUMN email VARCHAR(255) NOT NULL UNIQUE";
    $this->pdo->exec($sql);
}

public function down() {
    $sql = "ALTER TABLE users DROP COLUMN email";
    $this->pdo->exec($sql);
}
```

**Step 3: Run migration**
```bash
php migrate.php up
```

Output should show:
```
✓ Backup created: before_batch_1_20240101_120000.sql
✓ Executed: 20240101120000_add_email_to_users (0.1234s)
✓ Batch 1 completed successfully
```

**Step 4: Verify in database**
```bash
php migrate.php status
```

---

### Scenario 2: Something Went Wrong

**Situation:** You ran a migration but the code was wrong and now the database is broken.

**Immediate Fix (Last Batch Only):**
```bash
php migrate.php down
```

The system will:
1. Create a backup first
2. Run all `down()` methods from that batch in reverse order
3. Restore database to previous state

**If You Can't Rollback:**

1. List available backups:
```bash
php migrate.php backups
```

2. Restore from backup:
```bash
php migrate.php restore before_batch_1_20240101_120000.sql
```

---

### Scenario 3: Testing Migrations Locally

Before pushing to production:

```bash
# Run your new migrations
php migrate.php up

# Test your application thoroughly

# If there are issues, rollback
php migrate.php down

# Fix your migration code

# Run again
php migrate.php up
```

Repeat until perfect.

---

### Scenario 4: Multiple Developers

**Developer A** creates migration `add_status_column`  
**Developer B** creates migration `add_role_column`

Both add different migrations. When you run `php migrate.php up`, both run in order (by timestamp).

**Rule:** Never modify another developer's migration file. Create a new one instead if changes are needed.

---

## Command Reference

### Create a Migration

```bash
php migrate.php make <name>
```

Names should be descriptive:
- ✅ `add_email_column_to_users`
- ✅ `create_orders_table`
- ✅ `add_index_to_users_email`
- ❌ `fix_db`
- ❌ `changes`

### Run Pending Migrations

```bash
php migrate.php up
```

Or:
```bash
php migrate.php run
```

### Rollback Last Batch

```bash
php migrate.php down
```

Prompts for confirmation before executing.

### View Migration History

```bash
php migrate.php status
```

Shows: migration name, status (completed/failed/rolled_back), batch, execution time, timestamp.

### Manage Backups

**List all backups:**
```bash
php migrate.php backups
```

**Restore from backup:**
```bash
php migrate.php restore before_batch_2_20240101_120500.sql
```

### Advanced Commands

**Refresh** (rollback all, then run all):
```bash
php migrate.php refresh
```

Use this when testing migrations in development.

**Reset** (rollback everything):
```bash
php migrate.php reset
```

⚠️ DESTRUCTIVE - Only use in development!

---

## Best Practices

### 1. Always Make Down() Match Up()

**WRONG:**
```php
public function up() {
    $this->pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20)");
}

public function down() {
    // Forgot to drop the column!
}
```

**CORRECT:**
```php
public function up() {
    $this->pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20)");
}

public function down() {
    $this->pdo->exec("ALTER TABLE users DROP COLUMN phone");
}
```

### 2. One Change Per Migration

**WRONG:**
```php
public function up() {
    $this->pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20)");
    $this->pdo->exec("ALTER TABLE users ADD COLUMN address TEXT");
    $this->pdo->exec("ALTER TABLE orders ADD COLUMN status VARCHAR(50)");
    // Too many changes in one migration
}
```

**CORRECT - Create 3 separate migrations:**
```bash
php migrate.php make add_phone_to_users
php migrate.php make add_address_to_users
php migrate.php make add_status_to_orders
```

Why? If one fails, you can identify and fix it easily.

### 3. Never Modify Data in Migrations (Usually)

**WRONG:**
```php
public function up() {
    $this->pdo->exec("DELETE FROM users WHERE age < 18");
}
```

**CORRECT - Data changes go in scripts/seeder, not migrations:**
```php
public function up() {
    // Only schema changes here
    $this->pdo->exec("ALTER TABLE users ADD COLUMN age_verified INT DEFAULT 0");
}
```

### 4. Be Explicit with Constraints

**WRONG:**
```php
$this->pdo->exec("CREATE TABLE posts (id INT, title TEXT)");
```

**CORRECT:**
```php
$this->pdo->exec("
    CREATE TABLE posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content LONGTEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");
```

### 5. Use Transactions for Safety

```php
public function up() {
    try {
        // Already wrapped by MigrationManager, but good to know
        $this->pdo->exec("ALTER TABLE users ADD COLUMN status VARCHAR(50)");
        $this->pdo->exec("UPDATE users SET status = 'active'");
    } catch (Exception $e) {
        throw $e; // MigrationManager will rollback
    }
}
```

### 6. Test Migrations in Development First

```bash
# Local testing
php migrate.php up
# Test your app
php migrate.php down
# Fix if needed
php migrate.php up
# Once perfect, commit and push
```

---

## Troubleshooting

### Migration Fails: "Syntax Error"

```
✗ Executed: 20240101120000_add_email (Error: Syntax error in SQL)
```

**Fix:**
1. Check your SQL syntax
2. Run `php migrate.php down` to rollback
3. Edit the migration file
4. Run `php migrate.php up` again

### Migration Hangs

**Cause:** Large table operations without timeout.

**Fix:** Add timeouts or split into multiple migrations:
```php
$this->pdo->exec("SET SESSION max_execution_time = 300");
$this->pdo->exec("ALTER TABLE large_table ADD COLUMN new_col INT");
```

### Backup Creation Failed

```
✗ Backup creation failed
```

**Check:**
1. `app/migrations/backups/` exists and is writable
2. `mysqldump` is installed: `which mysqldump`
3. Database credentials in `.env` are correct

### Can't Restore Backup

```
✗ Restore error: Backup file not found
```

**Fix:**
1. List backups: `php migrate.php backups`
2. Use exact filename: `php migrate.php restore exact_filename.sql`

### Lost Data After Migration

**Recovery:**
1. List all backups: `php migrate.php backups`
2. Find backup from before the problem migration
3. Restore: `php migrate.php restore backup_name.sql`
4. Identify what went wrong
5. Create a proper migration
6. Run: `php migrate.php up`

---

## Production Checklist

Before deploying to production:

- [ ] All migrations tested locally
- [ ] Each migration has proper `up()` and `down()` methods
- [ ] Migrations are one change per file
- [ ] Down methods undo exactly what up does
- [ ] No hardcoded data/sensitive info in migrations
- [ ] Tested rollback scenario: `php migrate.php up` then `php migrate.php down`
- [ ] Tested on copy of production database (if possible)
- [ ] Backup space available (100% of database size)
- [ ] Database user has CREATE/ALTER/DROP permissions

### Production Deployment Steps

**1. Take Database Backup (Manually)**
```bash
mysqldump -u root -p database_name > manual_backup_$(date +%Y%m%d_%H%M%S).sql
```

**2. Run Migrations**
```bash
php migrate.php up
```

**3. Verify Status**
```bash
php migrate.php status
```

**4. Test Application**
- Check critical features work
- Verify data is correct

**5. If Issues Found**
```bash
php migrate.php down
# Fix and redeploy
```

**6. Keep Backup**
- Keep at least last 3-5 backups
- Archive older backups to separate storage

---

## Decision Tree: What Should I Do?

```
I want to change the database schema
├─ Create a new migration
│  └─ php migrate.php make <name>
│
I ran migrations and need to undo
├─ Last batch only
│  └─ php migrate.php down
│
├─ Multiple batches back
│  └─ php migrate.php backups
│     php migrate.php restore <filename>
│
I modified a migration file that was already run
├─ Never do this! Instead:
│  ├─ php migrate.php down (undo it)
│  ├─ Fix the file
│  └─ php migrate.php up (run again)
│
I want to test my migrations
├─ php migrate.php refresh
│  (Rolls back and runs all again)
│
I'm unsure if my migration is safe
├─ Test locally first:
│  ├─ php migrate.php up
│  ├─ Verify data/app
│  ├─ php migrate.php down
│  └─ Once sure, go to production
│
My migration failed in production
├─ 1. Immediate: php migrate.php down
├─ 2. Verify database: php migrate.php status
├─ 3. If issues: php migrate.php restore <backup>
├─ 4. Fix migration
├─ 5. Test locally again
└─ 6. Rerun: php migrate.php up
```

---

## Example Production Migration

Real-world example - adding a new user verification system:

**Step 1: Create**
```bash
php migrate.php make add_email_verification_system
```

**Step 2: Edit migration**
```php
public function up() {
    // Add columns for email verification
    $sql = "
        ALTER TABLE users 
        ADD COLUMN email_verified_at TIMESTAMP NULL,
        ADD COLUMN verification_token VARCHAR(255),
        ADD INDEX idx_verification_token (verification_token)
    ";
    $this->pdo->exec($sql);
    
    // Mark existing users as verified
    $this->pdo->exec("UPDATE users SET email_verified_at = NOW()");
}

public function down() {
    $sql = "
        ALTER TABLE users 
        DROP INDEX idx_verification_token,
        DROP COLUMN email_verified_at,
        DROP COLUMN verification_token
    ";
    $this->pdo->exec($sql);
}
```

**Step 3: Test locally**
```bash
php migrate.php up
# Verify changes
php migrate.php down
# Verify rollback works
php migrate.php up
```

**Step 4: Commit and push**
```bash
git add app/migrations/20240101120000_add_email_verification_system.php
git commit -m "Add email verification system migration"
git push
```

**Step 5: Deploy to production**
```bash
php migrate.php up
```

---

## Summary

✅ Always create migrations for schema changes  
✅ Test migrations locally before production  
✅ Keep migrations one change per file  
✅ Make sure down() undoes up()  
✅ Use backups as safety net  
✅ Check status before and after running  
✅ Never modify ran migrations - create new ones  
✅ Rollback before fixing - then rerun  

Questions? Check `php migrate.php help`