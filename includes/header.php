<?php require_once __DIR__ . '/../config.php'; $role = $_SESSION['role'] ?? ''; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? 'Online Job Portal') ?> | Online Job Portal</title>
<link rel="stylesheet" href="<?= BASE ?>css/style.css">
</head>
<body>
<header class="top">
  <a class="brand" href="<?= BASE ?>index.php">Job<span>Portal</span></a>
  <nav>
    <a href="<?= BASE ?>index.php">Find jobs</a>
    <?php if ($role === 'seeker'): ?>
      <a href="<?= BASE ?>my_applications.php">My applications</a>
      <a href="<?= BASE ?>profile.php">My profile</a>
    <?php elseif ($role === 'employer'): ?>
      <a href="<?= BASE ?>employer/dashboard.php">Dashboard</a>
      <a href="<?= BASE ?>employer/post_job.php">Post a job</a>
    <?php elseif ($role === 'admin'): ?>
      <a href="<?= BASE ?>admin/dashboard.php">Admin panel</a>
    <?php endif; ?>
    <?php if ($role): ?>
      <a href="<?= BASE ?>logout.php">Log out (<?= e($_SESSION['name']) ?>)</a>
    <?php else: ?>
      <a href="<?= BASE ?>login.php">Log in</a>
      <a class="btn small" href="<?= BASE ?>register.php">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main>
<?php show_flash(); ?>
