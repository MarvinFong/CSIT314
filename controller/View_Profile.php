<?php
require_once __DIR__ . '/../entity/UserProfile.php';

// <<Controller>> View_Profile
class View_Profile {

    // ViewProfile(userId): profile | null
    public function ViewProfile($userId) {
        $profile = new UserProfile();
        return $profile->getProfileDetails($userId);      // getProfileDetails(userId)
    }
}
