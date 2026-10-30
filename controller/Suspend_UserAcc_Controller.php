<?php
require_once __DIR__ . '/../entity/UserAcc.php';

// <<Control>> Suspend_UserAcc_Controller
class Suspend_UserAcc_Controller {

    // CheckUserStatus(userId): str  -> "Active", "Suspended" or "Not Found"
    public function CheckUserStatus($userId) {
        $user = new UserAcc();
        $data = $user->FindUser($userId);          // FindUser(userId)
        if ($data === null) {
            return "Not Found";
        }
        return $data['is_active'] ? "Active" : "Suspended";
    }

    // GetUserData(userId): userData | null   (for the confirmation prompt)
    public function GetUserData($userId) {
        $user = new UserAcc();
        return $user->FindUser($userId);
    }

    // SuspendAccount(userId): bool
    public function SuspendAccount($userId) {
        // Re-check at the moment of suspending (another admin may have acted in between)
        if ($this->CheckUserStatus($userId) !== "Active") {
            return false;
        }
        $user = new UserAcc();
        return $user->UpdateStatus($userId, "Suspended");   // UpdateStatus(userId, "Suspended")
    }
}
