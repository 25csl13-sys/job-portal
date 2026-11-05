<?php
require_once 'config.php';
$id = (int)($_GET['id'] ?? 0);

$st = $conn->prepare("SELECT j.*, ep.company_name, ep.website, ep.description AS company_info
                      FROM jobs j JOIN employer_profiles ep ON ep.user_id=j.employer_id
                      WHERE j.id=? AND ep.approval_status='approved'");
$st->bind_param('i', $id);
$st->execute();
$job = $st->get_result()->fetch_assoc();
if (!$job) { flash('err', 'That job was not found.'); header('Location: index.php'); exit; }

$isOpen = $job['status'] === 'open' && $job['last_date'] >= date('Y-m-d');
$applied = null;
if (($_SESSION['role'] ?? '') === 'seeker') {
    $st = $conn->prepare('SELECT status FROM applications WHERE job_id=? AND seeker_id=?');
    $st->bind_param('ii', $id, $_SESSION['user_id']);
    $st->execute();
    $applied = $st->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_role('seeker');
    if (!$isOpen) {
        flash('err', 'This job is closed for applications.');
    } elseif ($applied) {
        flash('info', 'You have already applied for this job.');
    } else {
        $note = trim($_POST['cover_note'] ?? '');
        $today = date('Y-m-d');
        $st = $conn->prepare('INSERT INTO applications (job_id,seeker_id,cover_note,applied_date) VALUES (?,?,?,?)');
        $st->bind_param('iiss', $id, $_SESSION['user_id'], $note, $today);
        $st->execute();
        flash('ok', 'Application sent. Track its status under My applications.');
    }
    header('Location: job.php?id=' . $id);
    exit;
}
$page_title = $job['title'];
require 'includes/header.php';
?>
<div class="card">
  <h1><?= e($job['title']) ?></h1>
  <p class="meta"><?= e($job['company_name']) ?> &middot; <?= e($job['location']) ?> &middot; <?= e($job['job_type']) ?><?= $job['salary'] ? ' &middot; ' . e($job['salary']) : '' ?></p>
  <p class="meta">Posted <?= e(date('d M Y', strtotime($job['posted_date']))) ?> &middot; Apply by <?= e(date('d M Y', strtotime($job['last_date']))) ?> <?= badge($isOpen ? 'Open' : 'Closed') ?></p>
  <h3>About the role</h3>
  <p><?= nl2br(e($job['description'])) ?></p>
  <h3>Skills required</h3>
  <div class="tags">
    <?php foreach (array_filter(array_map('trim', explode(',', $job['skills']))) as $s): ?><span class="tag"><?= e($s) ?></span><?php endforeach; ?>
  </div>
  <?php if ($job['company_info']): ?><h3 style="margin-top:1rem">About <?= e($job['company_name']) ?></h3><p><?= e($job['company_info']) ?></p><?php endif; ?>
</div>

<div class="card">
<?php if (!isset($_SESSION['user_id'])): ?>
  <p><a href="login.php">Log in</a> or <a href="register.php">register</a> as a job seeker to apply.</p>
<?php elseif ($_SESSION['role'] !== 'seeker'): ?>
  <p class="meta">Only job seeker accounts can apply.</p>
<?php elseif ($applied): ?>
  <p>You applied for this job. Current status: <?= badge($applied['status']) ?></p>
<?php elseif (!$isOpen): ?>
  <p class="meta">This job is no longer accepting applications.</p>
<?php else: ?>
  <h3>Apply for this job</h3>
  <form method="post">
    <label for="cover_note">Cover note</label>
    <textarea id="cover_note" name="cover_note" rows="4" maxlength="1000" placeholder="Why are you a good fit?" required></textarea>
    <p><button type="submit">Send application</button></p>
  </form>
<?php endif; ?>
</div>
<?php require 'includes/footer.php'; ?>
