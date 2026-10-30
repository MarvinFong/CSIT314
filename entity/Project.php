<?php
require_once __DIR__ . '/../config/db.php';

// <<Entity>> Project
class Project {

    // saveProject(title, projectDate, description, status, ownerEmail, imagePaths): bool
    // Saves the project and its images together. All or nothing.
    public function saveProject($title, $projectDate, $description, $status, $ownerEmail, $imagePaths) {
        $conn = (new Database())->connect();
        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "INSERT INTO projects (title, project_date, description, status, created_by)
                 SELECT ?, ?, ?, ?, user_id FROM user_accounts WHERE email = ?");
            $stmt->bind_param("sssss", $title, $projectDate, $description, $status, $ownerEmail);
            if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                $conn->rollback();
                return false;
            }
            $projectId = (int)$conn->insert_id;

            foreach ($imagePaths as $path) {
                $img = $conn->prepare("INSERT INTO project_images (project_id, file_path) VALUES (?, ?)");
                $img->bind_param("is", $projectId, $path);
                if (!$img->execute()) {
                    $conn->rollback();
                    return false;
                }
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    // getProjectsByOwner(ownerEmail): the designer's portfolio, newest first
    public function getProjectsByOwner($ownerEmail) {
        $conn = (new Database())->connect();
        $stmt = $conn->prepare(
            "SELECT p.project_id, p.title, p.project_date, p.status,
                    (SELECT i.file_path FROM project_images i
                     WHERE i.project_id = p.project_id ORDER BY i.image_id LIMIT 1) AS cover
             FROM projects p
             JOIN user_accounts u ON u.user_id = p.created_by
             WHERE u.email = ?
             ORDER BY p.created_at DESC, p.project_id DESC");
        $stmt->bind_param("s", $ownerEmail);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = array();
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
