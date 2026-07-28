<?php
// EXISTS布尔盲注
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = ''; $found = false;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE EXISTS (SELECT * FROM users_info WHERE id = '$id')";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; $found = true; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
}
?>
<p>EXISTS布尔盲注</p>
<p>EXISTS子查询中存在注入点。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['id'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<div class="result-box">查询结果: <?= $found ? '找到数据' : '未找到数据' ?></div>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT * FROM users_info WHERE EXISTS (SELECT * FROM users_info WHERE id = '<strong>$id</strong>')
    <br><strong>提示:</strong> EXISTS注入: ?id=1' OR EXISTS(SELECT * FROM users_info WHERE username='admin') --+
</div>
