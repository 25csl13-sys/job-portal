<?php
$page_title = 'Find jobs';
require 'includes/header.php';

$q    = trim($_GET['q'] ?? '');
$loc  = trim($_GET['location'] ?? '');
$type = $_GET['type'] ?? '';

$sql = "SELECT j.*, ep.company_name FROM jobs j
        JOIN employer_profiles ep ON ep.user_id = j.employer_id
        JOIN users u ON u.id = j.employer_id
        WHERE j.status='open' AND j.last_date >= CURDATE()
          AND ep.approval_status='approved' AND u.status='active'";
$types = ''; $vals = [];
if ($q !== '')    { $sql .= " AND (j.title LIKE ? OR j.skills LIKE ? OR ep.company_name LIKE ?)"; $like = "%$q%"; $types .= 'sss'; array_push($vals, $like, $like, $like); }
if ($loc !== '')  { $sql .= " AND j.location LIKE ?"; $types .= 's'; $vals[] = "%$loc%"; }
if (in_array($type, ['Full-time', 'Part-time', 'Internship'])) { $sql .= " AND j.job_type = ?"; $types .= 's'; $vals[] = $type; }
$sql .= " ORDER BY j.posted_date DESC, j.id DESC";

$st = $conn->prepare($sql);
if ($types) $st->bind_param($types, ...$vals);
$st->execute();
$jobs = $st->get_result();
?>
<section class="hero">
  <h1>Find a job that fits your skills</h1>
  <p>Search openings from approved employers, apply in one step and track every application.</p>
</section>

<form class="search" method="get">
  <input type="text" name="q" placeholder="Job title, skill or company" value="<?= e($q) ?>" aria-label="Keyword">
  <input type="text" name="location" placeholder="Location" value="<?= e($loc) ?>" aria-label="Location">
  <select name="type" aria-label="Job type">
    <option value="">Any type</option>
    <?php foreach (['Full-time', 'Part-time', 'Internship'] as $t): ?>
      <option <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Search jobs</button>
</form>

<?php if ($jobs->num_rows === 0): ?>
  <p class="empty">No jobs match your search. Try a different keyword or clear the filters.</p>
<?php endif; ?>

<?php while ($j = $jobs->fetch_assoc()): ?>
  <article class="card job">
    <div>
      <h3><a href="job.php?id=<?= (int)$j['id'] ?>"><?= e($j['title']) ?></a></h3>
      <div class="meta"><?= e($j['company_name']) ?> &middot; <?= e($j['location']) ?> &middot; <?= e($j['job_type']) ?><?= $j['salary'] ? ' &middot; ' . e($j['salary']) : '' ?></div>
      <div class="tags">
        <?php foreach (array_filter(array_map('trim', explode(',', $j['skills']))) as $s): ?>
          <span class="tag"><?= e($s) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="meta">Apply by <?= e(date('d M Y', strtotime($j['last_date']))) ?><br><a class="btn small" href="job.php?id=<?= (int)$j['id'] ?>">View and apply</a></div>
  </article>
<?php endwhile; ?>

<?php require 'includes/footer.php'; ?>
