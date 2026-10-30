<?php
require_once __DIR__ . '/../entity/UserAcc.php';
require_once __DIR__ . '/../entity/UserProfile.php';

// <<Control>> Create_UserAcc_Controller
class Create_UserAcc_Controller {

    // CreateAccount(str username, str password, str email, str role): bool
    public function CreateAccount($username, $password, $email, $role) {
        $user = new UserAcc();
        return $user->SaveAccount($username, $password, $email, $role);
    }

    // GetProfileNames(): array  -> options for the role dropdown
    public function GetProfileNames() {
        $profile = new UserProfile();
        return $profile->GetAllProfileNames();
    }
}
