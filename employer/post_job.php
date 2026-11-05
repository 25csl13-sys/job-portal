<?php
require_once '../config.php';
require_role('employer');
$uid = $_SESSION['user_id'];
$approved = employer_approved($conn, $uid);

$errors = [];
$f = ['title' => '', 'description' => '', 'location' => '', 'salary' => '', 'job_type' => 'Full-time', 'skills' => '', 'last_date' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $v) $f[$k] = trim($_POST[$k] ?? $v);
    if (!$approved) $errors[] = 'Your company is not approved yet.';
    if ($f['title'] === '' || $f['description'] === '' || $f['location'] === '') $errors[] = 'Title, description and location are required.';
    if (!in_array($f['job_type'], ['Full-time', 'Part-time', 'Internship'])) $errors[] = 'Choose a job type.';
    if ($f['last_date'] < date('Y-m-d')) $errors[] = 'Last date to apply cannot be in the past.';

    if (!$errors) {
        $today = date('Y-m-d');
        $st = $conn->prepare('INSERT INTO jobs (employer_id,title,description,location,salary,job_type,skills,posted_date,last_date) VALUES (?,?,?,?,?,?,?,?,?)');
        $st->bind_param('issssssss', $uid, $f['title'], $f['description'], $f['location'], $f['salary'], $f['job_type'], $f['skills'], $today, $f['last_date']);
        $st->execute();
        flash('ok', 'Job posted. Seekers can now find and apply for it.');
        header('Location: dashboard.php');
        exit;
    }
}
$page_title = 'Post a job';
require '../includes/header.php';
?>
<div class="card narrow" style="max-width:640px">
  <h2>Post a job</h2>
  <?php if (!$approved): ?><div class="notice info">You can post jobs after the admin approves your company.</div><?php endif; ?>
  <?php foreach ($errors as $er): ?><div class="notice err"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post">
    <label for="title">Job title</label>
    <input id="title" name="title" value="<?= e($f['title']) ?>" required>
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5" required><?= e($f['description']) ?></textarea>
    <label for="location">Location</label>
    <input id="location" name="location" value="<?= e($f['location']) ?>" required>
    <label for="salary">Salary</label>
    <input id="salary" name="salary" value="<?= e($f['salary']) ?>" placeholder="e.g. 4 - 6 LPA">
    <label for="job_type">Job type</label>
    <select id="job_type" name="job_type">
      <?php foreach (['Full-time', 'Part-time', 'Internship'] as $t): ?><option <?= $f['job_type'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
    </select>
    <label for="skills">Skills required (comma separated)</label>
    <input id="skills" name="skills" value="<?= e($f['skills']) ?>">
    <label for="last_date">Last date to apply</label>
    <input id="last_date" type="date" name="last_date" value="<?= e($f['last_date']) ?>" min="<?= date('Y-m-d') ?>" required>
    <p><button type="submit" <?= $approved ? '' : 'disabled' ?>>Post job</button></p>
  </form>
</div>
<?php require '../includes/footer.php'; ?>
