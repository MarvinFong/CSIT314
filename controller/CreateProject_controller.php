<?php
require_once __DIR__ . '/../entity/Project.php';

// <<Controller>> CreateProject_controller
class CreateProject_controller {

    // CreateProject(title, projectDate, description, status, ownerEmail, imagePaths): bool
    public function CreateProject($title, $projectDate, $description, $status, $ownerEmail, $imagePaths) {
        $project = new Project();
        return $project->saveProject($title, $projectDate, $description, $status, $ownerEmail, $imagePaths);
    }

    // GetPortfolio(ownerEmail): the designer's list of projects (step 5)
    public function GetPortfolio($ownerEmail) {
        $project = new Project();
        return $project->getProjectsByOwner($ownerEmail);
    }
}
