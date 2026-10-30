<?php
session_start();
require_once __DIR__ . '/../controller/Edit_Profile.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI (edit profile screens)
class Admin_GUI {
    // Built-in profiles: the code checks these names, so they can't be renamed.
    // Their permissions can still be edited.
    private $protectedNames = array('User Admin', 'Customer', 'Interior Designer', 'Platform Manager');

    // DisplayEditForm(): dropdown (step 1) and, once a profile is chosen, the edit form (step 2)
    public function DisplayEditForm($profile = null, $msg = '', $type = '') {
        $controller = new Edit_Profile();
        $profiles = $controller->GetProfileList();
        $permissions = $controller->GetPermissions();
        $selectedId = $profile ? (int)$profile['role_id'] : 0;
        $locked = $profile && in_array($profile['original_name'] ?? $profile['role_name'], $this->protectedNames, true);
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update User Profile</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:380px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input[type=text], select { width:100%; padding:8px; margin:6px 0 14px; box-sizing:border-box; }
    input[readonly] { background:#eee; }
    .perm { display:block; margin:6px 0; }
    button { width:100%; padding:10px; cursor:pointer; margin-top:8px; }
    .error { color:#b00020; margin-bottom:12px; }
    .success { color:#1b7e2e; margin-bottom:12px; }
    small { color:#666; }
    hr { margin:22px 0; border:0; border-top:1px solid #ddd; }
    a.back { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Update User Profile</h2>

    <?php if ($msg !== ''): ?>
      <div class="<?= $type === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Step 1: choose a profile, click Edit -->
    <form method="GET" action="update_user_profile.php">
      <label>Select a profile</label>
      <select name="profile_id">
        <?php foreach ($profiles as $pr): ?>
          <option value="<?= (int)$pr['role_id'] ?>" <?= (int)$pr['role_id'] === $selectedId ? 'selected' : '' ?>>
            <?= htmlspecialchars($pr['role_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Edit</button>
    </form>

    <?php if ($profile): ?>
      <hr>
      <!-- Step 2: name textbox + permission checkboxes -->
      <form method="POST" action="update_user_profile.php">
        <input type="hidden" name="profile_id" value="<?= (int)$profile['role_id'] ?>">
        <label>Profile name</label>
        <input type="text" name="profile_name" value="<?= htmlspecialchars($profile['role_name']) ?>" <?= $locked ? 'readonly' : '' ?>>
        <?php if ($locked): ?><small>Built-in profile: the name cannot be changed.</small><br><br><?php endif; ?>

        <label>Permissions</label>
        <?php foreach ($permissions as $p): ?>
          <label class="perm">
            <input type="checkbox" name="permissions[]" value="<?= (int)$p['permission_id'] ?>"
              <?= in_array((int)$p['permission_id'], $profile['permission_ids'], true) ? 'checked' : '' ?>>
            <?= htmlspecialchars($p['permission_name']) ?>
          </label>
        <?php endforeach; ?>

        <button type="submit">Save</button>
      </form>
    <?php endif; ?>

    <a class="back" href="user_admin_home.php">Back to home</a>
  </div>
</body>
</html>
<?php }

    // SubmitForm(profileId, newName, permissions): admin clicks "Save" (step 3)
    public function SubmitForm($profileId, $newName, $permissions) {
        $controller = new Edit_Profile();
        $current = $controller->GetProfile($profileId);
        $permIds = array_map('intval', $permissions);

        if ($current === null) {
            $this->DisplayEditForm(null, "That profile no longer exists.", 'error');
            return;
        }

        // what the admin typed, so the form keeps their input on failure
        $entered = array(
            'role_id'        => $current['role_id'],
            'role_name'      => $newName,
            'original_name'  => $current['role_name'],
            'permission_ids' => $permIds,
        );

        // Alternative flow: empty or invalid name
        if ($newName === '') {
            $this->DisplayFail("Please enter a profile name.", $entered);
            return;
        }
        if (strlen($newName) > 50 || !preg_match('/^[A-Za-z0-9 \-]+$/', $newName)) {
            $this->DisplayFail("Profile name may only contain letters, numbers, spaces and hyphens (max 50 characters).", $entered);
            return;
        }
        // Built-in profiles keep their names
        if (in_array($current['role_name'], $this->protectedNames, true) && $newName !== $current['role_name']) {
            $entered['role_name'] = $current['role_name'];
            $this->DisplayFail("Built-in profiles cannot be renamed.", $entered);
            return;
        }

        // EditProfile(...): bool
        if ($controller->EditProfile($profileId, $newName, $permIds)) {
            $saved = $controller->GetProfile($profileId);
            $this->DisplaySuccess("Profile updated successfully.", $saved);
        } else {
            // Alternative flow: duplicate (or save failed)
            $this->DisplayFail("Profile not updated. A profile with that name may already exist.", $entered);
        }
    }

    // DisplaySuccess()
    public function DisplaySuccess($msg, $profile) {
        $this->DisplayEditForm($profile, $msg, 'success');
    }

    // DisplayFail()
    public function DisplayFail($msg, $profile) {
        $this->DisplayEditForm($profile, $msg, 'error');
    }
}

// ---- Flow from the sequence diagram ----
$gui = new Admin_GUI();
$controller = new Edit_Profile();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $perms = isset($_POST['permissions']) && is_array($_POST['permissions']) ? $_POST['permissions'] : array();
    $gui->SubmitForm($_POST['profile_id'] ?? 0, trim($_POST['profile_name'] ?? ''), $perms);
    exit;
}

if (isset($_GET['profile_id'])) {
    $profile = $controller->GetProfile($_GET['profile_id']);
    if ($profile === null) {
        $gui->DisplayEditForm(null, "That profile no longer exists.", 'error');
    } else {
        $gui->DisplayEditForm($profile);                  // DisplayEditForm()
    }
} else {
    $gui->DisplayEditForm();                              // dropdown only
}
