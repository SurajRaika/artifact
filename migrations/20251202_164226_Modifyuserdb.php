<?php

class Modifyuserdb {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Run the migrations.
     * This method applies the schema change.
     */
    public function up() {
        // SQL Example:
        // $sql = "
        //     CREATE TABLE users (
        //         id INT AUTO_INCREMENT PRIMARY KEY,
        //         username VARCHAR(50) NOT NULL UNIQUE,
        //         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        //     )
        // ";
        // if ($this->db->query($sql) === false) {
        //     throw new Exception($this->db->error);
        // }
    }

    /**
     * Reverse the migrations.
     * This method reverses the schema change made in up().
     */
    public function down() {
        // SQL Example:
        // $sql = "DROP TABLE users";
        // if ($this->db->query($sql) === false) {
        //     throw new Exception($this->db->error);
        // }
    }
}