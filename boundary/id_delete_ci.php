<?php
session_start();
require_once __DIR__ . '/../controller/Delete_CompanyInfo.php';

// Precondition: Interior Designer is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Interior Designer') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> DesignGUI
class DesignGUI {

    // ClickDelete(infoID): the designer selected a company and clicked Delete
    public function ClickDelete($infoID) {
        // DeleteCompanyInfo(infoID)
        $controller = new Delete_CompanyInfo();
        $result = $controller->DeleteCompanyInfo($infoID);

        if ($result === "success") {
            $this->DisplaySuccess("The company information has been deleted.");
        } elseif ($result === "not_found") {
            $this->DisplayFail("The company information could not be found. It may already have been deleted.");   // A1
        } else {
            $this->DisplayFail("Deletion failed. The company information has not been deleted.");                 // A2
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
  <title>Delete Company Information</title>
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
    <h2>Delete Company Information</h2>
    <div class="<?= $type === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <a href="id_view_ci.php">Back to company list</a>
  </div>
</body>
</html>
<?php }
}

// ---- Flow from the sequence diagram ----
$gui = new DesignGUI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gui->ClickDelete($_POST['infoID'] ?? 0);       // ClickDelete(infoID)
} else {
    header("Location: id_view_ci.php");
}
