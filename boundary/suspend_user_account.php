<?php
session_start();
require_once __DIR__ . '/../controller/Suspend_UserAcc_Controller.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI (suspend screens)
class Admin_GUI {

    // SearchUser(userId): admin selected a user from the list and clicked "Suspend"
    public function SearchUser($userId) {
        $controller = new Suspend_UserAcc_Controller();

        // CheckUserStatus(userId): str
        $status = $controller->CheckUserStatus($userId);

        // Alternative flow A1: not found or already suspended
        if ($status !== "Active") {
            $this->DisplayError("User cannot be suspended. The account does not exist or is already suspended.");
            return;
        }

        $userData = $controller->GetUserData($userId);

        // Extra safety: an admin should not lock themselves out
        if ($userData['email'] === ($_SESSION['email'] ?? '')) {
            $this->DisplayError("You cannot suspend your own account.");
            return;
        }

        $this->DisplayConfirmPrompt($userData);   // Step 2
    }

    // DisplayConfirmPrompt(userData)
    public function DisplayConfirmPrompt($userData) {
        $this->render('confirm', '', $userData);
    }

    // SubmitSuspension(userId): admin confirmed (Step 3)
    public function SubmitSuspension($userId) {
        $controller = new Suspend_UserAcc_Controller();

        $userData = $controller->GetUserData($userId);
        if ($userData !== null && $userData['email'] === ($_SESSION['email'] ?? '')) {
            $this->DisplayError("You cannot suspend your own account.");
            return;
        }

        // SuspendAccount(userId): bool
        if ($controller->SuspendAccount($userId)) {
            $this->DisplaySuccess("The account has been suspended.");
        } else {
            $this->DisplayError("The account could not be suspended. It may already be suspended or no longer exist.");
        }
    }

    // DisplaySuccess(str msg)
    public function DisplaySuccess($msg) {
        $this->render('success', $msg, null);
    }

    // DisplayError(str msg)
    public function DisplayError($msg) {
        $this->render('error', $msg, null);
    }

    private function render($mode, $msg, $userData) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Suspend User Account</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:380px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    button { width:100%; padding:10px; cursor:pointer; margin-top:8px; }
    .error { color:#b00020; margin-bottom:12px; }
    .success { color:#1b7e2e; margin-bottom:12px; }
    table { width:100%; border-collapse:collapse; margin:10px 0; }
    th, td { border:1px solid #ccc; padding:8px; text-align:left; }
    th { background:#f2f2f2; width:35%; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Suspend User Account</h2>

    <?php if ($mode === 'confirm'): ?>
      <p>Are you sure you want to suspend this account? The user will no longer be able to log in.</p>
      <table>
        <tr><th>User ID</th><td><?= (int)$userData['user_id'] ?></td></tr>
        <tr><th>Username</th><td><?= htmlspecialchars($userData['username'] ?? '') ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($userData['email']) ?></td></tr>
        <tr><th>Role</th><td><?= htmlspecialchars($userData['role_name']) ?></td></tr>
      </table>
      <form method="POST" action="suspend_user_account.php">
        <input type="hidden" name="userId" value="<?= (int)$userData['user_id'] ?>">
        <button type="submit" name="action" value="confirm">Confirm Suspension</button>
        <button type="submit" name="action" value="cancel">Cancel</button>
      </form>
    <?php else: ?>
      <div class="<?= $mode === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
      <a href="user_list.php">Back to user list</a>
    <?php endif; ?>
  </div>
</body>
</html>
<?php }
}

// ---- Flow from the sequence diagram ----
$gui = new Admin_GUI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Alternative flow A2: admin cancels at the confirmation prompt
    if (($_POST['action'] ?? '') !== 'confirm') {
        header("Location: user_list.php");
        exit;
    }
    $gui->SubmitSuspension($_POST['userId'] ?? '');     // SubmitSuspension(userId)
    exit;
}

if (isset($_GET['userId'])) {
    $gui->SearchUser($_GET['userId']);                  // SearchUser(userId)
} else {
    header("Location: user_list.php");
}
