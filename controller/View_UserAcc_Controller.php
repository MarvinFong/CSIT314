<?php
require_once __DIR__ . '/../entity/UserAcc.php';

// <<Control>> View_UserAcc_Controller
class View_UserAcc_Controller {

    // GetUserDetails(userId): userData   (null when not found)
    public function GetUserDetails($userId) {
        $user = new UserAcc();
        return $user->FindUser($userId);
    }
}
