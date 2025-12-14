<?php

class AddVisibilityAndVerifiedToArtifacts
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up()
    {
        $sql = "
        ALTER TABLE artifacts
            ADD COLUMN visibility BOOLEAN NOT NULL DEFAULT TRUE AFTER is_private,
            ADD COLUMN verified BOOLEAN NOT NULL DEFAULT FALSE AFTER visibility;
        ";

        if (!$this->db->query($sql)) {
            throw new Exception($this->db->error);
        }
    }

    public function down()
    {
        $sql = "
        ALTER TABLE artifacts
            DROP COLUMN verified,
            DROP COLUMN visibility;
        ";

        if (!$this->db->query($sql)) {
            throw new Exception($this->db->error);
        }
    }
}
