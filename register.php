<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => '', 'role' => 'seeker', 'company' => '', 'location' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) $old[$k] = trim($_POST[$k] ?? $v);
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($old['name'] === '') $errors[] = 'Enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (!preg_match('/^[0-9]{10}$/', $old['phone'])) $errors[] = 'Phone number must have 10 digits.';
    if (strlen($pass) < 6) $errors[] = 'Password must have at least 6 characters.';
    if ($pass !== $pass2) $errors[] = 'Passwords do not match.';
    if (!in_array($old['role'], ['seeker', 'employer'])) $errors[] = 'Choose a valid account type.';
    if ($old['role'] === 'employer' && $old['company'] === '') $errors[] = 'Enter your company name.';

    if (!$errors) {
        $st = $conn->prepare('SELECT id FROM users WHERE email=?');
        $st->bind_param('s', $old['email']);
        $st->execute();
        if ($st->get_result()->num_rows) $errors[] = 'This email is already registered. Log in instead.';
    }

    if (!$errors) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $st = $conn->prepare('INSERT INTO users (name,email,password,phone,role) VALUES (?,?,?,?,?)');
        $st->bind_param('sssss', $old['name'], $old['email'], $hash, $old['phone'], $old['role']);
        $st->execute();
        $uid = $conn->insert_id;

        if ($old['role'] === 'employer') {
            $st = $conn->prepare('INSERT INTO employer_profiles (user_id,company_name,location) VALUES (?,?,?)');
            $st->bind_param('iss', $uid, $old['company'], $old['location']);
        } else {
            $st = $conn->prepare('INSERT INTO seeker_profiles (user_id) VALUES (?)');
            $st->bind_param('i', $uid);
        }
        $st->execute();

        flash('ok', $old['role'] === 'employer'
            ? 'Account created. You can post jobs once the admin approves your company.'
            : 'Account created. Log in to start applying.');
        header('Location: login.php');
        exit;
    }
}
$page_title = 'Register';
require 'includes/header.php';
?>
<div class="card narrow">
  <h2>Create your account</h2>
  <?php foreach ($errors as $er): ?><div class="notice err"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post" id="regForm">
    <label for="role">I am a</label>
    <select name="role" id="role">
      <option value="seeker" <?= $old['role'] === 'seeker' ? 'selected' : '' ?>>Job seeker</option>
      <option value="employer" <?= $old['role'] === 'employer' ? 'selected' : '' ?>>Employer</option>
    </select>
    <label for="name">Full name</label>
    <input id="name" name="name" value="<?= e($old['name']) ?>" required>
    <label for="email">Email</label>
    <input id="email" type="email" name="email" value="<?= e($old['email']) ?>" required>
    <label for="phone">Phone (10 digits)</label>
    <input id="phone" name="phone" value="<?= e($old['phone']) ?>" pattern="[0-9]{10}" required>
    <div id="employerFields">
      <label for="company">Company name</label>
      <input id="company" name="company" value="<?= e($old['company']) ?>">
      <label for="location">Company location</label>
      <input id="location" name="location" value="<?= e($old['location']) ?>">
    </div>
    <label for="password">Password</label>
    <input id="password" type="password" name="password" minlength="6" required>
    <label for="password2">Confirm password</label>
    <input id="password2" type="password" name="password2" minlength="6" required>
    <p><button type="submit">Create account</button></p>
  </form>
  <p class="meta">Already registered? <a href="login.php">Log in</a></p>
</div>
<script>
  const role = document.getElementById('role'), box = document.getElementById('employerFields');
  function toggle() { box.style.display = role.value === 'employer' ? 'block' : 'none'; }
  role.addEventListener('change', toggle); toggle();
  document.getElementById('regForm').addEventListener('submit', function (ev) {
    if (document.getElementById('password').value !== document.getElementById('password2').value) {
      ev.preventDefault(); alert('Passwords do not match.');
    }
  });
</script>
<?php require 'includes/footer.php'; ?>
