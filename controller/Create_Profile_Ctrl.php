<?php
require_once __DIR__ . '/../entity/UserProfile.php';

// <<Controller>> Create_Profile_Ctrl
class Create_Profile_Ctrl {

    // createProfile(str profileName, array permissionIds): bool
    public function createProfile($profileName, $permissionIds) {
        $profile = new UserProfile();
        return $profile->saveProfile($profileName, $permissionIds);   // saveProfile(): bool
    }

    // GetPermissions(): array  -> used to display the checkboxes (Main Flow step 3)
    public function GetPermissions() {
        $profile = new UserProfile();
        return $profile->GetAllPermissions();
    }
}
