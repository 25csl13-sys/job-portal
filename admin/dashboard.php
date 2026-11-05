<?php
require_once '../config.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($act === 'approve' || $act === 'reject') {
        $new = $act === 'approve' ? 'approved' : 'rejected';
        $st = $conn->prepare('UPDATE employer_profiles SET approval_status=? WHERE user_id=?');
        $st->bind_param('si', $new, $id);
        $st->execute();
        flash('ok', 'Employer ' . $new . '.');
    } elseif ($act === 'block' || $act === 'unblock') {
        $new = $act === 'block' ? 'blocked' : 'active';
        $st = $conn->prepare("UPDATE users SET status=? WHERE id=? AND role<>'admin'");
        $st->bind_param('si', $new, $id);
        $st->execute();
        flash('ok', 'User is now ' . $new . '.');
    } elseif ($act === 'delete_user') {
        $st = $conn->prepare("DELETE FROM users WHERE id=? AND role<>'admin'");
        $st->bind_param('i', $id);
        $st->execute();
        flash('ok', 'User deleted.');
    } elseif ($act === 'delete_job') {
        $st = $conn->prepare('DELETE FROM jobs WHERE id=?');
        $st->bind_param('i', $id);
        $st->execute();
        flash('ok', 'Job listing deleted.');
    }
    header('Location: dashboard.php');
    exit;
}

$count = fn($sql) => (int)$conn->query($sql)->fetch_row()[0];
$stats = [
    'Job seekers' => $count("SELECT COUNT(*) FROM users WHERE role='seeker'"),
    'Employers' => $count("SELECT COUNT(*) FROM users WHERE role='employer'"),
    'Pending approvals' => $count("SELECT COUNT(*) FROM employer_profiles WHERE approval_status='pending'"),
    'Jobs' => $count('SELECT COUNT(*) FROM jobs'),
    'Applications' => $count('SELECT COUNT(*) FROM applications'),
];
$pending = $conn->query("SELECT u.id,u.name,u.email,ep.company_name,ep.location FROM employer_profiles ep JOIN users u ON u.id=ep.user_id WHERE ep.approval_status='pending'");
$users = $conn->query("SELECT id,name,email,role,status FROM users WHERE role<>'admin' ORDER BY role,id");
$jobs = $conn->query("SELECT j.id,j.title,j.location,j.status,ep.company_name FROM jobs j JOIN employer_profiles ep ON ep.user_id=j.employer_id ORDER BY j.id DESC");
$page_title = 'Admin panel';
require '../includes/header.php';
?>
<h1>Admin panel</h1>
<div class="stats">
  <?php foreach ($stats as $label => $n): ?><div class="stat"><b><?= $n ?></b><?= e($label) ?></div><?php endforeach; ?>
</div>

<h2>Employers waiting for approval</h2>
<?php if ($pending->num_rows === 0): ?>
  <p class="empty">No pending employers.</p>
<?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Company</th><th>Contact</th><th>Location</th><th>Action</th></tr>
  <?php while ($p = $pending->fetch_assoc()): ?>
  <tr>
    <td><?= e($p['company_name']) ?></td>
    <td><?= e($p['name']) ?><br><span class="meta"><?= e($p['email']) ?></span></td>
    <td><?= e($p['location']) ?></td>
    <td class="actions"><form method="post">
      <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <button name="action" value="approve">Approve</button>
      <button class="danger" name="action" value="reject">Reject</button>
    </form></td>
  </tr>
  <?php endwhile; ?>
</table></div>
<?php endif; ?>

<h2>Manage users</h2>
<div class="table-wrap"><table>
  <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
  <?php while ($u = $users->fetch_assoc()): ?>
  <tr>
    <td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e(ucfirst($u['role'])) ?></td>
    <td><?= badge(ucfirst($u['status'])) ?></td>
    <td class="actions"><form method="post">
      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
      <?php if ($u['status'] === 'active'): ?><button class="warn" name="action" value="block">Block</button>
      <?php else: ?><button name="action" value="unblock">Unblock</button><?php endif; ?>
      <button class="danger" name="action" value="delete_user" onclick="return confirm('Delete this user and all their data?')">Delete</button>
    </form></td>
  </tr>
  <?php endwhile; ?>
</table></div>

<h2>Manage job listings</h2>
<div class="table-wrap"><table>
  <tr><th>Title</th><th>Company</th><th>Location</th><th>Status</th><th>Action</th></tr>
  <?php while ($j = $jobs->fetch_assoc()): ?>
  <tr>
    <td><?= e($j['title']) ?></td><td><?= e($j['company_name']) ?></td><td><?= e($j['location']) ?></td>
    <td><?= badge(ucfirst($j['status'])) ?></td>
    <td class="actions"><form method="post">
      <input type="hidden" name="id" value="<?= (int)$j['id'] ?>">
      <button class="danger" name="action" value="delete_job" onclick="return confirm('Delete this listing?')">Delete</button>
    </form></td>
  </tr>
  <?php endwhile; ?>
</table></div>
<?php require '../includes/footer.php'; ?>
