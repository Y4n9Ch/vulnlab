<?php
// HAVING注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['having'])) {
    $having = $_GET['having'];
    $db = getVulnDB();
    $sql = "SELECT username, email, role FROM users_info GROUP BY username HAVING $having";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
}
?>
<p>HAVING注入</p>
<p>HAVING子句参数可控，可泄露列名和数据。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>HAVING条件</label>
    <input type="text" name="having" placeholder="HAVING条件" value="<?= h($_GET['having'] ?? '1=1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?> 角色:<?= h($row['role']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT ... GROUP BY username HAVING <strong>$having</strong>
    <br><strong>提示:</strong> HAVING报错泄露列名: ?having=1 AND (SELECT 1 FROM(SELECT COUNT(*),CONCAT(version(),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)
</div>
