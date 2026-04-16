<?php
// ─── Auth ────────────────────────────────────────────────────────────────────
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['valt_admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
        if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
            $_SESSION['valt_admin'] = true;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — VALT Academy</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#0a2342;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif}
  .login-card{background:#fff;border-radius:16px;padding:48px 40px;width:100%;max-width:400px;box-shadow:0 24px 64px rgba(0,0,0,.4)}
  .login-card h1{color:#0a2342;font-size:22px;font-weight:700;margin-bottom:4px}
  .login-card p{color:#7a8ba0;font-size:13px;margin-bottom:32px}
  label{display:block;font-size:13px;font-weight:600;color:#3a4a5c;margin-bottom:6px}
  input{width:100%;padding:12px 14px;border:1.5px solid #dde3ec;border-radius:8px;font-size:14px;outline:none;margin-bottom:18px}
  input:focus{border-color:#2a9d8f}
  .btn{width:100%;padding:13px;background:#0a2342;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer}
  .btn:hover{background:#0d2e55}
  .err{background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 14px;color:#dc2626;font-size:13px;margin-bottom:18px}
</style>
</head>
<body>
<div class="login-card">
  <h1>VALT Admin</h1>
  <p>Sign in to access the student panel</p>
  <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="admin_login" value="1">
    <label>Username</label>
    <input type="text" name="username" required autofocus>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit" class="btn">Sign In</button>
  </form>
</div>
</body>
</html>
    <?php
    exit;
}

// ─── Logged in ───────────────────────────────────────────────────────────────
$pdo  = getDB();
$page = $_GET['page'] ?? 'students';
$flash = $_GET['flash'] ?? '';

// ── POST actions ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['student_db_id'];
        $pdo->prepare("DELETE FROM valt_students WHERE id = ?")->execute([$id]);
        header('Location: index.php?page=students&flash=deleted');
        exit;
    }

    if ($action === 'edit') {
        $id = (int)$_POST['student_db_id'];
        $pdo->prepare("UPDATE valt_students SET
            first_name=?, last_name=?, grade=?, province=?, city=?,
            school_name=?, whatsapp_number=?, email=?, programme_interest=?
            WHERE id=?")->execute([
            trim($_POST['first_name']),
            trim($_POST['last_name']),
            (int)$_POST['grade'],
            trim($_POST['province']),
            trim($_POST['city']),
            trim($_POST['school_name']),
            trim($_POST['whatsapp_number']),
            trim($_POST['email']),
            trim($_POST['programme_interest']),
            $id,
        ]);
        header('Location: index.php?page=students&flash=edited');
        exit;
    }
}

// ── Shared queries ────────────────────────────────────────────────────────────
$total      = $pdo->query("SELECT COUNT(*) FROM valt_students")->fetchColumn();
$todayCount = $pdo->query("SELECT COUNT(*) FROM valt_students WHERE DATE(registered_at)=CURDATE()")->fetchColumn();
$provinces  = $pdo->query("SELECT DISTINCT province FROM valt_students ORDER BY province")->fetchAll(PDO::FETCH_COLUMN);

// Official module list
$modules = [
    'VALT 101 - Financial Literacy',
    'VALT 102 - Entrepreneurship',
    'VALT 103 - Emotional Intelligence',
    'VALT 104 - Career Guidance',
    'VALT 105 - Health & Fitness',
    'VALT 106 - Internship Programme',
];

// Count students per official module
$modCounts = [];
foreach ($modules as $mod) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM valt_students WHERE programme_interest LIKE ?");
    $stmt->execute(['%' . $mod . '%']);
    $modCounts[$mod] = (int)$stmt->fetchColumn();
}

// ── Dashboard data ────────────────────────────────────────────────────────────
$regByDay = $byGrade = $byProvince = [];
if ($page === 'dashboard') {
    $regByDay   = $pdo->query("SELECT DATE(registered_at) as day, COUNT(*) as cnt FROM valt_students WHERE registered_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY day ORDER BY day")->fetchAll(PDO::FETCH_ASSOC);
    $byGrade    = $pdo->query("SELECT grade, COUNT(*) as cnt FROM valt_students GROUP BY grade ORDER BY grade")->fetchAll(PDO::FETCH_ASSOC);
    $byProvince = $pdo->query("SELECT province, COUNT(*) as cnt FROM valt_students GROUP BY province ORDER BY cnt DESC LIMIT 9")->fetchAll(PDO::FETCH_ASSOC);
}

// ── Modules page data ─────────────────────────────────────────────────────────
$selectedMod = '';
$modStudents = [];
if ($page === 'modules') {
    $selectedMod = $_GET['mod'] ?? '';
    if ($selectedMod && in_array($selectedMod, $modules)) {
        $stmt = $pdo->prepare("SELECT * FROM valt_students WHERE programme_interest LIKE ? ORDER BY first_name, last_name");
        $stmt->execute(['%' . $selectedMod . '%']);
        $modStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ── Students filter & fetch ───────────────────────────────────────────────────
$where = []; $params = [];
$filterGrade    = $_GET['grade']    ?? '';
$filterProvince = $_GET['province'] ?? '';
$filterModule   = $_GET['module']   ?? '';
$filterSearch   = trim($_GET['q']   ?? '');

if ($filterGrade    !== '') { $where[] = 'grade = ?';                 $params[] = (int)$filterGrade; }
if ($filterProvince !== '') { $where[] = 'province = ?';              $params[] = $filterProvince; }
if ($filterModule   !== '') { $where[] = 'programme_interest LIKE ?'; $params[] = '%' . $filterModule . '%'; }
if ($filterSearch   !== '') {
    $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_id LIKE ?)';
    $like = '%' . $filterSearch . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$orderBy  = 'ORDER BY registered_at DESC';

if (isset($_GET['export'])) {
    $stmt = $pdo->prepare("SELECT * FROM valt_students $whereSql $orderBy");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="valt_students_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        if ($rows) { fputcsv($out, array_keys($rows[0])); foreach ($rows as $r) fputcsv($out, $r); }
        fclose($out); exit;
    }
    if ($_GET['export'] === 'excel') {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="valt_students_' . date('Y-m-d') . '.xls"');
        $out = fopen('php://output', 'w');
        if ($rows) { fputcsv($out, array_keys($rows[0]), "\t"); foreach ($rows as $r) fputcsv($out, $r, "\t"); }
        fclose($out); exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM valt_students $whereSql $orderBy");
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentUrl = 'index.php?page=students&' . http_build_query(array_filter([
    'grade' => $filterGrade, 'province' => $filterProvince,
    'module' => $filterModule, 'q' => $filterSearch,
]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — VALT Academy</title>
<?php if ($page === 'dashboard'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php endif; ?>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#f0f4f8;font-family:Arial,sans-serif;color:#2c3e50;font-size:14px}

  /* Sidebar */
  .sidebar{position:fixed;top:0;left:0;width:220px;height:100vh;background:#0a2342;padding:28px 0;display:flex;flex-direction:column;z-index:200}
  .sidebar .logo{padding:0 24px 24px;border-bottom:1px solid rgba(255,255,255,.1)}
  .sidebar .logo h2{color:#fff;font-size:18px;font-weight:700}
  .sidebar .logo p{color:#2a9d8f;font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-top:3px}
  .sidebar nav{flex:1;padding:16px 0}
  .sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 24px;color:rgba(255,255,255,.65);text-decoration:none;font-size:13px;font-weight:500;transition:all .2s}
  .sidebar nav a.active,.sidebar nav a:hover{background:rgba(42,157,143,.15);color:#2a9d8f}
  .sidebar nav .nav-section{padding:10px 24px 4px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.3)}
  .sidebar .logout{padding:0 16px 20px}
  .sidebar .logout form button{width:100%;padding:10px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.7);border-radius:6px;font-size:13px;cursor:pointer}
  .sidebar .logout form button:hover{background:rgba(255,255,255,.15)}

  /* Main */
  .main{margin-left:220px;padding:32px}

  /* Flash */
  .flash{padding:12px 18px;border-radius:8px;font-size:13px;font-weight:600;margin-bottom:20px}
  .flash.success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7}
  .flash.danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}

  /* Page header */
  .page-header{margin-bottom:24px}
  .page-header h1{font-size:22px;font-weight:700;color:#0a2342}
  .page-header p{color:#7a8ba0;font-size:13px;margin-top:4px}

  /* Stats */
  .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:28px}
  .stat-card{background:#fff;border-radius:12px;padding:22px 24px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
  .stat-card .num{font-size:32px;font-weight:800;color:#0a2342}
  .stat-card .lbl{font-size:12px;color:#7a8ba0;text-transform:uppercase;letter-spacing:.5px;margin-top:4px}

  /* Toolbar */
  .toolbar{display:flex;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap}
  .toolbar form{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .toolbar input,.toolbar select{padding:9px 12px;border:1.5px solid #dde3ec;border-radius:7px;font-size:13px;color:#2c3e50;background:#fff;outline:none}
  .toolbar input:focus,.toolbar select:focus{border-color:#2a9d8f}
  .btn-filter{padding:9px 20px;background:#0a2342;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer}
  .btn-clear{padding:9px 14px;background:transparent;border:1.5px solid #dde3ec;border-radius:7px;font-size:13px;color:#5a6a7a;text-decoration:none;cursor:pointer}
  .exports{margin-left:auto;display:flex;gap:8px}
  .exports a{padding:9px 16px;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none}
  .btn-csv{background:#27ae60;color:#fff}.btn-csv:hover{background:#219150}
  .btn-excel{background:#2980b9;color:#fff}.btn-excel:hover{background:#1f6391}
  .btn-print{background:#7f8c8d;color:#fff}.btn-print:hover{background:#636e72}

  /* Table */
  .table-wrap{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);overflow-x:auto}
  table{width:100%;border-collapse:collapse}
  thead th{background:#0a2342;color:#fff;padding:12px 14px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
  tbody tr:nth-child(even){background:#f8fafc}
  tbody tr:hover{background:#f0faf9}
  tbody td{padding:11px 14px;border-bottom:1px solid #eef1f5;font-size:13px;white-space:nowrap}
  .badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600}
  .badge-grade{background:#e8f4fd;color:#2980b9}
  .empty{text-align:center;padding:48px;color:#7a8ba0}
  .count-info{font-size:13px;color:#7a8ba0;margin-bottom:10px}

  /* Sortable */
  thead th.sortable{cursor:pointer;user-select:none}
  thead th.sortable:hover{background:#0d2e55}
  thead th .sort-icon{display:inline-block;margin-left:5px;opacity:.5;font-size:10px}
  thead th.sort-asc .sort-icon::after{content:'▲';opacity:1}
  thead th.sort-desc .sort-icon::after{content:'▼';opacity:1}
  thead th:not(.sort-asc):not(.sort-desc) .sort-icon::after{content:'⇅'}

  /* Action buttons */
  .btn-view{padding:4px 10px;background:#2a9d8f;color:#fff;border:none;border-radius:5px;font-size:11px;font-weight:600;cursor:pointer}
  .btn-view:hover{background:#21867a}
  .btn-module{padding:4px 10px;background:#6c3fc5;color:#fff;border:none;border-radius:5px;font-size:11px;font-weight:600;cursor:pointer}
  .btn-module:hover{background:#5a33a8}
  .btn-edit{padding:4px 10px;background:#e67e22;color:#fff;border:none;border-radius:5px;font-size:11px;font-weight:600;cursor:pointer}
  .btn-edit:hover{background:#ca6c1a}
  .btn-delete{padding:4px 10px;background:#e74c3c;color:#fff;border:none;border-radius:5px;font-size:11px;font-weight:600;cursor:pointer}
  .btn-delete:hover{background:#c0392b}
  .action-group{display:flex;gap:4px;align-items:center}

  /* Modals shared */
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center}
  .modal-overlay.open{display:flex}
  .modal-card{background:#fff;border-radius:14px;width:100%;max-width:560px;box-shadow:0 24px 64px rgba(0,0,0,.3);overflow:hidden;max-height:90vh;overflow-y:auto}

  /* View modal */
  .modal-header{background:#0a2342;color:#fff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between}
  .modal-header h2{font-size:16px;font-weight:700}
  .modal-header .modal-sid{font-size:11px;color:#2a9d8f;margin-top:3px}
  .modal-close{background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1}
  .modal-body{padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:14px 20px}
  .modal-field label{font-size:11px;font-weight:600;color:#7a8ba0;text-transform:uppercase;letter-spacing:.5px}
  .modal-field p{font-size:14px;color:#2c3e50;margin-top:3px;word-break:break-word}
  .modal-field.full{grid-column:1/-1}

  /* Edit modal */
  .edit-header{background:#e67e22;color:#fff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between}
  .edit-header h2{font-size:16px;font-weight:700}
  .edit-body{padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:14px 20px}
  .edit-field{display:flex;flex-direction:column;gap:5px}
  .edit-field.full{grid-column:1/-1}
  .edit-field label{font-size:11px;font-weight:600;color:#7a8ba0;text-transform:uppercase;letter-spacing:.5px}
  .edit-field input,.edit-field select,.edit-field textarea{padding:9px 12px;border:1.5px solid #dde3ec;border-radius:7px;font-size:13px;color:#2c3e50;outline:none;font-family:inherit}
  .edit-field input:focus,.edit-field select:focus,.edit-field textarea:focus{border-color:#e67e22}
  .edit-footer{padding:16px 24px;border-top:1px solid #eef1f5;display:flex;justify-content:flex-end;gap:10px}
  .btn-save{padding:9px 22px;background:#e67e22;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer}
  .btn-save:hover{background:#ca6c1a}
  .btn-cancel{padding:9px 16px;background:transparent;border:1.5px solid #dde3ec;border-radius:7px;font-size:13px;color:#5a6a7a;cursor:pointer}

  /* Delete modal */
  .delete-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1100;align-items:center;justify-content:center}
  .delete-overlay.open{display:flex}
  .delete-card{background:#fff;border-radius:14px;width:100%;max-width:400px;box-shadow:0 24px 64px rgba(0,0,0,.3);overflow:hidden}
  .delete-header{background:#e74c3c;color:#fff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between}
  .delete-header h2{font-size:16px;font-weight:700}
  .delete-body{padding:24px;text-align:center}
  .delete-body .delete-icon{font-size:40px;margin-bottom:12px}
  .delete-body p{color:#2c3e50;font-size:14px;line-height:1.6}
  .delete-body strong{color:#e74c3c}
  .delete-footer{padding:16px 24px;border-top:1px solid #eef1f5;display:flex;justify-content:flex-end;gap:10px}
  .btn-confirm-delete{padding:9px 22px;background:#e74c3c;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer}
  .btn-confirm-delete:hover{background:#c0392b}

  /* Module popup */
  .module-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1100;align-items:center;justify-content:center}
  .module-overlay.open{display:flex}
  .module-card{background:#fff;border-radius:14px;width:100%;max-width:420px;box-shadow:0 24px 64px rgba(0,0,0,.3);overflow:hidden}
  .module-header{background:#6c3fc5;color:#fff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between}
  .module-header h2{font-size:15px;font-weight:700}
  .module-student-name{font-size:11px;color:rgba(255,255,255,.7);margin-top:3px}
  .module-close{background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1}
  .module-body{padding:28px 24px}
  .module-body .module-icon{font-size:36px;margin-bottom:12px;text-align:center}
  .mod-list{list-style:none;display:flex;flex-direction:column;gap:8px;margin-top:8px}
  .mod-list li{background:#f3eeff;color:#5a33a8;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600}
  .module-label{font-size:11px;font-weight:600;color:#7a8ba0;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px}

  /* Dashboard */
  .dash-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:28px}
  .charts-row{display:grid;grid-template-columns:2fr 1fr;gap:18px;margin-bottom:18px}
  .charts-row2{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}
  .chart-card{background:#fff;border-radius:12px;padding:22px 24px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
  .chart-card h3{font-size:14px;font-weight:700;color:#0a2342;margin-bottom:16px}
  .chart-wrap{position:relative;height:220px}

  /* Modules page */
  .mod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-bottom:28px}
  .mod-tile{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.06);cursor:pointer;text-decoration:none;color:inherit;border:2px solid transparent;transition:all .2s}
  .mod-tile:hover,.mod-tile.active{border-color:#6c3fc5}
  .mod-tile .mod-name{font-size:13px;font-weight:700;color:#0a2342;margin-bottom:6px}
  .mod-tile .mod-count{font-size:28px;font-weight:800;color:#6c3fc5}
  .mod-tile .mod-lbl{font-size:11px;color:#7a8ba0;text-transform:uppercase;letter-spacing:.5px}

  @media print{
    .sidebar,.toolbar,.exports{display:none!important}
    .main{margin-left:0!important;padding:0!important}
    .stats,.dash-stats{display:none!important}
    body{background:#fff}
  }
</style>
</head>
<body>

<div class="sidebar">
  <div class="logo">
    <h2>VALT Academy</h2>
    <p>Admin Panel</p>
  </div>
  <nav>
    <div class="nav-section">Overview</div>
    <a href="index.php?page=dashboard" class="<?= $page==='dashboard'?'active':'' ?>">&#128200; Dashboard</a>
    <div class="nav-section">Students</div>
    <a href="index.php?page=students" class="<?= $page==='students'?'active':'' ?>">&#128100; All Students</a>
    <a href="index.php?page=modules"  class="<?= $page==='modules' ?'active':'' ?>">&#127979; By Module</a>
  </nav>
  <div class="logout">
    <form method="post">
      <button type="submit" name="logout">&#x2192; Sign Out</button>
    </form>
  </div>
</div>

<div class="main">

<?php if ($flash === 'deleted'): ?>
<div class="flash danger">Student has been permanently deleted.</div>
<?php elseif ($flash === 'edited'): ?>
<div class="flash success">Student details updated successfully.</div>
<?php endif; ?>

<!-- ═══════════════════ DASHBOARD ═══════════════════ -->
<?php if ($page === 'dashboard'): ?>

<div class="page-header">
  <h1>Dashboard</h1>
  <p>Overview of all VALT Academy registrations</p>
</div>

<?php
$thisWeek  = $pdo->query("SELECT COUNT(*) FROM valt_students WHERE registered_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$thisMonth = $pdo->query("SELECT COUNT(*) FROM valt_students WHERE MONTH(registered_at)=MONTH(CURDATE()) AND YEAR(registered_at)=YEAR(CURDATE())")->fetchColumn();
?>
<div class="dash-stats">
  <div class="stat-card"><div class="num"><?= number_format($total) ?></div><div class="lbl">Total Registrations</div></div>
  <div class="stat-card"><div class="num"><?= number_format($todayCount) ?></div><div class="lbl">Registered Today</div></div>
  <div class="stat-card"><div class="num"><?= number_format($thisWeek) ?></div><div class="lbl">This Week</div></div>
  <div class="stat-card"><div class="num"><?= number_format($thisMonth) ?></div><div class="lbl">This Month</div></div>
</div>

<div class="charts-row">
  <div class="chart-card">
    <h3>Registrations — Last 30 Days</h3>
    <div class="chart-wrap"><canvas id="chartLine"></canvas></div>
  </div>
  <div class="chart-card">
    <h3>By Grade</h3>
    <div class="chart-wrap"><canvas id="chartGrade"></canvas></div>
  </div>
</div>
<div class="charts-row2">
  <div class="chart-card">
    <h3>By Province</h3>
    <div class="chart-wrap"><canvas id="chartProvince"></canvas></div>
  </div>
  <div class="chart-card">
    <h3>Module Popularity</h3>
    <div class="chart-wrap"><canvas id="chartModules"></canvas></div>
  </div>
</div>

<script>
const palette = ['#2a9d8f','#e9c46a','#f4a261','#e76f51','#264653','#6c3fc5','#219150','#2980b9','#e67e22'];

// Line chart — registrations per day
(function(){
  <?php
    $days = []; $cnts = [];
    foreach ($regByDay as $r) { $days[] = date('d M', strtotime($r['day'])); $cnts[] = (int)$r['cnt']; }
    echo 'var lineDays=' . json_encode($days) . ';';
    echo 'var lineCnts=' . json_encode($cnts) . ';';
  ?>
  new Chart(document.getElementById('chartLine'), {
    type:'line',
    data:{labels:lineDays,datasets:[{label:'Registrations',data:lineCnts,borderColor:'#2a9d8f',backgroundColor:'rgba(42,157,143,.1)',tension:.3,fill:true,pointRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
  });
})();

// Bar chart — by grade
(function(){
  <?php
    $gl = []; $gc = [];
    foreach ($byGrade as $r) { $gl[] = 'Grade ' . $r['grade']; $gc[] = (int)$r['cnt']; }
    echo 'var gradeLabels=' . json_encode($gl) . ';';
    echo 'var gradeCnts=' . json_encode($gc) . ';';
  ?>
  new Chart(document.getElementById('chartGrade'), {
    type:'bar',
    data:{labels:gradeLabels,datasets:[{label:'Students',data:gradeCnts,backgroundColor:palette}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
  });
})();

// Doughnut — by province
(function(){
  <?php
    $pl = []; $pc = [];
    foreach ($byProvince as $r) { $pl[] = $r['province']; $pc[] = (int)$r['cnt']; }
    echo 'var provLabels=' . json_encode($pl) . ';';
    echo 'var provCnts=' . json_encode($pc) . ';';
  ?>
  new Chart(document.getElementById('chartProvince'), {
    type:'doughnut',
    data:{labels:provLabels,datasets:[{data:provCnts,backgroundColor:palette}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'right'}}}
  });
})();

// Horizontal bar — modules
(function(){
  <?php
    $ml = []; $mc = [];
    foreach ($modCounts as $name => $cnt) { $ml[] = $name; $mc[] = $cnt; }
    echo 'var modLabels=' . json_encode($ml) . ';';
    echo 'var modCnts=' . json_encode($mc) . ';';
  ?>
  new Chart(document.getElementById('chartModules'), {
    type:'bar',
    data:{labels:modLabels,datasets:[{label:'Students',data:modCnts,backgroundColor:palette}]},
    options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{stepSize:1}}}}
  });
})();
</script>

<!-- ═══════════════════ BY MODULE ═══════════════════ -->
<?php elseif ($page === 'modules'): ?>

<div class="page-header">
  <h1>By Module</h1>
  <p>Students grouped by their programme interest</p>
</div>

<div class="mod-grid">
  <?php foreach ($modCounts as $modName => $cnt): ?>
  <a href="index.php?page=modules&mod=<?= urlencode($modName) ?>" class="mod-tile <?= ($selectedMod === $modName ? 'active' : '') ?>">
    <div class="mod-name"><?= htmlspecialchars($modName) ?></div>
    <div class="mod-count"><?= $cnt ?></div>
    <div class="mod-lbl">Student<?= $cnt !== 1 ? 's' : '' ?></div>
  </a>
  <?php endforeach; ?>
  <?php if (empty($modCounts)): ?>
  <p style="color:#7a8ba0">No module data available yet.</p>
  <?php endif; ?>
</div>

<?php if ($selectedMod && $modStudents): ?>
<div class="page-header">
  <h1 style="font-size:17px"><?= htmlspecialchars($selectedMod) ?></h1>
  <p><?= count($modStudents) ?> student<?= count($modStudents)!==1?'s':'' ?> enrolled</p>
</div>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Student ID</th><th>Name</th><th>Grade</th><th>Province</th><th>City</th><th>School</th><th>WhatsApp</th><th>Email</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($modStudents as $s): ?>
      <?php $school = $s['school_name']==='Other' ? ($s['school_other']?:'Other') : $s['school_name']; ?>
      <tr>
        <td><strong><?= htmlspecialchars($s['student_id']) ?></strong></td>
        <td><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
        <td><span class="badge badge-grade">Gr <?= (int)$s['grade'] ?></span></td>
        <td><?= htmlspecialchars($s['province']) ?></td>
        <td><?= htmlspecialchars($s['city']) ?></td>
        <td><?= htmlspecialchars($school) ?></td>
        <td><?= htmlspecialchars($s['whatsapp_number']) ?></td>
        <td><?= htmlspecialchars($s['email']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php elseif ($selectedMod): ?>
<p style="color:#7a8ba0;padding:20px 0">No students found for this module.</p>
<?php endif; ?>

<!-- ═══════════════════ STUDENTS ═══════════════════ -->
<?php else: ?>

<div class="page-header">
  <h1>Registered Students</h1>
  <p>All students who have signed up through the VALT Academy registration form</p>
</div>

<div class="stats">
  <div class="stat-card"><div class="num"><?= number_format($total) ?></div><div class="lbl">Total Registrations</div></div>
  <div class="stat-card"><div class="num"><?= number_format($todayCount) ?></div><div class="lbl">Registered Today</div></div>
  <div class="stat-card"><div class="num"><?= count($students) ?></div><div class="lbl">Showing (filtered)</div></div>
</div>

<div class="toolbar">
  <form method="get">
    <input type="hidden" name="page" value="students">
    <input type="text" name="q" placeholder="Search name, email, ID..." value="<?= htmlspecialchars($filterSearch) ?>">
    <select name="grade">
      <option value="">All Grades</option>
      <?php foreach (range(8,12) as $g): ?>
      <option value="<?= $g ?>" <?= $filterGrade==$g?'selected':'' ?>>Grade <?= $g ?></option>
      <?php endforeach; ?>
    </select>
    <select name="province">
      <option value="">All Provinces</option>
      <?php foreach ($provinces as $prov): ?>
      <option value="<?= htmlspecialchars($prov) ?>" <?= $filterProvince===$prov?'selected':'' ?>><?= htmlspecialchars($prov) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="module">
      <option value="">All Modules</option>
      <?php foreach ($modules as $mod): ?>
      <option value="<?= htmlspecialchars($mod) ?>" <?= $filterModule===$mod?'selected':'' ?>><?= htmlspecialchars($mod) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-filter">Filter</button>
    <a href="index.php?page=students" class="btn-clear">Clear</a>
  </form>
  <div class="exports">
    <a href="<?= $currentUrl ?>&amp;export=csv"   class="btn-csv">&#x2B07; CSV</a>
    <a href="<?= $currentUrl ?>&amp;export=excel" class="btn-excel">&#x2B07; Excel</a>
    <a href="#" onclick="window.print()" class="btn-print">&#x1F5A8; Print / PDF</a>
  </div>
</div>

<p class="count-info"><?= count($students) ?> student<?= count($students)!==1?'s':'' ?> found</p>

<div class="table-wrap">
  <?php if ($students): ?>
  <table>
    <thead>
      <tr>
        <th class="sortable" data-col="0">Student ID<span class="sort-icon"></span></th>
        <th class="sortable" data-col="1">Name<span class="sort-icon"></span></th>
        <th class="sortable" data-col="2">Grade<span class="sort-icon"></span></th>
        <th class="sortable" data-col="3">Province<span class="sort-icon"></span></th>
        <th class="sortable" data-col="4">City<span class="sort-icon"></span></th>
        <th class="sortable" data-col="5">School<span class="sort-icon"></span></th>
        <th class="sortable" data-col="6">WhatsApp<span class="sort-icon"></span></th>
        <th class="sortable" data-col="7">Email<span class="sort-icon"></span></th>
        <th>Module</th>
        <th class="sortable" data-col="8">Registered<span class="sort-icon"></span></th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($students as $s): ?>
      <?php
        $school = $s['school_name']==='Other' ? ($s['school_other']?:'Other') : $s['school_name'];
        $fullName = $s['first_name'].' '.$s['last_name'];
        $viewData = json_encode([
          'student_id' => $s['student_id'],
          'name'       => $fullName,
          'grade'      => $s['grade'],
          'province'   => $s['province'],
          'city'       => $s['city'],
          'school'     => $school,
          'whatsapp'   => $s['whatsapp_number'],
          'email'      => $s['email'],
          'module'     => $s['programme_interest'] ?: '—',
          'registered' => date('d M Y, H:i', strtotime($s['registered_at'])),
        ]);
        $editData = json_encode([
          'db_id'      => $s['id'],
          'first_name' => $s['first_name'],
          'last_name'  => $s['last_name'],
          'grade'      => $s['grade'],
          'province'   => $s['province'],
          'city'       => $s['city'],
          'school'     => $school,
          'whatsapp'   => $s['whatsapp_number'],
          'email'      => $s['email'],
          'module'     => $s['programme_interest'] ?: '',
        ]);
        $modData = json_encode([
          'name'   => $fullName,
          'module' => $s['programme_interest'] ?: '—',
        ]);
      ?>
      <tr>
        <td><strong><?= htmlspecialchars($s['student_id']) ?></strong></td>
        <td><?= htmlspecialchars($fullName) ?></td>
        <td><span class="badge badge-grade">Gr <?= (int)$s['grade'] ?></span></td>
        <td><?= htmlspecialchars($s['province']) ?></td>
        <td><?= htmlspecialchars($s['city']) ?></td>
        <td><?= htmlspecialchars($school) ?></td>
        <td><?= htmlspecialchars($s['whatsapp_number']) ?></td>
        <td><?= htmlspecialchars($s['email']) ?></td>
        <td>
          <?php if ($s['programme_interest']): ?>
            <button class="btn-module" onclick="openModuleModal(<?= htmlspecialchars($modData, ENT_QUOTES) ?>)">&#128218; View</button>
          <?php else: ?>—<?php endif; ?>
        </td>
        <td><?= date('d M Y', strtotime($s['registered_at'])) ?></td>
        <td>
          <div class="action-group">
            <button class="btn-view"   onclick="openModal(<?= htmlspecialchars($viewData, ENT_QUOTES) ?>)">View</button>
            <button class="btn-edit"   onclick="openEditModal(<?= htmlspecialchars($editData, ENT_QUOTES) ?>)">Edit</button>
            <button class="btn-delete" onclick="openDeleteModal(<?= (int)$s['id'] ?>, '<?= htmlspecialchars(addslashes($fullName)) ?>')">Delete</button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty">No students found matching your filters.</div>
  <?php endif; ?>
</div>

<?php endif; ?>
</div><!-- /.main -->

<!-- ══ Module Popup ══ -->
<div class="module-overlay" id="moduleModal" onclick="if(event.target===this)closeModuleModal()">
  <div class="module-card">
    <div class="module-header">
      <div>
        <h2>Enrolled Module(s)</h2>
        <div class="module-student-name" id="mm-student-name"></div>
      </div>
      <button class="module-close" onclick="closeModuleModal()">&#x2715;</button>
    </div>
    <div class="module-body">
      <div class="module-icon">&#127979;</div>
      <div class="module-label">Programme Interest</div>
      <ul class="mod-list" id="mm-module-list"></ul>
    </div>
  </div>
</div>

<!-- ══ View Modal ══ -->
<div class="modal-overlay" id="studentModal" onclick="if(event.target===this)closeModal()">
  <div class="modal-card">
    <div class="modal-header">
      <div>
        <h2 id="modal-name"></h2>
        <div class="modal-sid" id="modal-sid"></div>
      </div>
      <button class="modal-close" onclick="closeModal()">&#x2715;</button>
    </div>
    <div class="modal-body">
      <div class="modal-field"><label>Grade</label><p id="modal-grade"></p></div>
      <div class="modal-field"><label>Province</label><p id="modal-province"></p></div>
      <div class="modal-field"><label>City</label><p id="modal-city"></p></div>
      <div class="modal-field"><label>School</label><p id="modal-school"></p></div>
      <div class="modal-field"><label>WhatsApp</label><p id="modal-whatsapp"></p></div>
      <div class="modal-field"><label>Email</label><p id="modal-email"></p></div>
      <div class="modal-field full"><label>Module</label><p id="modal-module"></p></div>
      <div class="modal-field full"><label>Registered</label><p id="modal-registered"></p></div>
    </div>
  </div>
</div>

<!-- ══ Edit Modal ══ -->
<div class="modal-overlay" id="editModal" onclick="if(event.target===this)closeEditModal()">
  <div class="modal-card">
    <div class="edit-header">
      <div><h2>Edit Student</h2></div>
      <button class="modal-close" onclick="closeEditModal()">&#x2715;</button>
    </div>
    <form method="post" action="index.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="student_db_id" id="edit-db-id">
      <div class="edit-body">
        <div class="edit-field"><label>First Name</label><input type="text" name="first_name" id="edit-first" required></div>
        <div class="edit-field"><label>Last Name</label><input type="text" name="last_name" id="edit-last" required></div>
        <div class="edit-field">
          <label>Grade</label>
          <select name="grade" id="edit-grade">
            <?php foreach (range(8,12) as $g): ?><option value="<?= $g ?>"><?= $g ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="edit-field"><label>Province</label><input type="text" name="province" id="edit-province" required></div>
        <div class="edit-field"><label>City</label><input type="text" name="city" id="edit-city" required></div>
        <div class="edit-field"><label>School</label><input type="text" name="school_name" id="edit-school" required></div>
        <div class="edit-field"><label>WhatsApp</label><input type="text" name="whatsapp_number" id="edit-whatsapp" required></div>
        <div class="edit-field"><label>Email</label><input type="email" name="email" id="edit-email" required></div>
        <div class="edit-field full"><label>Module(s)</label><input type="text" name="programme_interest" id="edit-module" placeholder="e.g. VALT 101 - Financial Literacy, VALT 102 - ..."></div>
      </div>
      <div class="edit-footer">
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
        <button type="submit" class="btn-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ Delete Confirm Modal ══ -->
<div class="delete-overlay" id="deleteModal" onclick="if(event.target===this)closeDeleteModal()">
  <div class="delete-card">
    <div class="delete-header">
      <h2>Delete Student</h2>
      <button class="modal-close" onclick="closeDeleteModal()">&#x2715;</button>
    </div>
    <div class="delete-body">
      <div class="delete-icon">&#x26A0;&#xFE0F;</div>
      <p>Are you sure you want to permanently delete<br><strong id="delete-name"></strong>?<br><br>This action cannot be undone.</p>
    </div>
    <form method="post" action="index.php">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="student_db_id" id="delete-db-id">
      <div class="delete-footer">
        <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
        <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
// ── View Modal ─────────────────────────────────────────────────────────────────
function openModal(d){
  document.getElementById('modal-name').textContent     = d.name;
  document.getElementById('modal-sid').textContent      = 'ID: '+d.student_id;
  document.getElementById('modal-grade').textContent    = 'Grade '+d.grade;
  document.getElementById('modal-province').textContent = d.province;
  document.getElementById('modal-city').textContent     = d.city;
  document.getElementById('modal-school').textContent   = d.school;
  document.getElementById('modal-whatsapp').textContent = d.whatsapp;
  document.getElementById('modal-email').textContent    = d.email;
  document.getElementById('modal-module').textContent   = d.module;
  document.getElementById('modal-registered').textContent = d.registered;
  document.getElementById('studentModal').classList.add('open');
}
function closeModal(){ document.getElementById('studentModal').classList.remove('open'); }

// ── Module Popup ───────────────────────────────────────────────────────────────
function openModuleModal(d){
  document.getElementById('mm-student-name').textContent = d.name;
  var list = document.getElementById('mm-module-list');
  list.innerHTML = '';
  d.module.split(',').map(function(m){ return m.trim(); }).filter(Boolean).forEach(function(m){
    var li = document.createElement('li'); li.textContent = m; list.appendChild(li);
  });
  document.getElementById('moduleModal').classList.add('open');
}
function closeModuleModal(){ document.getElementById('moduleModal').classList.remove('open'); }

// ── Edit Modal ─────────────────────────────────────────────────────────────────
function openEditModal(d){
  document.getElementById('edit-db-id').value    = d.db_id;
  document.getElementById('edit-first').value    = d.first_name;
  document.getElementById('edit-last').value     = d.last_name;
  document.getElementById('edit-grade').value    = d.grade;
  document.getElementById('edit-province').value = d.province;
  document.getElementById('edit-city').value     = d.city;
  document.getElementById('edit-school').value   = d.school;
  document.getElementById('edit-whatsapp').value = d.whatsapp;
  document.getElementById('edit-email').value    = d.email;
  document.getElementById('edit-module').value   = d.module;
  document.getElementById('editModal').classList.add('open');
}
function closeEditModal(){ document.getElementById('editModal').classList.remove('open'); }

// ── Delete Modal ───────────────────────────────────────────────────────────────
function openDeleteModal(id, name){
  document.getElementById('delete-db-id').value    = id;
  document.getElementById('delete-name').textContent = name;
  document.getElementById('deleteModal').classList.add('open');
}
function closeDeleteModal(){ document.getElementById('deleteModal').classList.remove('open'); }

// ── Escape key ─────────────────────────────────────────────────────────────────
document.addEventListener('keydown', function(e){
  if(e.key==='Escape'){ closeModal(); closeEditModal(); closeDeleteModal(); closeModuleModal(); }
});

// ── Sortable columns ───────────────────────────────────────────────────────────
(function(){
  var table = document.querySelector('table');
  if(!table) return;
  var tbody = table.querySelector('tbody');
  var headers = table.querySelectorAll('thead th.sortable');
  var sortCol = -1, sortAsc = true;
  headers.forEach(function(th){
    th.addEventListener('click', function(){
      var col = parseInt(th.dataset.col);
      if(sortCol===col){ sortAsc=!sortAsc; } else { sortCol=col; sortAsc=true; }
      headers.forEach(function(h){ h.classList.remove('sort-asc','sort-desc'); });
      th.classList.add(sortAsc?'sort-asc':'sort-desc');
      var rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort(function(a,b){
        var av = a.cells[col]?a.cells[col].textContent.trim():'';
        var bv = b.cells[col]?b.cells[col].textContent.trim():'';
        if(col===2){ av=parseInt(av.replace(/\D/g,''))||0; bv=parseInt(bv.replace(/\D/g,''))||0; return sortAsc?av-bv:bv-av; }
        return sortAsc?av.localeCompare(bv):bv.localeCompare(av);
      });
      rows.forEach(function(r){ tbody.appendChild(r); });
    });
  });
})();
</script>
</body>
</html>
