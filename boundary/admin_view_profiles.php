<?php
session_start();
require_once __DIR__ . '/../controller/SearchProfilesController.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> admin_view_profiles
class admin_view_profiles {
    private $keyword = '';

    // EnterKeyword(str keyword)
    public function EnterKeyword($keyword) {
        $this->keyword = trim($keyword);
    }

    // ClickSearch()
    public function ClickSearch() {
        // Step 4: validate the keyword
        if ($this->keyword === '') {
            $this->render('Please enter a keyword to search.', null, false);
            return;
        }

        // search(keyword): array[UserProfile]
        $controller = new SearchProfilesController();
        $profiles = $controller->search($this->keyword);

        if (count($profiles) === 0) {
            $this->DisplayNoMatchingProfiles();
        } else {
            $this->DisplaySearchResults($profiles);
        }
    }

    // DisplaySearchResults(array Profiles)
    public function DisplaySearchResults($profiles) {
        $this->render('', $profiles, true);
    }

    // DisplayNoMatchingProfiles()
    public function DisplayNoMatchingProfiles() {
        $this->render('No matching user profiles found. Please try a different keyword.', null, true);
    }

    // Step 1: the search form (also used to show results and messages)
    public function render($message, $profiles, $searched) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search User Profile</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 40px; }
    input[type=text] { padding:8px; width:280px; }
    button { padding:8px 14px; cursor:pointer; }
    table { border-collapse: collapse; min-width: 560px; margin-top:16px; }
    th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; vertical-align: top; }
    th { background: #f2f2f2; }
    .msg { color:#b00020; margin-top:14px; }
  </style>
</head>
<body>
  <h1>Search User Profile</h1>
  <form method="GET" action="admin_view_profiles.php">
    <input type="text" name="keyword" placeholder="Profile keyword" value="<?= htmlspecialchars($this->keyword) ?>">
    <button type="submit">Search</button>
  </form>

  <?php if ($message !== ''): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if ($profiles !== null): ?>
    <p><?= count($profiles) ?> profile(s) found.</p>
    <table>
      <tr><th>ID</th><th>Profile</th><th>Status</th><th>Permissions</th><th></th></tr>
      <?php foreach ($profiles as $p): ?>
      <tr>
        <td><?= (int)$p['role_id'] ?></td>
        <td><?= htmlspecialchars($p['role_name']) ?></td>
        <td><?= $p['is_active'] ? 'Active' : 'Suspended' ?></td>
        <td><?= $p['permissions'] !== null ? htmlspecialchars($p['permissions']) : 'No permissions assigned' ?></td>
        <td><a href="update_user_profile.php?profile_id=<?= (int)$p['role_id'] ?>">Edit</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <p><a href="profile_list.php">Back to profile list</a> | <a href="user_admin_home.php">Home</a></p>
</body>
</html>
<?php }
}

// ---- Flow from the sequence diagram ----
$gui = new admin_view_profiles();

if (isset($_GET['keyword'])) {
    $gui->EnterKeyword($_GET['keyword']);     // Enter keyword
    $gui->ClickSearch();                      // Click "Search"
} else {
    $gui->render('', null, false);            // Step 1: display the search form
}
