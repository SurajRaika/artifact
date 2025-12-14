<?php

class ChangeDefaultVisibility {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function up() {
        $sql = "ALTER TABLE artifacts MODIFY visibility BOOLEAN NOT NULL DEFAULT FALSE";
        if (!$this->db->query($sql)) throw new Exception($this->db->error);
    }

    public function down() {
        $sql = "ALTER TABLE artifacts MODIFY visibility BOOLEAN NOT NULL DEFAULT TRUE";
        if (!$this->db->query($sql)) throw new Exception($this->db->error);
    }
}