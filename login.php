<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $st = $conn->prepare('SELECT id,name,password,role,status FROM users WHERE email=?');
    $st->bind_param('s', $email);
    $st->execute();
    $u = $st->get_result()->fetch_assoc();

    if (!$u || !password_verify($pass, $u['password'])) {
        $error = 'Email or password is incorrect.';
    } elseif ($u['status'] === 'blocked') {
        $error = 'Your account is blocked. Contact the site admin.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['name'] = $u['name'];
        $_SESSION['role'] = $u['role'];
        $go = ['seeker' => 'index.php', 'employer' => 'employer/dashboard.php', 'admin' => 'admin/dashboard.php'];
        header('Location: ' . $go[$u['role']]);
        exit;
    }
}
$page_title = 'Log in';
require 'includes/header.php';
?>
<div class="card narrow">
  <h2>Log in</h2>
  <?php if ($error): ?><div class="notice err"><?= e($error) ?></div><?php endif; ?>
  <form method="post">
    <label for="email">Email</label>
    <input id="email" type="email" name="email" required autofocus>
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required>
    <p><button type="submit">Log in</button></p>
  </form>
  <p class="meta">New here? <a href="register.php">Create an account</a></p>
</div>
<?php require 'includes/footer.php'; ?>
