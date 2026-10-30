<?php
session_start();
require_once __DIR__ . '/../controller/Login_Controller.php';

// <<Boundary>> Login_Page
class Login_Page {

    // DisplayLogin()
    public function DisplayLogin(string $msg = ''): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:80px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:320px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input { width:100%; padding:8px; margin:6px 0 14px; box-sizing:border-box; }
    button { width:100%; padding:10px; cursor:pointer; }
    .error { color:#b00020; margin-bottom:12px; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Login</h2>
    <?php if ($msg !== ''): ?>
      <div class="error"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <label>Email</label>
      <input type="email" name="email">
      <label>Password</label>
      <input type="password" name="pw">
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
<?php }

    // Get_Input(str pw, str email)
    public function Get_Input(): array {
        return [
            'email' => trim($_POST['email'] ?? ''),
            'pw'    => $_POST['pw'] ?? '',
        ];
    }

    // DisplayFail(str msg)
    public function DisplayFail(string $msg): void {
        $this->DisplayLogin($msg);
    }

    // Redirect(str role)
    public function Redirect(string $role): void {
        $pages = [
            'User Admin'        => 'user_admin_home.php',
            'Customer'          => 'customer_home.php',
            'Interior Designer' => 'designer_home.php',
            'Platform Manager'  => 'manager_home.php',
        ];
        header("Location: " . ($pages[$role] ?? 'general_home.php'));   // new profiles use the generic home page
        exit;
    }
}

// ---- Flow from the sequence diagram ----
$page = new Login_Page();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $page->DisplayLogin();                       // 1. Display login page
    exit;
}

$input = $page->Get_Input();                     // 2. User enters email + password

if ($input['email'] === '' || $input['pw'] === '') {   // 3. Check both fields filled
    $page->DisplayFail("Please fill in both email and password");
    exit;
}

$controller = new Login_Controller();
if ($controller->FindUser($input['email'], $input['pw'])) {   // 4. Verify against DB
    $role = $controller->getRole();
    session_regenerate_id(true);
    $_SESSION['email'] = $input['email'];
    $_SESSION['role']  = $role;
    $page->Redirect($role);                      // 5a. Success -> redirect by role
} else {
    $page->DisplayFail("Invalid email or password");   // 5b. Failure
}
