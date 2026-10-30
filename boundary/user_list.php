<?php
session_start();
require_once __DIR__ . '/../controller/Update_UserAcc_Controller.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

$controller = new Update_UserAcc_Controller();
$accounts = $controller->GetAllAccounts();

$msg = '';
if (isset($_GET['error']) && $_GET['error'] === 'notfound') {
    $msg = "That account no longer exists. Please pick another from the list.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Accounts</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 40px; }
    table { border-collapse: collapse; min-width: 560px; }
    th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
    th { background: #f2f2f2; }
    .error { color:#b00020; margin-bottom:12px; }
  </style>
</head>
<body>
  <h1>User Accounts</h1>
  <?php if ($msg !== ''): ?><div class="error"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <form method="GET" action="search_user_account.php" style="margin-bottom:16px">
    <input type="text" name="criteria" placeholder="Search by username, email or ID">
    <button type="submit">Search</button>
  </form>
  <table>
    <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr>
    <?php foreach ($accounts as $a): ?>
    <tr>
      <td><?= (int)$a['user_id'] ?></td>
      <td><?= htmlspecialchars($a['username'] ?? '') ?></td>
      <td><?= htmlspecialchars($a['email']) ?></td>
      <td><?= htmlspecialchars($a['role_name']) ?></td>
      <td><?= $a['is_active'] ? 'Active' : 'Suspended' ?></td>
      <td>
        <form method="GET" action="view_user_account.php" style="margin:0 0 4px 0">
          <input type="hidden" name="userId" value="<?= (int)$a['user_id'] ?>">
          <button type="submit">View</button>
        </form>
        <form method="GET" action="view_user_profile.php" style="margin:0 0 4px 0">
          <input type="hidden" name="userId" value="<?= (int)$a['user_id'] ?>">
          <button type="submit">View Profile</button>
        </form>
        <?php if ($a['is_active']): ?>
          <form method="GET" action="suspend_user_account.php" style="margin:0 0 4px 0">
            <input type="hidden" name="userId" value="<?= (int)$a['user_id'] ?>">
            <button type="submit">Suspend</button>
          </form>
        <?php endif; ?>
        <?php if (!empty($a['username'])): ?>
          <form method="GET" action="update_user_account.php" style="margin:0">
            <input type="hidden" name="username" value="<?= htmlspecialchars($a['username']) ?>">
            <button type="submit">Update</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p><a href="user_admin_home.php">Back to home</a></p>
</body>
</html>
