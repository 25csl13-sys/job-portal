<?php
define('BASE', '/');
$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASS') ?: '';
$DB_NAME = getenv('DB_NAME') ?: 'job_portal';

session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (Exception $ex) {
    die('Database connection failed. Check config.php and make sure database.sql was imported.');
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function require_role($role) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        $_SESSION['flash'] = ['err', 'Please log in with the correct account to open that page.'];
        header('Location: ' . BASE . 'login.php');
        exit;
    }
}

function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        [$t, $m] = $_SESSION['flash'];
        echo '<div class="notice ' . e($t) . '">' . e($m) . '</div>';
        unset($_SESSION['flash']);
    }
}

function badge($status) {
    $cls = strtolower($status);
    return '<span class="badge ' . e($cls) . '">' . e($status) . '</span>';
}

// Is the logged-in employer approved by the admin?
function employer_approved($conn, $uid) {
    $st = $conn->prepare('SELECT approval_status FROM employer_profiles WHERE user_id=?');
    $st->bind_param('i', $uid);
    $st->execute();
    $r = $st->get_result()->fetch_assoc();
    return $r && $r['approval_status'] === 'approved';
}
