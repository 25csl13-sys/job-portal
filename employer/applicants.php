<?php
require_once '../config.php';
require_role('employer');
$uid = $_SESSION['user_id'];
$jid = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 0);

// The job must belong to this employer
$st = $conn->prepare('SELECT id,title FROM jobs WHERE id=? AND employer_id=?');
$st->bind_param('ii', $jid, $uid);
$st->execute();
$job = $st->get_result()->fetch_assoc();
if (!$job) { flash('err', 'Job not found.'); header('Location: dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aid = (int)($_POST['application_id'] ?? 0);
    $new = $_POST['status'] ?? '';
    if (in_array($new, ['Shortlisted', 'Rejected'])) {
        $st = $conn->prepare('UPDATE applications SET status=? WHERE id=? AND job_id=?');
        $st->bind_param('sii', $new, $aid, $jid);
        $st->execute();
        flash('ok', 'Application marked as ' . $new . '.');
    }
    header('Location: applicants.php?job_id=' . $jid);
    exit;
}

$st = $conn->prepare("SELECT a.id, a.cover_note, a.applied_date, a.status, u.name, u.email, u.phone,
                             sp.qualification, sp.skills, sp.experience
                      FROM applications a
                      JOIN users u ON u.id=a.seeker_id
                      LEFT JOIN seeker_profiles sp ON sp.user_id=u.id
                      WHERE a.job_id=? ORDER BY a.id DESC");
$st->bind_param('i', $jid);
$st->execute();
$apps = $st->get_result();
$page_title = 'Applicants';
require '../includes/header.php';
?>
<p><a href="dashboard.php">&larr; Back to dashboard</a></p>
<h1>Applicants for <?= e($job['title']) ?></h1>
<?php if ($apps->num_rows === 0): ?>
  <p class="empty">No one has applied yet.</p>
<?php endif; ?>
<?php while ($a = $apps->fetch_assoc()): ?>
<article class="card job">
  <div style="flex:1;min-width:240px">
    <h3><?= e($a['name']) ?> <?= badge($a['status']) ?></h3>
    <div class="meta"><?= e($a['email']) ?> &middot; <?= e($a['phone']) ?></div>
    <p class="meta">
      Qualification: <?= e($a['qualification'] ?: 'Not added') ?><br>
      Skills: <?= e($a['skills'] ?: 'Not added') ?><br>
      Experience: <?= e($a['experience'] ?: 'Not added') ?><br>
      Applied on <?= e(date('d M Y', strtotime($a['applied_date']))) ?>
    </p>
    <p><?= nl2br(e($a['cover_note'])) ?></p>
  </div>
  <form method="post" class="actions">
    <input type="hidden" name="job_id" value="<?= $jid ?>">
    <input type="hidden" name="application_id" value="<?= (int)$a['id'] ?>">
    <button name="status" value="Shortlisted">Shortlist</button>
    <button class="danger" name="status" value="Rejected">Reject</button>
  </form>
</article>
<?php endwhile; ?>
<?php require '../includes/footer.php'; ?>
