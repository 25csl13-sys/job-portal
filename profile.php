<?php
require_once 'config.php';
require_role('seeker');
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q = trim($_POST['qualification'] ?? '');
    $s = trim($_POST['skills'] ?? '');
    $x = trim($_POST['experience'] ?? '');
    $st = $conn->prepare('UPDATE seeker_profiles SET qualification=?, skills=?, experience=? WHERE user_id=?');
    $st->bind_param('sssi', $q, $s, $x, $uid);
    $st->execute();
    flash('ok', 'Profile saved.');
    header('Location: profile.php');
    exit;
}
$st = $conn->prepare('SELECT * FROM seeker_profiles WHERE user_id=?');
$st->bind_param('i', $uid);
$st->execute();
$p = $st->get_result()->fetch_assoc() ?: ['qualification' => '', 'skills' => '', 'experience' => ''];
$page_title = 'My profile';
require 'includes/header.php';
?>
<div class="card narrow">
  <h2>My profile</h2>
  <p class="meta">Employers see this when you apply.</p>
  <form method="post">
    <label for="qualification">Qualification</label>
    <input id="qualification" name="qualification" value="<?= e($p['qualification']) ?>">
    <label for="skills">Skills (comma separated)</label>
    <input id="skills" name="skills" value="<?= e($p['skills']) ?>">
    <label for="experience">Experience</label>
    <input id="experience" name="experience" value="<?= e($p['experience']) ?>" placeholder="Fresher, 2 years...">
    <p><button type="submit">Save profile</button></p>
  </form>
</div>
<?php require 'includes/footer.php'; ?>
