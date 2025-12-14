<?php

class AddPreviewCountToArtifacts
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function up()
    {
        // Add a field to store how many previews an artifact has
        $sql = "
ALTER TABLE artifacts
ADD COLUMN preview_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER description;
";

        if (!$this->db->query($sql)) {
            throw new Exception($this->db->error);
        }
    }

    public function down()
    {
        // Remove the field if rolled back
        $sql = "
ALTER TABLE artifacts
DROP COLUMN preview_count;
";

        if (!$this->db->query($sql)) {
            throw new Exception($this->db->error);
        }
    }
}
