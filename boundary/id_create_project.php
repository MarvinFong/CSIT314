<?php
session_start();
require_once __DIR__ . '/../controller/CreateProject_controller.php';

// Precondition: Interior Designer is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Interior Designer') {
    header("Location: login.php");
    exit;
}

// <<Boundary>> ID_GUI
class ID_GUI {
    private $statuses = array('New', 'Ongoing');
    private $maxImages = 10;
    private $maxBytes = 2097152;   // 2 MB per image

    // displayForm(): the project form (step 2)
    public function displayForm($msg = '', $old = array()) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Project</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f2f2f2; display:flex; justify-content:center; padding:50px 0; }
    .box { background:#fff; padding:30px; border-radius:8px; width:420px; box-shadow:0 2px 8px rgba(0,0,0,.15); }
    input[type=text], input[type=date], select, textarea { width:100%; padding:8px; margin:6px 0 14px; box-sizing:border-box; font-family:inherit; }
    textarea { height:110px; }
    button { width:100%; padding:10px; cursor:pointer; }
    .error { color:#b00020; margin-bottom:12px; }
    small { color:#666; }
    a { display:block; margin-top:14px; text-align:center; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Create Project</h2>
    <?php if ($msg !== ''): ?>
      <div class="error"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" action="id_create_project.php" enctype="multipart/form-data">
      <label>Title</label>
      <input type="text" name="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>">

      <label>Date</label>
      <input type="date" name="project_date" value="<?= htmlspecialchars($old['project_date'] ?? '') ?>">

      <label>Description</label>
      <textarea name="description"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>

      <label>Status</label>
      <select name="status">
        <?php foreach ($this->statuses as $st): ?>
          <option value="<?= $st ?>" <?= ($old['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>

      <label>Pictures</label>
      <input type="file" name="images[]" accept="image/*" multiple>
      <small>Up to <?= $this->maxImages ?> images (JPG, PNG, GIF or WebP, max 2 MB each).</small>
      <br><br>

      <button type="submit">Save</button>
    </form>
    <a href="id_portfolio.php">Back to portfolio</a>
  </div>
</body>
</html>
<?php }

    // submitProject(): the designer clicks Save (step 4)
    public function submitProject() {
        // If the upload was bigger than the server allows, PHP discards everything
        if (empty($_POST) && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH'])) {
            $this->displayFail("The upload was too large. Please use fewer or smaller images.");
            return;
        }

        $old = array(
            'title'        => trim($_POST['title'] ?? ''),
            'project_date' => trim($_POST['project_date'] ?? ''),
            'description'  => trim($_POST['description'] ?? ''),
            'status'       => $_POST['status'] ?? '',
        );
        $files = $this->collectImages();

        // Alternative flow A1: any required field or the images are missing
        if ($old['title'] === '' || $old['project_date'] === '' || $old['description'] === ''
            || $old['status'] === '' || count($files) === 0) {
            $this->displayFail("Please fill in the title, date, description and status, and add at least one image. The project was not saved.", $old);
            return;
        }

        // Format checks
        if (strlen($old['title']) > 100) {
            $this->displayFail("The title must be 100 characters or fewer.", $old);
            return;
        }
        if (strlen($old['description']) > 5000) {
            $this->displayFail("The description must be 5000 characters or fewer.", $old);
            return;
        }
        $d = DateTime::createFromFormat('Y-m-d', $old['project_date']);
        if (!$d || $d->format('Y-m-d') !== $old['project_date']) {
            $this->displayFail("Please enter a valid date.", $old);
            return;
        }
        if (!in_array($old['status'], $this->statuses, true)) {
            $this->displayFail("Please select a valid status.", $old);
            return;
        }
        if (count($files) > $this->maxImages) {
            $this->displayFail("You can upload at most " . $this->maxImages . " images.", $old);
            return;
        }

        // Check every image before anything is saved
        $mimeToExt = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp');
        $checked = array();
        foreach ($files as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $this->displayFail("There was a problem uploading \"" . $f['name'] . "\". Please try again.", $old);
                return;
            }
            if ($f['size'] > $this->maxBytes) {
                $this->displayFail("\"" . $f['name'] . "\" is larger than 2 MB.", $old);
                return;
            }
            $info = @getimagesize($f['tmp']);
            if ($info === false || !isset($mimeToExt[$info['mime']])) {
                $this->displayFail("\"" . $f['name'] . "\" is not a valid image (use JPG, PNG, GIF or WebP).", $old);
                return;
            }
            $checked[] = array('tmp' => $f['tmp'], 'ext' => $mimeToExt[$info['mime']]);
        }

        // Store the images on disk with random names
        $dir = $this->uploadDir();
        $saved = array();      // relative paths saved to the database
        $absolute = array();   // for cleanup if saving fails
        foreach ($checked as $c) {
            $name = bin2hex(random_bytes(8)) . '.' . $c['ext'];
            if (!move_uploaded_file($c['tmp'], $dir . '/' . $name)) {
                $this->removeFiles($absolute);
                $this->displayFail("The images could not be stored. Please try again.", $old);
                return;
            }
            $saved[] = 'uploads/projects/' . $name;
            $absolute[] = $dir . '/' . $name;
        }

        // createProject(...): bool
        $controller = new CreateProject_controller();
        if ($controller->CreateProject($old['title'], $old['project_date'], $old['description'], $old['status'], $_SESSION['email'], $saved)) {
            $this->displaySuccess("Project created successfully.");
        } else {
            $this->removeFiles($absolute);     // nothing is kept when the save fails
            $this->displayFail("Failed to create the project. Nothing was saved.", $old);
        }
    }

    // displaySuccess(): step 5 - return to the portfolio, which now includes the new project
    public function displaySuccess($msg) {
        header("Location: id_portfolio.php?created=1");
        exit;
    }

    // displayFail()
    public function displayFail($msg, $old = array()) {
        $this->displayForm($msg, $old);
    }

    // ---- helpers ----

    private function collectImages() {
        $out = array();
        if (!isset($_FILES['images']) || !is_array($_FILES['images']['name'])) {
            return $out;
        }
        $f = $_FILES['images'];
        foreach ($f['name'] as $i => $name) {
            if ($f['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[] = array('name' => $name, 'tmp' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]);
        }
        return $out;
    }

    private function uploadDir() {
        $dir = __DIR__ . '/../uploads/projects';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        // never let uploaded files run as PHP
        $ht = $dir . '/.htaccess';
        if (!file_exists($ht)) {
            file_put_contents($ht, '<FilesMatch "\.(php|phtml|php3|php4|php5|phps)$">' . "\n" . '  Require all denied' . "\n" . '</FilesMatch>' . "\n");
        }
        return $dir;
    }

    private function removeFiles($paths) {
        foreach ($paths as $p) {
            if (file_exists($p)) {
                unlink($p);
            }
        }
    }
}

// ---- Flow from the sequence diagram ----
$gui = new ID_GUI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gui->submitProject();               // submitProject()
} else {
    $gui->displayForm();                 // Click "Create Project" -> form
}
