<?php
session_start();
require_once __DIR__ . '/../controller/Update_UserAcc_Controller.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI (update screens)
class Admin_GUI {
    private $roles = array();

    public function __construct() {
        $controller = new Update_UserAcc_Controller();
        $this->roles = $controller->GetProfileNames();   // profiles come from the database
    }

    // DisplayUpdateForm(): form pre-filled with the account's existing details
    public function DisplayUpdateForm($account, $msg = '', $type = '') { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update User Account</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:340px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input, select { width:100%; padding:8px; margin:6px 0 14px; box-sizing:border-box; }
    input[readonly] { background:#eee; }
    button { width:100%; padding:10px; cursor:pointer; }
    .error { color:#b00020; margin-bottom:12px; }
    .success { color:#1b7e2e; margin-bottom:12px; }
    small { color:#666; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Update User Account</h2>
    <?php if ($msg !== ''): ?>
      <div class="<?= $type === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" action="update_user_account.php">
      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($account['username']) ?>" readonly>
      <label>Email</label>
      <input type="text" name="new_email" value="<?= htmlspecialchars($account['email']) ?>">
      <label>Role</label>
      <select name="new_role">
        <?php foreach ($this->roles as $r): ?>
          <option value="<?= htmlspecialchars($r) ?>" <?= $r === $account['role_name'] ? 'selected' : '' ?>><?= htmlspecialchars($r) ?></option>
        <?php endforeach; ?>
      </select>
      <label>New Password</label>
      <input type="password" name="new_password">
      <small>Leave blank to keep the current password.</small>
      <br><br>
      <button type="submit">Save Changes</button>
    </form>
    <a href="user_list.php">Back to user list</a>
  </div>
</body>
</html>
<?php }

    // SubmitUpdate(str username, str new_email, str new_role, str new_password)
    public function SubmitUpdate($username, $new_email, $new_role, $new_password) {
        $controller = new Update_UserAcc_Controller();
        $entered = array('username' => $username, 'email' => $new_email, 'role_name' => $new_role);

        // Alternative flow A2: account was deleted before the update
        if ($controller->GetAccount($username) === null) {
            header("Location: user_list.php?error=notfound");
            exit;
        }

        // Step 3 / Alternative flow A1: missing or badly formatted data
        if ($new_email === '' || $new_role === '') {
            $this->DisplayFail("Please fill in all required fields.", $entered);
            return;
        }
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $this->DisplayFail("Please enter a valid email address.", $entered);
            return;
        }
        if ($new_password !== '' && strlen($new_password) < 8) {
            $this->DisplayFail("Password must be at least 8 characters.", $entered);
            return;
        }
        if (!in_array($new_role, $this->roles, true)) {
            $this->DisplayFail("Please select a valid role.", $entered);
            return;
        }

        // UpdateAccount(...): bool
        if ($controller->UpdateAccount($username, $new_email, $new_role, $new_password)) {
            $this->DisplaySuccess("Account updated successfully.", $entered);
        } else {
            $this->DisplayFail("Account not updated. The email may already be used by another account.", $entered);
        }
    }

    // DisplaySuccess(str msg)
    public function DisplaySuccess($msg, $account) {
        $this->DisplayUpdateForm($account, $msg, 'success');
    }

    // DisplayFail(str msg)
    public function DisplayFail($msg, $account) {
        $this->DisplayUpdateForm($account, $msg, 'error');
    }
}

// ---- Flow from the sequence diagram ----
$gui = new Admin_GUI();
$controller = new Update_UserAcc_Controller();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Admin selected an account and clicked "Update" -> DisplayUpdateForm()
    $username = trim($_GET['username'] ?? '');
    $account = $controller->GetAccount($username);
    if ($account === null) {
        header("Location: user_list.php?error=notfound");   // A2
        exit;
    }
    $gui->DisplayUpdateForm($account);
    exit;
}

$gui->SubmitUpdate(
    trim($_POST['username'] ?? ''),
    trim($_POST['new_email'] ?? ''),
    $_POST['new_role'] ?? '',
    $_POST['new_password'] ?? ''
);
