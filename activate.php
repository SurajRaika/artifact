<?php
$host = "localhost";
$user = "phpusr";
$pass = "password";


$dbname   = "mylocalporject_artisan";




try {
    // Connect without database to create it
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop old tables just in case (optional)
    // $pdo->exec("DROP TABLE IF EXISTS `gists`");
    // $pdo->exec("DROP TABLE IF EXISTS `users`");

    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(100) NOT NULL,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB;
    ");

    // Create gists table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `gists` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `userid` INT NOT NULL,
            `description` VARCHAR(255) NOT NULL,
            `filename` VARCHAR(255) NOT NULL,
            `content` TEXT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");



    // Create lock file
    file_put_contents(__DIR__.'/installed.lock', 'installed');

    echo "Installation complete. <a href='index.php'>Go to app</a>";

} catch (PDOException $e) {
    die("Install failed: " . $e->getMessage());
}
?>