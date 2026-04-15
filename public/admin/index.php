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
        if (
            $_POST['username'] === ADMIN_USER &&
            $_POST['password'] === ADMIN_PASS
        ) {
            $_SESSION['valt_admin'] = true;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
    // Show login page
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

// ─── Logged in — handle exports first (before any HTML output) ───────────────
$pdo = getDB();

// ── Build filter SQL ──────────────────────────────────────────────────────────
$where   = [];
$params  = [];

$filterGrade    = $_GET['grade']    ?? '';
$filterProvince = $_GET['province'] ?? '';
$filterModule   = $_GET['module']   ?? '';
$filterSearch   = trim($_GET['q']   ?? '');

if ($filterGrade    !== '') { $where[] = 'grade = ?';                $params[] = (int)$filterGrade; }
if ($filterProvince !== '') { $where[] = 'province = ?';             $params[] = $filterProvince; }
if ($filterModule   !== '') { $where[] = 'programme_interest = ?';   $params[] = $filterModule; }
if ($filterSearch   !== '') {
    $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR student_id LIKE ?)';
    $like = '%' . $filterSearch . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$orderBy  = 'ORDER BY registered_at DESC';

// ── CSV Export ────────────────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $stmt = $pdo->prepare("SELECT * FROM valt_students $whereSql $orderBy");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="valt_students_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    if ($rows) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

// ── Excel (TSV) Export ────────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $stmt = $pdo->prepare("SELECT * FROM valt_students $whereSql $orderBy");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="valt_students_' . date('Y-m-d') . '.xls"');
    $out = fopen('php://output', 'w');
    if ($rows) {
        fputcsv($out, array_keys($rows[0]), "\t");
        foreach ($rows as $row) fputcsv($out, $row, "\t");
    }
    fclose($out);
    exit;
}

// ── Fetch stats ───────────────────────────────────────────────────────────────
$total       = $pdo->query("SELECT COUNT(*) FROM valt_students")->fetchColumn();
$todayCount  = $pdo->query("SELECT COUNT(*) FROM valt_students WHERE DATE(registered_at) = CURDATE()")->fetchColumn();
$provinces   = $pdo->query("SELECT DISTINCT province FROM valt_students ORDER BY province")->fetchAll(PDO::FETCH_COLUMN);
$modules     = $pdo->query("SELECT DISTINCT programme_interest FROM valt_students WHERE programme_interest IS NOT NULL AND programme_interest != '' ORDER BY programme_interest")->fetchAll(PDO::FETCH_COLUMN);

