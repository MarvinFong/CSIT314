<?php
require_once __DIR__ . '/../entity/UserAcc.php';
require_once __DIR__ . '/../entity/UserProfile.php';

// <<Control>> Update_UserAcc_Controller
class Update_UserAcc_Controller {

    // UpdateAccount(str username, str new_email, str new_role, str new_password): bool
    public function UpdateAccount($username, $new_email, $new_role, $new_password = '') {
        $user = new UserAcc();
        return $user->ModifyAccount($username, $new_email, $new_role, $new_password);
    }

    // GetAccount(str username): array|null  -> pre-fills the form / detects A2
    public function GetAccount($username) {
        $user = new UserAcc();
        return $user->FindAccount($username);
    }

    // GetAllAccounts(): array -> for the user list
    public function GetAllAccounts() {
        $user = new UserAcc();
        return $user->GetAllAccounts();
    }

    // GetProfileNames(): array  -> options for the role dropdown
    public function GetProfileNames() {
        $profile = new UserProfile();
        return $profile->GetAllProfileNames();
    }
}
