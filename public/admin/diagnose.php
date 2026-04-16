<?php
if (($_GET['token'] ?? '') !== 'valt-diag-2026') {
    die('Missing token.');
}
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = getDB();
echo '<style>body{font-family:monospace;padding:24px;background:#f5f5f5} table{border-collapse:collapse;width:100%} td,th{border:1px solid #ccc;padding:6px 10px;text-align:left} th{background:#0a2342;color:#fff} .ok{color:green;font-weight:bold} .fail{color:red;font-weight:bold} h2{margin:20px 0 10px}</style>';
echo '<h1>VALT DB Diagnostics</h1>';

// 1. Connection
echo '<h2>1. Database Connection</h2>';
echo '<p class="ok">Connected to: ' . DB_NAME . ' on ' . DB_HOST . '</p>';

// 2. Table exists
echo '<h2>2. Table: valt_students</h2>';
try {
    $cols = $pdo->query("DESCRIBE valt_students")->fetchAll(PDO::FETCH_ASSOC);
    echo '<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
    foreach ($cols as $col) {
        $highlight = in_array($col['Field'], ['programme_interest','parent_guardian_name','parent_guardian_email']) ? ' style="background:#fffbe6"' : '';
        echo "<tr$highlight><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
    }
    echo '</table>';
} catch (Exception $e) {
    echo '<p class="fail">Table not found: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// 3. Check specific columns
echo '<h2>3. Critical Column Check</h2>';
$required = ['programme_interest','parent_guardian_name','parent_guardian_email'];
foreach ($required as $col) {
    $exists = false;
    foreach ($cols ?? [] as $c) {
        if ($c['Field'] === $col) { $exists = $c; break; }
    }
    if ($exists) {
        echo '<p class="ok">✔ ' . $col . ' — ' . $exists['Type'] . '</p>';
    } else {
        echo '<p class="fail">✘ ' . $col . ' — MISSING</p>';
    }
}

// 4. Recent PHP error log
echo '<h2>4. Recent Error Log</h2>';
$logFile = ini_get('error_log');
if ($logFile && file_exists($logFile)) {
    $lines = array_slice(file($logFile), -20);
    $relevant = array_filter($lines, fn($l) => str_contains($l, 'Registration') || str_contains($l, 'valt'));
    echo '<pre>' . htmlspecialchars(implode('', $relevant ?: array_slice($lines, -5))) . '</pre>';
} else {
    echo '<p>Error log not accessible at: ' . htmlspecialchars($logFile) . '</p>';
    echo '<p>Check cPanel → Errors for PHP error details.</p>';
}

echo '<br><p><a href="index.php">← Back to Admin</a></p>';