// ── Fetch students ────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM valt_students $whereSql $orderBy");
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentUrl = 'index.php?' . http_build_query(array_filter([
    'grade'    => $filterGrade,
    'province' => $filterProvince,
    'module'   => $filterModule,
    'q'        => $filterSearch,
]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Admin — VALT Academy</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:#f0f4f8;font-family:Arial,sans-serif;color:#2c3e50;font-size:14px}

  /* Sidebar */
  .sidebar{position:fixed;top:0;left:0;width:220px;height:100vh;background:#0a2342;padding:28px 0;display:flex;flex-direction:column}
  .sidebar .logo{padding:0 24px 28px;border-bottom:1px solid rgba(255,255,255,.1)}
  .sidebar .logo h2{color:#fff;font-size:18px;font-weight:700}
  .sidebar .logo p{color:#2a9d8f;font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-top:3px}
  .sidebar nav{flex:1;padding:20px 0}
  .sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 24px;color:rgba(255,255,255,.7);text-decoration:none;font-size:13px;font-weight:500;transition:all .2s}
  .sidebar nav a.active,.sidebar nav a:hover{background:rgba(42,157,143,.15);color:#2a9d8f}
  .sidebar .logout{padding:0 16px 20px}
  .sidebar .logout form button{width:100%;padding:10px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.7);border-radius:6px;font-size:13px;cursor:pointer}
  .sidebar .logout form button:hover{background:rgba(255,255,255,.15)}

  /* Main */
  .main{margin-left:220px;padding:32px}

  /* Header */
  .page-header{margin-bottom:28px}
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
  .toolbar .btn-filter{padding:9px 20px;background:#0a2342;color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:600;cursor:pointer}
  .toolbar .btn-clear{padding:9px 14px;background:transparent;border:1.5px solid #dde3ec;border-radius:7px;font-size:13px;color:#5a6a7a;text-decoration:none;cursor:pointer}
  .exports{margin-left:auto;display:flex;gap:8px}
  .exports a{padding:9px 16px;border-radius:7px;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s}
  .btn-csv{background:#27ae60;color:#fff}
  .btn-csv:hover{background:#219150}
  .btn-excel{background:#2980b9;color:#fff}
  .btn-excel:hover{background:#1f6391}
  .btn-print{background:#7f8c8d;color:#fff}
  .btn-print:hover{background:#636e72}

  /* Table */
  .table-wrap{background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);overflow:hidden}
  table{width:100%;border-collapse:collapse}
  thead th{background:#0a2342;color:#fff;padding:12px 14px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
  tbody tr:nth-child(even){background:#f8fafc}
  tbody tr:hover{background:#f0faf9}
  tbody td{padding:11px 14px;border-bottom:1px solid #eef1f5;font-size:13px;white-space:nowrap}
  .badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;text-transform:uppercase}
  .badge-grade{background:#e8f4fd;color:#2980b9}
  .empty{text-align:center;padding:48px;color:#7a8ba0}
  .count-info{font-size:13px;color:#7a8ba0;margin-bottom:10px}

  /* Sortable headers */
  thead th.sortable{cursor:pointer;user-select:none}
  thead th.sortable:hover{background:#0d2e55}
  thead th .sort-icon{display:inline-block;margin-left:5px;opacity:.5;font-size:10px}
  thead th.sort-asc .sort-icon::after{content:'▲';opacity:1}
  thead th.sort-desc .sort-icon::after{content:'▼';opacity:1}
  thead th:not(.sort-asc):not(.sort-desc) .sort-icon::after{content:'⇅'}

  /* View button */
  .btn-view{padding:4px 10px;background:#2a9d8f;color:#fff;border:none;border-radius:5px;font-size:11px;font-weight:600;cursor:pointer}
  .btn-view:hover{background:#21867a}

  /* Modal */
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center}
  .modal-overlay.open{display:flex}
  .modal-card{background:#fff;border-radius:14px;width:100%;max-width:560px;box-shadow:0 24px 64px rgba(0,0,0,.3);overflow:hidden}
  .modal-header{background:#0a2342;color:#fff;padding:20px 24px;display:flex;align-items:center;justify-content:space-between}
  .modal-header h2{font-size:16px;font-weight:700}
  .modal-header .modal-sid{font-size:11px;color:#2a9d8f;margin-top:3px}
  .modal-close{background:none;border:none;color:#fff;font-size:20px;cursor:pointer;line-height:1}
  .modal-body{padding:24px;display:grid;grid-template-columns:1fr 1fr;gap:14px 20px}
  .modal-field label{font-size:11px;font-weight:600;color:#7a8ba0;text-transform:uppercase;letter-spacing:.5px}
  .modal-field p{font-size:14px;color:#2c3e50;margin-top:3px;word-break:break-word}
  .modal-field.full{grid-column:1/-1}

  @media print{
    .sidebar,.toolbar,.exports{display:none!important}
    .main{margin-left:0!important;padding:0!important}
    .stats{display:none!important}
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
    <a href="index.php" class="active">&#x1F464; Students</a>
  </nav>
  <div class="logout">
    <form method="post">
      <button type="submit" name="logout">&#x2192; Sign Out</button>
    </form>
  </div>
</div>

<div class="main">

  <div class="page-header">
    <h1>Registered Students</h1>
    <p>All students who have signed up through the VALT Academy registration form</p>
  </div>

  <div class="stats">
    <div class="stat-card">
      <div class="num"><?= number_format($total) ?></div>
      <div class="lbl">Total Registrations</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= number_format($todayCount) ?></div>
      <div class="lbl">Registered Today</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= count($students) ?></div>
      <div class="lbl">Showing (filtered)</div>
    </div>
  </div>

  <div class="toolbar">
    <form method="get">
      <input type="text" name="q" placeholder="Search name, email, ID..." value="<?= htmlspecialchars($filterSearch) ?>">
      <select name="grade">
        <option value="">All Grades</option>
        <?php foreach (range(8, 12) as $g): ?>
          <option value="<?= $g ?>" <?= $filterGrade == $g ? 'selected' : '' ?>>Grade <?= $g ?></option>
        <?php endforeach; ?>
      </select>
      <select name="province">
        <option value="">All Provinces</option>
        <?php foreach ($provinces as $prov): ?>
          <option value="<?= htmlspecialchars($prov) ?>" <?= $filterProvince === $prov ? 'selected' : '' ?>><?= htmlspecialchars($prov) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="module">
        <option value="">All Modules</option>
        <?php foreach ($modules as $mod): ?>
          <option value="<?= htmlspecialchars($mod) ?>" <?= $filterModule === $mod ? 'selected' : '' ?>><?= htmlspecialchars($mod) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-filter">Filter</button>
      <a href="index.php" class="btn-clear">Clear</a>
    </form>
    <div class="exports">
      <a href="<?= $currentUrl ?>&amp;export=csv"   class="exports btn-csv">&#x2B07; CSV</a>
      <a href="<?= $currentUrl ?>&amp;export=excel" class="exports btn-excel">&#x2B07; Excel</a>
      <a href="#" onclick="window.print()" class="exports btn-print">&#x1F5A8; Print / PDF</a>
    </div>
  </div>

  <p class="count-info"><?= count($students) ?> student<?= count($students) !== 1 ? 's' : '' ?> found</p>

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
          <th class="sortable" data-col="8">Module<span class="sort-icon"></span></th>
          <th class="sortable" data-col="9">Registered<span class="sort-icon"></span></th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <?php
          $school = $s['school_name'] === 'Other' ? ($s['school_other'] ?: 'Other') : $s['school_name'];
          $modalData = json_encode([
            'student_id'   => $s['student_id'],
            'name'         => $s['first_name'] . ' ' . $s['last_name'],
            'grade'        => $s['grade'],
            'province'     => $s['province'],
            'city'         => $s['city'],
            'school'       => $school,
            'whatsapp'     => $s['whatsapp_number'],
            'email'        => $s['email'],
            'module'       => $s['programme_interest'] ?: '—',
            'registered'   => date('d M Y, H:i', strtotime($s['registered_at'])),
          ]);
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($s['student_id']) ?></strong></td>
          <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
          <td><span class="badge badge-grade">Gr <?= (int)$s['grade'] ?></span></td>
          <td><?= htmlspecialchars($s['province']) ?></td>
          <td><?= htmlspecialchars($s['city']) ?></td>
          <td><?= htmlspecialchars($school) ?></td>
          <td><?= htmlspecialchars($s['whatsapp_number']) ?></td>
          <td><?= htmlspecialchars($s['email']) ?></td>
          <td><?= htmlspecialchars($s['programme_interest'] ?: '—') ?></td>
          <td><?= date('d M Y', strtotime($s['registered_at'])) ?></td>
          <td><button class="btn-view" onclick="openModal(<?= htmlspecialchars($modalData, ENT_QUOTES) ?>)">View</button></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty">No students found matching your filters.</div>
    <?php endif; ?>
  </div>

</div>

<!-- Student Detail Modal -->
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

<script>
// ── Student Modal ──────────────────────────────────────────────────────────────
function openModal(data) {
  document.getElementById('modal-name').textContent       = data.name;
  document.getElementById('modal-sid').textContent        = 'ID: ' + data.student_id;
  document.getElementById('modal-grade').textContent      = 'Grade ' + data.grade;
  document.getElementById('modal-province').textContent   = data.province;
  document.getElementById('modal-city').textContent       = data.city;
  document.getElementById('modal-school').textContent     = data.school;
  document.getElementById('modal-whatsapp').textContent   = data.whatsapp;
  document.getElementById('modal-email').textContent      = data.email;
  document.getElementById('modal-module').textContent     = data.module;
  document.getElementById('modal-registered').textContent = data.registered;
  document.getElementById('studentModal').classList.add('open');
}
function closeModal() {
  document.getElementById('studentModal').classList.remove('open');
}
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeModal(); });

// ── Sortable columns ───────────────────────────────────────────────────────────
(function(){
  var table = document.querySelector('table');
  if (!table) return;
  var tbody = table.querySelector('tbody');
  var headers = table.querySelectorAll('thead th.sortable');
  var sortCol = -1, sortAsc = true;

  headers.forEach(function(th) {
    th.addEventListener('click', function(){
      var col = parseInt(th.dataset.col);
      if (sortCol === col) { sortAsc = !sortAsc; }
      else { sortCol = col; sortAsc = true; }

      headers.forEach(function(h){ h.classList.remove('sort-asc','sort-desc'); });
      th.classList.add(sortAsc ? 'sort-asc' : 'sort-desc');

      var rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort(function(a, b){
        var av = (a.cells[col] ? a.cells[col].textContent.trim() : '');
        var bv = (b.cells[col] ? b.cells[col].textContent.trim() : '');
        if (col === 2) {
          av = parseInt(av.replace(/\D/g,'')) || 0;
          bv = parseInt(bv.replace(/\D/g,'')) || 0;
          return sortAsc ? av - bv : bv - av;
        }
        return sortAsc ? av.localeCompare(bv) : bv.localeCompare(av);
      });
      rows.forEach(function(r){ tbody.appendChild(r); });
    });
  });
})();
</script>
</body>
</html>
