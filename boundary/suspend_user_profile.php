<?php
session_start();
require_once __DIR__ . '/../controller/Suspend_Profile.php';

// Precondition: User Admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'User Admin') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> Admin_GUI (suspend profile screens)
class Admin_GUI {

    // ClickSuspend(profileId)
    public function ClickSuspend($profileId) {
        $controller = new Suspend_Profile();

        // Extra safety: an admin must not suspend the profile they are logged in with
        $detail = $controller->GetProfileDetail($profileId);
        if ($detail !== null && $detail['role_name'] === ($_SESSION['role'] ?? '')) {
            $this->DisplayFail("You cannot suspend your own profile.");
            return;
        }

        // SuspendProfile(profileId)
        $result = $controller->SuspendProfile($profileId);

        if ($result === "success") {
            $this->DisplaySuccess("The user profile has been suspended. Users with this profile can no longer log in.");
        } elseif ($result === "not_found") {
            $this->DisplayFail("The user profile cannot be found.");              // A1
        } elseif ($result === "already_suspended") {
            $this->DisplayFail("This user profile is already suspended.");        // A2
        } else {
            $this->DisplayFail("The user profile could not be suspended. Please try again.");
        }
    }

    // DisplaySuccess()
    public function DisplaySuccess($msg) {
        $this->render('success', $msg);
    }

    // DisplayFail(message)
    public function DisplayFail($message) {
        $this->render('error', $message);
    }

    private function render($type, $msg) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Suspend User Profile</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:380px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    .error { color:#b00020; }
    .success { color:#1b7e2e; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Suspend User Profile</h2>
    <div class="<?= $type === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <a href="profile_list.php">Back to profile list</a>
  </div>
</body>
</html>
<?php }
}

// ---- Flow from the sequence diagram ----
$gui = new Admin_GUI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gui->ClickSuspend($_POST['profileId'] ?? 0);        // ClickSuspend(profileId)
} else {
    header("Location: profile_list.php");
}
