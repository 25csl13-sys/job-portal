<?php
define('BASE', '/');
session_start();

class SqliteResult {
    private array $rows;
    private int $index = 0;
    public int $num_rows;
    public function __construct(array $rows) {
        $this->rows = $rows;
        $this->num_rows = count($rows);
    }
    public function fetch_assoc(): array|false {
        return $this->index < $this->num_rows ? $this->rows[$this->index++] : false;
    }
    public function fetch_row(): array|false {
        $row = $this->fetch_assoc();
        return $row === false ? false : array_values($row);
    }
    public function fetch_all(int $mode = 1): array {
        return $this->rows;
    }
}

class SqliteStatement {
    private PDO $pdo;
    private SqliteConnection $connection;
    private string $sql;
    private array $params = [];
    public function __construct(PDO $pdo, SqliteConnection $connection, string $sql) {
        $this->pdo = $pdo;
        $this->connection = $connection;
        $this->sql = $sql;
    }
    public function bind_param(string $types, mixed ...$values): void {
        $this->params = array_values($values);
    }
    public function execute(): bool {
        $stmt = $this->pdo->prepare(SqliteConnection::translate($this->sql));
        $result = $stmt->execute($this->params);
        $this->connection->insert_id = (int)$this->pdo->lastInsertId();
        return $result;
    }
    public function get_result(): SqliteResult {
        $stmt = $this->pdo->prepare(SqliteConnection::translate($this->sql));
        $stmt->execute($this->params);
        return new SqliteResult($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

class SqliteConnection {
    private PDO $pdo;
    public int $insert_id = 0;
    public function __construct(string $path) {
        $dir = dirname($path);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $this->pdo = new PDO('sqlite:' . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initialize($path);
    }
    private function initialize(string $path): void {
        $exists = file_exists($path) && filesize($path) > 0;
        if ($exists) return;
        $schema = __DIR__ . '/database.sqlite.sql';
        if (is_file($schema)) $this->pdo->exec(file_get_contents($schema));
    }
    public static function translate(string $sql): string {
        $sql = preg_replace('/CURDATE\(\)/i', "date('now','localtime')", $sql);
        $sql = preg_replace_callback('/DATE_ADD\(([^,]+),INTERVAL\s+(\d+)\s+DAY\)/i', function ($m) {
            return "date(" . trim($m[1]) . ", '+" . $m[2] . " days')";
        }, $sql);
        return $sql;
    }
    public function prepare(string $sql): SqliteStatement {
        return new SqliteStatement($this->pdo, $this, $sql);
    }
    public function query(string $sql): SqliteResult {
        $stmt = $this->pdo->query(self::translate($sql));
        return new SqliteResult($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    public function get_last_insert_id(): int {
        return (int)$this->pdo->lastInsertId();
    }
}

$DB_PATH = getenv('DB_PATH') ?: __DIR__ . '/data/job_portal.sqlite';
try {
    $conn = new SqliteConnection($DB_PATH);
    $conn->insert_id = 0;
} catch (Exception $ex) {
    die('Database connection failed: ' . e($ex->getMessage()));
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
