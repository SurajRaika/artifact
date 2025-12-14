<?php

class CreateUsersTable {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Run the migrations.
     * This method creates the users table.
     */
    public function up() {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                credit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        if ($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
    }

    /**
     * Reverse the migrations.
     * This method drops the users table.
     */
    public function down() {
        $sql = "DROP TABLE IF EXISTS users";

        if ($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
    }
}
