<?php
session_start();
require_once __DIR__ . '/../controller/Create_UserAcc_Controller.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI
class Admin_GUI {
    private $roles = array();

    public function __construct() {
        $controller = new Create_UserAcc_Controller();
        $this->roles = $controller->GetProfileNames();   // profiles come from the database
    }

    // DisplayForm()
    public function DisplayForm($msg = '', $type = '') { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create User Account</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:340px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input, select { width:100%; padding:8px; margin:6px 0 14px; box-sizing:border-box; }
    button { width:100%; padding:10px; cursor:pointer; }
    .error { color:#b00020; margin-bottom:12px; }
    .success { color:#1b7e2e; margin-bottom:12px; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Create User Account</h2>
    <?php if ($msg !== ''): ?>
      <div class="<?= $type === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" action="create_user_account.php">
      <label>Username</label>
      <input type="text" name="username">
      <label>Email</label>
      <input type="text" name="email">
      <label>Password</label>
      <input type="password" name="password">
      <label>Role</label>
      <select name="role">
        <?php foreach ($this->roles as $r): ?>
          <option value="<?= htmlspecialchars($r) ?>"><?= htmlspecialchars($r) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Create Account</button>
    </form>
    <a href="user_admin_home.php">Back to home</a>
  </div>
</body>
</html>
<?php }

    // SubmitForm(str username, str password, str email, str role)
    public function SubmitForm($username, $password, $email, $role) {
        // Step 3 / Alternative flow A1: missing or badly formatted data
        if ($username === '' || $password === '' || $email === '' || $role === '') {
            $this->DisplayFail("Please fill in all required fields.");
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->DisplayFail("Please enter a valid email address.");
            return;
        }
        if (strlen($password) < 8) {
            $this->DisplayFail("Password must be at least 8 characters.");
            return;
        }
        if (!in_array($role, $this->roles, true)) {
            $this->DisplayFail("Please select a valid role.");
            return;
        }

        // CreateAccount(...): bool
        $controller = new Create_UserAcc_Controller();
        if ($controller->CreateAccount($username, $password, $email, $role)) {
            $this->DisplaySuccess("Account created successfully for " . $email . ".");
        } else {
            // Alternative flow A2: duplicate (or save failed)
            $this->DisplayFail("Account not created. The email or username may already exist.");
        }
    }

    // DisplaySuccess(str msg)
    public function DisplaySuccess($msg) {
        $this->DisplayForm($msg, 'success');
    }

    // DisplayFail(str msg)
    public function DisplayFail($msg) {
        $this->DisplayForm($msg, 'error');
    }
}

// ---- Flow from the sequence diagram ----
$gui = new Admin_GUI();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $gui->DisplayForm();                         // Click "Create User Account" -> DisplayForm()
    exit;
}

$gui->SubmitForm(                                // SubmitForm(username, password, email, role)
    trim($_POST['username'] ?? ''),
    $_POST['password'] ?? '',
    trim($_POST['email'] ?? ''),
    $_POST['role'] ?? ''
);
