<?php
require_once 'config.php';
require_role('seeker');

$st = $conn->prepare("SELECT a.applied_date, a.status, j.id AS job_id, j.title, j.location, ep.company_name
                      FROM applications a
                      JOIN jobs j ON j.id=a.job_id
                      JOIN employer_profiles ep ON ep.user_id=j.employer_id
                      WHERE a.seeker_id=? ORDER BY a.applied_date DESC, a.id DESC");
$st->bind_param('i', $_SESSION['user_id']);
$st->execute();
$rows = $st->get_result();
$page_title = 'My applications';
require 'includes/header.php';
?>
<h1>My applications</h1>
<?php if ($rows->num_rows === 0): ?>
  <p class="empty">You have not applied for any jobs yet. <a href="index.php">Browse open jobs</a>.</p>
<?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Job</th><th>Company</th><th>Location</th><th>Applied on</th><th>Status</th></tr>
  <?php while ($r = $rows->fetch_assoc()): ?>
  <tr>
    <td><a href="job.php?id=<?= (int)$r['job_id'] ?>"><?= e($r['title']) ?></a></td>
    <td><?= e($r['company_name']) ?></td>
    <td><?= e($r['location']) ?></td>
    <td><?= e(date('d M Y', strtotime($r['applied_date']))) ?></td>
    <td><?= badge($r['status']) ?></td>
  </tr>
  <?php endwhile; ?>
</table></div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
