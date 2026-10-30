<?php
session_start();
require_once __DIR__ . '/../controller/IDCreate_CI_Controller.php';

// Precondition: Interior Designer is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Interior Designer') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> ID_CreateCI
class ID_CreateCI {

    // DisplayForm(): the company information form (step 2)
    public function DisplayForm($msg = '', $type = '', $old = array()) {
        $name    = $old['companyname'] ?? '';
        $address = $old['address'] ?? '';
        $contact = $old['contact_no'] ?? '';
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Company Information</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:360px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input { width:100%; padding:8px; margin:6px 0 14px; box-sizing:border-box; }
    button { width:100%; padding:10px; cursor:pointer; }
    .error { color:#b00020; margin-bottom:12px; }
    .success { color:#1b7e2e; margin-bottom:12px; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Create Company Information</h2>
    <?php if ($msg !== ''): ?>
      <div class="<?= $type === 'success' ? 'success' : 'error' ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" action="id_create_ci.php">
      <label>Company name</label>
      <input type="text" name="companyname" value="<?= htmlspecialchars($name) ?>">
      <label>Address</label>
      <input type="text" name="address" value="<?= htmlspecialchars($address) ?>">
      <label>Contact number</label>
      <input type="text" name="contact_no" value="<?= htmlspecialchars($contact) ?>">
      <button type="submit">Submit</button>
    </form>
    <a href="designer_home.php">Back to home</a>
  </div>
</body>
</html>
<?php }

    // SubmitForm(str companyname, str address, str contact_no)   (step 4)
    public function SubmitForm($companyname, $address, $contact_no) {
        $old = array('companyname' => $companyname, 'address' => $address, 'contact_no' => $contact_no);

        // Check the details before saving
        if ($companyname === '' || $address === '' || $contact_no === '') {
            $this->DisplayFail("Please fill in the company name, address and contact number.", $old);
            return;
        }
        if (strlen($companyname) > 100 || strlen($address) > 255) {
            $this->DisplayFail("Company name (max 100) or address (max 255) is too long.", $old);
            return;
        }
        if (!preg_match('/^[0-9]{7,15}$/', $contact_no)) {
            $this->DisplayFail("Contact number must be 7 to 15 digits.", $old);
            return;
        }

        // CreateCompany(...): bool
        $controller = new IDCreate_CI_Controller();
        if ($controller->CreateCompany($companyname, $address, $contact_no, $_SESSION['email'])) {
            $this->DisplaySuccess("Company created successfully");
        } else {
            $this->DisplayFail("Failed to create company", $old);     // alternative flow
        }
    }

    // DisplaySuccess(str msg)
    public function DisplaySuccess($msg) {
        $this->DisplayForm($msg, 'success');
    }

    // DisplayFail(str msg)
    public function DisplayFail($msg, $old = array()) {
        $this->DisplayForm($msg, 'error', $old);
    }
}

// ---- Flow from the sequence diagram ----
$gui = new ID_CreateCI();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $gui->DisplayForm();                                   // Click Create Company Information -> DisplayForm()
    exit;
}

$gui->SubmitForm(                                          // SubmitForm(companyname, address, contact_no)
    trim($_POST['companyname'] ?? ''),
    trim($_POST['address'] ?? ''),
    trim($_POST['contact_no'] ?? '')
);
