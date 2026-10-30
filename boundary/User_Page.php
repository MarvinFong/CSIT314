<?php
// <<Boundary>> User_Page
// Shared page shown to all four roles once logged in.
class User_Page {

    // DisplayPage()
    public function DisplayPage($title) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
</head>
<body style="font-family:Arial;padding:40px">
  <h1><?= htmlspecialchars($title) ?></h1>
  <p>Logged in as <?= htmlspecialchars($_SESSION['email']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</p>

  <?php if ($_SESSION['role'] === 'User Admin'): ?>
    <p><a href="create_user_account.php">Create User Account</a></p>
    <p><a href="user_list.php">View / Update User Accounts</a></p>
    <p><a href="create_user_profile.php">Create User Profile</a></p>
    <p><a href="update_user_profile.php">Update User Profile</a></p>
    <p><a href="profile_list.php">Suspend User Profile</a></p>
    <p><a href="admin_view_profiles.php">Search User Profile</a></p>
  <?php endif; ?>

  <?php if ($_SESSION['role'] === 'Interior Designer'): ?>
    <p><a href="id_create_ci.php">Create Company Information</a></p>
    <p><a href="id_view_ci.php">View Company Information</a></p>
  <?php endif; ?>

  <!-- "Click Logout" -->
  <form method="POST" action="logout.php">
    <button type="submit">Logout</button>
  </form>
</body>
</html>
<?php }

    // RedirectLogin()
    public function RedirectLogin() {
        header("Location: login.php");
        exit;
    }
}
