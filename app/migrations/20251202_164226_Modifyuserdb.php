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
        // Add a credit column to store user money / balance.
        $sql = "
            ALTER TABLE users
            ADD COLUMN credit DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER password
        ";

        if ($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
    }

    /**
     * Reverse the migrations.
     * This method reverses the schema change.
     */
    public function down() {
        // Remove the credit column.
        $sql = "
            ALTER TABLE users
            DROP COLUMN credit
        ";

        if ($this->db->query($sql) === false) {
            throw new Exception($this->db->error);
        }
    }
}
