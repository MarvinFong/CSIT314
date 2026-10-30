<?php
require_once __DIR__ . '/../entity/UserProfile.php';

// <<Controller>> Edit_Profile
class Edit_Profile {

    // EditProfile(profileId, newName, permissionIds): bool
    public function EditProfile($profileId, $newName, $permissionIds) {
        $profile = new UserProfile();
        return $profile->saveProfileSettings($profileId, $newName, $permissionIds);   // saveProfileSettings(): bool
    }

    // GetProfileList(): array  -> options for the dropdown (step 1)
    public function GetProfileList() {
        $profile = new UserProfile();
        return $profile->GetAllProfiles();
    }

    // GetProfile(profileId): array | null  -> pre-fills the form (step 2)
    public function GetProfile($profileId) {
        $profile = new UserProfile();
        return $profile->GetProfile($profileId);
    }

    // GetPermissions(): array  -> checkboxes
    public function GetPermissions() {
        $profile = new UserProfile();
        return $profile->GetAllPermissions();
    }
}
