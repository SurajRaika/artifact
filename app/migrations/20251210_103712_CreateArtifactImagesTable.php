<?php

class CreateArtifactImagesTable {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function up() {
        $sql = "
        CREATE TABLE IF NOT EXISTS  artifact_images (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    artifact_id INT(10) UNSIGNED NOT NULL,
    image_url LONGTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_artifact_id (artifact_id),
    CONSTRAINT fk_artifact
        FOREIGN KEY (artifact_id) REFERENCES artifacts(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        ";
        if (!$this->db->query($sql)) throw new Exception($this->db->error);
    }

    public function down() {
        $sql = "DROP TABLE IF EXISTS artifact_images";
        if (!$this->db->query($sql)) throw new Exception($this->db->error);
    }
}