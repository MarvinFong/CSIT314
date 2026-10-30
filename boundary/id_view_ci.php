<?php
session_start();
require_once __DIR__ . '/../controller/CompanySearchController.php';

// Precondition: Interior Designer is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Interior Designer') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> InteriorDesigner_GUI
class InteriorDesigner_GUI {
    public $asJson = false;     // true when answering the search bar's requests

    // DisplaySearchPage(): search bar + the list of every company
    public function DisplaySearchPage() { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Company Information</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:480px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input { width:100%; padding:10px; box-sizing:border-box; }
    #count { color:#666; font-size:13px; margin:12px 0 6px; }
    #list { border:1px solid #ddd; border-radius:4px; max-height:360px; overflow-y:auto; }
    #list a { display:block; padding:10px 12px; text-decoration:none; color:#000; border-bottom:1px solid #eee; }
    #list a:last-child { border-bottom:0; }
    #list a:hover { background:#f2f2f2; }
    #list small { display:block; color:#666; margin-top:2px; }
    #list .none { padding:12px; color:#b00020; }
    .back { display:block; margin-top:20px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>View Company Information</h2>
    <input type="text" id="search" placeholder="Search by company name" autocomplete="off">
    <div id="count"></div>
    <div id="list"></div>
    <a class="back" href="designer_home.php">Back to home</a>
  </div>

<script>
var input = document.getElementById('search');
var list  = document.getElementById('list');
var count = document.getElementById('count');
var timer = null;

function load(term) {
  fetch('id_view_ci.php?suggest=' + encodeURIComponent(term))
    .then(function (r) { return r.json(); })
    .then(function (data) {
      list.innerHTML = '';
      if (data.results.length === 0) {
        count.textContent = '';
        var d = document.createElement('div');
        d.className = 'none';
        d.textContent = data.message || 'No matching results';
        list.appendChild(d);
        return;
      }
      count.textContent = data.results.length + ' company(ies)';
      data.results.forEach(function (c) {
        var a = document.createElement('a');
        a.href = 'id_view_ci.php?company_id=' + encodeURIComponent(c.company_id);
        a.textContent = c.company_name;
        var s = document.createElement('small');
        s.textContent = c.address;
        a.appendChild(s);
        list.appendChild(a);
      });
    });
}

input.addEventListener('input', function () {
  clearTimeout(timer);
  timer = setTimeout(function () { load(input.value.trim()); }, 200);
});

load('');   // show every company when the page opens
</script>
</body>
</html>
<?php }

    // EnterSearchName(NameOfCoy): the list on the page; an empty name returns every company
    public function EnterSearchName($nameOfCoy) {
        $nameOfCoy = trim($nameOfCoy);

        // CompanySearch(companyName): List<CompanyInfo>
        $controller = new CompanySearchController();
        $matches = $controller->CompanySearch($nameOfCoy);

        if (count($matches) === 0) {
            $this->DisplayFail($nameOfCoy === '' ? "No companies have been created yet." : "No matching results");
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(array('results' => $matches));
    }

    // Designer clicked a company in the list -> company information page
    public function SelectCompany($companyId) {
        $controller = new CompanySearchController();
        $info = $controller->GetCompanyInfo($companyId);      // getCompanyInfo()
        if ($info === null) {
            $this->DisplayFail("Company not found.");
        } else {
            $this->DisplayInfo($info);
        }
    }

    // DisplayInfo(companyInfo)
    public function DisplayInfo($info) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Company Information</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding-top:60px; }
    .box { background:#fff; padding:30px; border-radius:8px; width:420px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { border:1px solid #ccc; padding:8px; text-align:left; vertical-align:top; }
    th { background:#f2f2f2; width:35%; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Company Information</h2>
    <table>
      <tr><th>Company name</th><td><?= htmlspecialchars($info['company_name']) ?></td></tr>
      <tr><th>Address</th><td><?= htmlspecialchars($info['address']) ?></td></tr>
      <tr><th>Contact number</th><td><?= htmlspecialchars($info['contact_no']) ?></td></tr>
      <tr><th>Added on</th><td><?= htmlspecialchars($info['created_at']) ?></td></tr>
    </table>
    <form method="POST" action="id_delete_ci.php" style="margin-top:18px"
          onsubmit="return confirm('Delete this company information? This cannot be undone.');">
      <input type="hidden" name="infoID" value="<?= (int)$info['company_id'] ?>">
      <button type="submit" style="width:100%;padding:10px;cursor:pointer;color:#b00020">Delete</button>
    </form>
    <a href="id_view_ci.php">Back to company list</a>
    <a href="designer_home.php">Back to home</a>
  </div>
</body>
</html>
<?php }

    // DisplayFail(): "No matching results" in the list, or an error page
    public function DisplayFail($msg) {
        if ($this->asJson) {
            header('Content-Type: application/json');
            echo json_encode(array('results' => array(), 'message' => $msg));
            return;
        }
        ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Company Information</title></head>
<body style="font-family:Arial;padding:40px">
  <p style="color:#b00020"><?= htmlspecialchars($msg) ?></p>
  <p><a href="id_view_ci.php">Back to company list</a></p>
</body>
</html>
<?php }
}

// ---- Flow ----
$gui = new InteriorDesigner_GUI();

if (isset($_GET['suggest'])) {
    $gui->asJson = true;
    $gui->EnterSearchName($_GET['suggest']);      // EnterSearchName(NameOfCoy)
} elseif (isset($_GET['company_id'])) {
    $gui->SelectCompany($_GET['company_id']);     // click a company -> DisplayInfo(companyInfo)
} else {
    $gui->DisplaySearchPage();                    // search bar + full company list
}
