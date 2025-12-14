<?php

class Artifact
{
    private $link;

    public function __construct($link)
    {
        $this->link = $link;
    }
    public function name_exists($name)
    {
        $sql = "SELECT id FROM artifacts WHERE name = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $exists = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $exists;
    }
    public function delete_artifact($artifact_id, $user_id)
    {
        // 1. Check ownership
        $check_sql = "SELECT seller_id FROM artifacts WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($this->link, $check_sql);

        if (!$stmt) {
            return "Database error during ownership check.";
        }

        mysqli_stmt_bind_param($stmt, "i", $artifact_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result || mysqli_num_rows($result) === 0) {
            return "Artifact not found.";
        }

        $row = mysqli_fetch_assoc($result);
        $seller_id = (int)$row['seller_id'];
        mysqli_stmt_close($stmt); // Close the check statement

        if ($seller_id !== (int)$user_id) {
            return "You are not allowed to delete this artifact.";
        }

        // 2. If ownership is confirmed, proceed with deletion
        $delete_sql = "DELETE FROM artifacts WHERE id = ?";
        $delete_stmt = mysqli_prepare($this->link, $delete_sql);

        if (!$delete_stmt) {
            return "Database error during deletion preparation: " . mysqli_error($this->link);
        }

        mysqli_stmt_bind_param($delete_stmt, "i", $artifact_id);

        if (mysqli_stmt_execute($delete_stmt)) {
            mysqli_stmt_close($delete_stmt);
            return null; // Success
        } else {
            $error = mysqli_stmt_error($delete_stmt);
            mysqli_stmt_close($delete_stmt);
            return "Database Execute Error during deletion: " . $error;
        }
    }
    public function create_artifact(
        $name,
        $description,
        $seller_id,
        $is_private,
        $preview_count,
        $password = null
    ) {
        // 1. Check name uniqueness BEFORE preparing the statement
        if ($this->name_exists($name)) {
            return "Error: Artifact name already exists.";
        }

        // 2. Hash password only if private + provided
        $hashed_password = null;
        if ($is_private && $password !== null) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        }

        // 3. Ensure preview_count is an integer
        $preview_count = (int)$preview_count;
        $is_private_int = (int)$is_private;

        // 4. SQL including preview_count
        $sql = "
        INSERT INTO artifacts
        (name, description, seller_id, is_private, preview_count, password)
        VALUES (?, ?, ?, ?, ?, ?)
    ";

        $stmt = mysqli_prepare($this->link, $sql);
        if (!$stmt) {
            return "Internal Error: DB Prepare failed. " . mysqli_error($this->link);
        }

        // Types: s = string, i = integer
        mysqli_stmt_bind_param(
            $stmt,
            "ssiiss",
            $name,
            $description,
            $seller_id,
            $is_private_int,
            $preview_count,
            $hashed_password
        );

        // 5. Execute & handle errors
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null; // Success
        } else {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            return "Database Execute Error: " . $error;
        }
    }

    public function update_visibility_secure($artifact_id, $visibility, $user_id)
    {
        // 1. Check ownership
        $check_sql = "SELECT seller_id FROM artifacts WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($this->link, $check_sql);

        if (!$stmt) {
            return "Database error during ownership check.";
        }

        mysqli_stmt_bind_param($stmt, "i", $artifact_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result || mysqli_num_rows($result) === 0) {
            return "Artifact not found.";
        }

        $row = mysqli_fetch_assoc($result);

        if ((int)$row['seller_id'] !== (int)$user_id) {
            return "You are not allowed to update this artifact.";
        }

        // 2. Update only visibility
        $update_sql = "UPDATE artifacts SET visibility = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($this->link, $update_sql);

        if (!$update_stmt) {
            return "Database error during update.";
        }

        mysqli_stmt_bind_param($update_stmt, "ii", $visibility, $artifact_id);

        if (mysqli_stmt_execute($update_stmt)) {
            return null; // success
        }

        return mysqli_stmt_error($update_stmt);
    }

    public function uploadImages($artifact_id, array $image_urls)
    {
        $sql = "INSERT INTO artifact_images (artifact_id, image_url) VALUES (?, ?)";
        $stmt = mysqli_prepare($this->link, $sql);

        if (!$stmt) {
            return "Internal Error: DB Prepare failed for image upload. " . mysqli_error($this->link);
        }

        foreach ($image_urls as $url) {
            mysqli_stmt_bind_param($stmt, "is", $artifact_id, $url);
            if (!mysqli_stmt_execute($stmt)) {
                $error = mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
                return "Database Execute Error during image upload for URL '{$url}': " . $error;
            }
        }

        mysqli_stmt_close($stmt);
        return null; // Success
    }



    public function getPreviewImage($artifact_id, $preview_number)
    {
        // Ensure preview number is valid (1, 2, 3, ...)
        if ($preview_number < 1) {
            return null;
        }

        // SQL: get images ordered by ID and pick the one based on preview number
        $sql = "SELECT image_url 
            FROM artifact_images 
            WHERE artifact_id = ? 
            ORDER BY id ASC 
            LIMIT 1 OFFSET ?";

        $stmt = mysqli_prepare($this->link, $sql);
        if (!$stmt) {
            return "Internal Error: DB Prepare failed for preview image. " . mysqli_error($this->link);
        }

        // OFFSET is zero-based, so subtract 1
        $offset = $preview_number - 1;

        mysqli_stmt_bind_param($stmt, "ii", $artifact_id, $offset);

        if (!mysqli_stmt_execute($stmt)) {
            $error = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            return "Database Execute Error: " . $error;
        }

        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['image_url']; // Found the preview image
        }

        mysqli_stmt_close($stmt);
        return null; // No image for this preview number
    }
 public function get_artifacts($seller_id)
    {
        // Ensure all necessary columns (id, name, description, is_private, is_verified) are selected.
        // Assuming 'is_verified' is a column in your 'artifacts' table (0 or 1).
        $sql = "SELECT id, name, description, seller_id, is_private, preview_count, password, visibility, verified 
            FROM artifacts 
            WHERE seller_id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $seller_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $artifacts = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $artifacts;
    }



    public function get_artifacts_by_seller_id($seller_id)
    {
        // Ensure all necessary columns (id, name, description, is_private, is_verified) are selected.
        // Assuming 'is_verified' is a column in your 'artifacts' table (0 or 1).
        $sql = "SELECT id, name, description, seller_id, is_private, preview_count, password, visibility, verified 
            FROM artifacts 
            WHERE seller_id = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $seller_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $artifacts = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $artifacts;
    }
}
