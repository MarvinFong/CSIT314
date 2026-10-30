<?php
session_start();
require_once __DIR__ . '/../controller/View_UserAcc_Controller.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI (view screens)
class Admin_GUI {

    // SearchUser(userId)
    public function SearchUser($userId) {
        $userId = trim($userId);
        if ($userId === '') {
            $this->DisplayError("Please enter a username or ID.");
            return;
        }

        // GetUserDetails(userId): userData
        $controller = new View_UserAcc_Controller();
        $userData = $controller->GetUserDetails($userId);

        if ($userData === null) {
            $this->DisplayError("User not found. Please check the username or ID and try again.");   // A1
        } else {
            $this->DisplayUserDetails($userData);
        }
    }

    // DisplayUserDetails(userData)
    public function DisplayUserDetails($userData) {
        $this->render('', $userData);
    }

    // DisplayError(str msg)
    public function DisplayError($msg) {
        $this->render($msg, null);
    }

    // Shared page layout: search box + either an error or the user's details
    public function render($error, $userData, $searched = true) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View User Account</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:380px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input { width:100%; padding:8px; margin:6px 0 14px; box-sizing:border-box; }
    button { width:100%; padding:10px; cursor:pointer; }
    .error { color:#b00020; margin-bottom:12px; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { border:1px solid #ccc; padding:8px; text-align:left; }
    th { background:#f2f2f2; width:35%; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>View User Account</h2>
    <form method="GET" action="view_user_account.php">
      <label>Search by username or ID</label>
      <input type="text" name="userId" value="<?= htmlspecialchars($_GET['userId'] ?? '') ?>">
      <button type="submit">Search</button>
    </form>

    <?php if ($error !== ''): ?>
      <div class="error" style="margin-top:14px"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($userData !== null): ?>
      <table>
        <tr><th>User ID</th><td><?= (int)$userData['user_id'] ?></td></tr>
        <tr><th>Username</th><td><?= htmlspecialchars($userData['username'] ?? '') ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($userData['email']) ?></td></tr>
        <tr><th>Role / rights</th><td><?= htmlspecialchars($userData['role_name']) ?></td></tr>
        <tr><th>Status</th><td><?= $userData['is_active'] ? 'Active' : 'Suspended' ?></td></tr>
      </table>
    <?php endif; ?>

    <a href="user_list.php">Back to user list</a>
  </div>
</body>
</html>
<?php }
}

// ---- Flow from the sequence diagram ----
$gui = new Admin_GUI();

if (isset($_GET['userId'])) {
    $gui->SearchUser($_GET['userId']);       // SearchUser(userId)
} else {
    $gui->render('', null);                  // empty search page
}
