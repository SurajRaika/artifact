<?php

class CreateArtifact
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up()
    {
      $sql = "
CREATE TABLE IF NOT EXISTS artifacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    seller_id INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_private BOOLEAN NOT NULL DEFAULT FALSE,
    password VARCHAR(255) NULL,

    CONSTRAINT chk_password_if_private CHECK (
        is_private = FALSE OR password IS NOT NULL
    ),

    CONSTRAINT fk_seller FOREIGN KEY (seller_id) REFERENCES users(id)
) ENGINE=InnoDB;
";

        if (!$this->db->query($sql)) throw new Exception($this->db->error);
    }

    public function down()
    {
        $sql = "DROP TABLE IF EXISTS artifacts";
        if (!$this->db->query($sql)) throw new Exception($this->db->error);
    }
}
