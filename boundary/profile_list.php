<?php
session_start();
require_once __DIR__ . '/../controller/Suspend_Profile.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

$controller = new Suspend_Profile();
$profiles = $controller->GetProfileList();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Profiles</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 40px; }
    table { border-collapse: collapse; min-width: 420px; }
    th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
    th { background: #f2f2f2; }
  </style>
</head>
<body>
  <h1>User Profiles</h1>
  <form method="GET" action="admin_view_profiles.php" style="margin-bottom:16px">
    <input type="text" name="keyword" placeholder="Search profiles by keyword">
    <button type="submit">Search</button>
  </form>
  <table>
    <tr><th>ID</th><th>Profile</th><th>Status</th><th></th></tr>
    <?php foreach ($profiles as $p): ?>
    <tr>
      <td><?= (int)$p['role_id'] ?></td>
      <td><?= htmlspecialchars($p['role_name']) ?></td>
      <td><?= $p['is_active'] ? 'Active' : 'Suspended' ?></td>
      <td>
        <?php if ($p['is_active']): ?>
          <form method="POST" action="suspend_user_profile.php" style="margin:0">
            <input type="hidden" name="profileId" value="<?= (int)$p['role_id'] ?>">
            <button type="submit">Suspend</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p><a href="user_admin_home.php">Back to home</a></p>
</body>
</html>
