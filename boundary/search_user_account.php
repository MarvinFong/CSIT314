<?php
session_start();
require_once __DIR__ . '/../controller/Search_UserAcc_Controller.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI (search screens)
class Admin_GUI {

    // InputSearchCriteria(str criteria)
    public function InputSearchCriteria($criteria) {
        $criteria = trim($criteria);
        if ($criteria === '') {
            $this->DisplayError("Please enter a username, email or ID to search.");
            return;
        }

        // SearchAccount(criteria): userList
        $controller = new Search_UserAcc_Controller();
        $userList = $controller->SearchAccount($criteria);

        if (count($userList) === 0) {
            $this->DisplayError("No users found. Please try a different search.");   // A1
        } else {
            $this->DisplaySearchResults($userList);
        }
    }

    // DisplaySearchResults(userList)
    public function DisplaySearchResults($userList) {
        $this->render('', $userList);
    }

    // DisplayError(str msg)
    public function DisplayError($msg) {
        $this->render($msg, null);
    }

    public function render($error, $userList) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search User Account</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 40px; }
    input[type=text] { padding:8px; width:280px; }
    button { padding:8px 14px; cursor:pointer; }
    table { border-collapse: collapse; min-width: 560px; margin-top:16px; }
    th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
    th { background: #f2f2f2; }
    .error { color:#b00020; margin-top:14px; }
  </style>
</head>
<body>
  <h1>Search User Account</h1>
  <form method="GET" action="search_user_account.php">
    <input type="text" name="criteria" placeholder="Username, email or ID" value="<?= htmlspecialchars($_GET['criteria'] ?? '') ?>">
    <button type="submit">Search</button>
  </form>

  <?php if ($error !== ''): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($userList !== null): ?>
    <p><?= count($userList) ?> user(s) found.</p>
    <table>
      <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
      <?php foreach ($userList as $u): ?>
      <tr>
        <td><?= (int)$u['user_id'] ?></td>
        <td><?= htmlspecialchars($u['username'] ?? '') ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role_name']) ?></td>
        <td><?= $u['is_active'] ? 'Active' : 'Suspended' ?></td>
        <td>
          <a href="view_user_account.php?userId=<?= (int)$u['user_id'] ?>">View</a>
          | <a href="view_user_profile.php?userId=<?= (int)$u['user_id'] ?>">Profile</a>
          <?php if (!empty($u['username'])): ?>
            | <a href="update_user_account.php?username=<?= urlencode($u['username']) ?>">Update</a>
          <?php endif; ?>
          <?php if ($u['is_active']): ?>
            | <a href="suspend_user_account.php?userId=<?= (int)$u['user_id'] ?>">Suspend</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <p><a href="user_list.php">Back to user list</a> | <a href="user_admin_home.php">Home</a></p>
</body>
</html>
<?php }
}

// ---- Flow from the sequence diagram ----
$gui = new Admin_GUI();

if (isset($_GET['criteria'])) {
    $gui->InputSearchCriteria($_GET['criteria']);     // InputSearchCriteria(criteria)
} else {
    $gui->render('', null);                           // empty search page
}
