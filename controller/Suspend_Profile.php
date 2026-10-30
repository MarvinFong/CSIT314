<?php
require_once __DIR__ . '/../entity/UserProfile.php';

// <<Controller>> Suspend_Profile
class Suspend_Profile {

    // SuspendProfile(profileId): "success" | "not_found" | "already_suspended" | "failed"
    public function SuspendProfile($profileId) {
        $profile = new UserProfile();

        $detail = $profile->getProfileDetail($profileId);        // getProfileDetail(profileId)
        if ($detail === null) {
            return "not_found";                                   // A1
        }
        if (!$detail['is_active']) {
            return "already_suspended";                           // A2
        }
        if ($profile->updateStatus($profileId, "Suspended")) {    // updateStatus(profileId, "Suspended")
            return "success";
        }
        return "failed";
    }

    // GetProfileDetail(profileId): array | null  (used by the boundary's self-suspend check)
    public function GetProfileDetail($profileId) {
        $profile = new UserProfile();
        return $profile->getProfileDetail($profileId);
    }

    // GetProfileList(): array  -> the list the admin picks from
    public function GetProfileList() {
        $profile = new UserProfile();
        return $profile->GetAllProfiles();
    }
}
