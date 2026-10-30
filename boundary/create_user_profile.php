<?php
session_start();
require_once __DIR__ . '/../controller/Create_Profile_Ctrl.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI (create profile screens)
class Admin_GUI {

    // DisplayCreateForm(): textbox for the profile name + checkboxes for permissions
    public function DisplayCreateForm($msg = '', $type = '', $oldName = '', $oldPerms = array()) {
        $controller = new Create_Profile_Ctrl();
        $permissions = $controller->GetPermissions();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create User Profile</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:360px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input[type=text] { width:100%; padding:8px; margin:6px 0 14px; box-sizing:border-box; }
    .perm { display:block; margin:6px 0; }
    button { width:100%; padding:10px; cursor:pointer; margin-top:14px; }
    .error { color:#b00020; margin-bottom:12px; }
    .success { color:#1b7e2e; margin-bottom:12px; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Create User Profile</h2>
    <?php if ($msg !== ''): ?>
      <div class="<?= $type === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" action="create_user_profile.php">
      <label>Profile name</label>
      <input type="text" name="profile_name" value="<?= htmlspecialchars($oldName) ?>">

      <label>Permissions</label>
      <?php foreach ($permissions as $p): ?>
        <label class="perm">
          <input type="checkbox" name="permissions[]" value="<?= (int)$p['permission_id'] ?>"
            <?= in_array((string)$p['permission_id'], array_map('strval', $oldPerms), true) ? 'checked' : '' ?>>
          <?= htmlspecialchars($p['permission_name']) ?>
        </label>
      <?php endforeach; ?>

      <button type="submit">Create</button>
    </form>
    <a href="user_admin_home.php">Back to home</a>
  </div>
</body>
</html>
<?php }

    // SubmitForm(str profileName, array permissions): admin clicks "Create" (step 4)
    public function SubmitForm($profileName, $permissions) {
        // Alternative flow: empty or invalid name
        if ($profileName === '') {
            $this->DisplayFail("Please enter a profile name.", $profileName, $permissions);
            return;
        }
        if (strlen($profileName) > 50 || !preg_match('/^[A-Za-z0-9 \-]+$/', $profileName)) {
            $this->DisplayFail("Profile name may only contain letters, numbers, spaces and hyphens (max 50 characters).", $profileName, $permissions);
            return;
        }

        // createProfile(...): bool
        $controller = new Create_Profile_Ctrl();
        if ($controller->createProfile($profileName, $permissions)) {
            $this->DisplaySuccess("Profile \"" . $profileName . "\" created successfully.");
        } else {
            // Alternative flow: duplicate (or save failed)
            $this->DisplayFail("Profile not created. A profile with that name may already exist.", $profileName, $permissions);
        }
    }

    // DisplaySuccess()
    public function DisplaySuccess($msg) {
        $this->DisplayCreateForm($msg, 'success');
    }

    // DisplayFail()
    public function DisplayFail($msg, $oldName = '', $oldPerms = array()) {
        $this->DisplayCreateForm($msg, 'error', $oldName, $oldPerms);
    }
}

// ---- Flow from the sequence diagram ----
$gui = new Admin_GUI();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $gui->DisplayCreateForm();                       // DisplayCreateForm()
    exit;
}

$perms = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : array();
$gui->SubmitForm(trim($_POST['profile_name'] ?? ''), $perms);
