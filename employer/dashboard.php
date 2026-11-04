<?php
require_once '../config.php';
require_role('employer');
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jid = (int)($_POST['job_id'] ?? 0);
    $act = $_POST['action'] ?? '';
    if ($act === 'close' || $act === 'open') {
        $new = $act === 'close' ? 'closed' : 'open';
        $st = $conn->prepare('UPDATE jobs SET status=? WHERE id=? AND employer_id=?');
        $st->bind_param('sii', $new, $jid, $uid);
        $st->execute();
        flash('ok', 'Job is now ' . $new . '.');
    } elseif ($act === 'delete') {
        $st = $conn->prepare('DELETE FROM jobs WHERE id=? AND employer_id=?');
        $st->bind_param('ii', $jid, $uid);
        $st->execute();
        flash('ok', 'Job deleted.');
    }
    header('Location: dashboard.php');
    exit;
}

$st = $conn->prepare('SELECT * FROM employer_profiles WHERE user_id=?');
$st->bind_param('i', $uid);
$st->execute();
$ep = $st->get_result()->fetch_assoc();

$st = $conn->prepare("SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) AS total
                      FROM jobs j WHERE j.employer_id=? ORDER BY j.id DESC");
$st->bind_param('i', $uid);
$st->execute();
$jobs = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$totalApps = array_sum(array_column($jobs, 'total'));
$openJobs = count(array_filter($jobs, fn($j) => $j['status'] === 'open'));
$page_title = 'Employer dashboard';
require '../includes/header.php';
?>
<h1><?= e($ep['company_name']) ?></h1>
<p>Approval status: <?= badge(ucfirst($ep['approval_status'])) ?></p>
<?php if ($ep['approval_status'] !== 'approved'): ?>
  <div class="notice info">Your company is waiting for admin approval. You can post jobs and see applicants after it is approved.</div>
<?php endif; ?>

<div class="stats">
  <div class="stat"><b><?= count($jobs) ?></b>Jobs posted</div>
  <div class="stat"><b><?= $openJobs ?></b>Open jobs</div>
  <div class="stat"><b><?= (int)$totalApps ?></b>Applications received</div>
</div>

<h2>Your jobs</h2>
<?php if (!$jobs): ?>
  <p class="empty">No jobs yet. <a href="post_job.php">Post your first job</a>.</p>
<?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Title</th><th>Location</th><th>Last date</th><th>Status</th><th>Applicants</th><th>Actions</th></tr>
  <?php foreach ($jobs as $j): ?>
  <tr>
    <td><?= e($j['title']) ?></td>
    <td><?= e($j['location']) ?></td>
    <td><?= e(date('d M Y', strtotime($j['last_date']))) ?></td>
    <td><?= badge(ucfirst($j['status'])) ?></td>
    <td><a href="applicants.php?job_id=<?= (int)$j['id'] ?>"><?= (int)$j['total'] ?> applicants</a></td>
    <td class="actions">
      <form method="post">
        <input type="hidden" name="job_id" value="<?= (int)$j['id'] ?>">
        <?php if ($j['status'] === 'open'): ?>
          <button class="warn" name="action" value="close">Close</button>
        <?php else: ?>
          <button name="action" value="open">Reopen</button>
        <?php endif; ?>
        <button class="danger" name="action" value="delete" onclick="return confirm('Delete this job and its applications?')">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
<?php require '../includes/footer.php'; ?>
