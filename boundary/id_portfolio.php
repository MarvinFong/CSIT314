<?php
session_start();
require_once __DIR__ . '/../controller/CreateProject_controller.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Interior Designer') {
    header("Location: login.php");
    exit;
}

$controller = new CreateProject_controller();
$projects = $controller->GetPortfolio($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Portfolio</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 40px; }
    .success { color:#1b7e2e; margin-bottom:14px; }
    .grid { display:flex; flex-wrap:wrap; gap:18px; margin-top:16px; }
    .card { width:220px; border:1px solid #ddd; border-radius:6px; overflow:hidden; background:#fff; }
    .card img { width:100%; height:150px; object-fit:cover; display:block; background:#eee; }
    .card div { padding:10px; }
    .card small { color:#666; display:block; margin-top:4px; }
    .btn { display:inline-block; padding:8px 14px; border:1px solid #888; border-radius:4px; text-decoration:none; color:#000; background:#f2f2f2; }
  </style>
</head>
<body>
  <h1>My Portfolio</h1>
  <?php if (isset($_GET['created'])): ?>
    <div class="success">Project created successfully.</div>
  <?php endif; ?>

  <a class="btn" href="id_create_project.php">Create Project</a>

  <?php if (count($projects) === 0): ?>
    <p>You have no projects yet.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($projects as $p): ?>
        <div class="card">
          <?php if ($p['cover']): ?>
            <img src="../<?= htmlspecialchars($p['cover']) ?>" alt="">
          <?php else: ?>
            <img alt="">
          <?php endif; ?>
          <div>
            <strong><?= htmlspecialchars($p['title']) ?></strong>
            <small><?= htmlspecialchars($p['project_date']) ?> &middot; <?= htmlspecialchars($p['status']) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <p><a href="designer_home.php">Back to home</a></p>
</body>
</html>
