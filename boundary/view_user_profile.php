<?php
session_start();
require_once __DIR__ . '/../controller/View_Profile.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI (view profile screens)
class Admin_GUI {

    // ClickView(userId): admin selected a user in the list and clicked View
    public function ClickView($userId) {
        // ViewProfile(userId)
        $controller = new View_Profile();
        $profile = $controller->ViewProfile($userId);

        if ($profile === null) {
            $this->DisplayFail("The user account cannot be found.");      // A1
        } else {
            $this->DisplayUserProfile($profile);
        }
    }

    // DisplayUserProfile(profile)
    public function DisplayUserProfile($profile) {
        $this->render('', $profile);
    }

    // DisplayFail(message)
    public function DisplayFail($message) {
        $this->render($message, null);
    }

    private function render($error, $profile) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View User Profile</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:400px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { border:1px solid #ccc; padding:8px; text-align:left; vertical-align:top; }
    th { background:#f2f2f2; width:35%; }
    ul { margin:0; padding-left:18px; }
    .error { color:#b00020; margin-bottom:12px; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>View User Profile</h2>

    <?php if ($error !== ''): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($profile !== null): ?>
      <table>
        <tr><th>User ID</th><td><?= (int)$profile['user_id'] ?></td></tr>
        <tr><th>Username</th><td><?= htmlspecialchars($profile['username'] ?? '') ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($profile['email']) ?></td></tr>
        <tr><th>Account status</th><td><?= $profile['is_active'] ? 'Active' : 'Suspended' ?></td></tr>
        <tr><th>Profile</th><td><?= htmlspecialchars($profile['role_name']) ?></td></tr>
        <tr><th>Permissions</th><td>
          <?php if (count($profile['permissions']) === 0): ?>
            No permissions assigned
          <?php else: ?>
            <ul>
              <?php foreach ($profile['permissions'] as $perm): ?>
                <li><?= htmlspecialchars($perm) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </td></tr>
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
    $gui->ClickView($_GET['userId']);                 // ClickView(userId)
} else {
    header("Location: user_list.php");
}
