<?php
require_once __DIR__ . '/../entity/UserProfile.php';

// <<Control>> SearchProfilesController
class SearchProfilesController {

    // search(keyword): array[UserProfile]
    public function search($keyword) {
        $profile = new UserProfile();
        return $profile->searchRoles($keyword);        // searchRoles(keyword)
    }
}
