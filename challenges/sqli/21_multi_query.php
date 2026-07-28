<?php
// 多语句执行注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // 按分号拆分，模拟多语句执行
        $statements = array_filter(array_map('trim', explode(';', $sql)), 'strlen');
        foreach ($statements as $i => $stmt_sql) {
            if (empty($stmt_sql)) continue;
            $isLast = ($i === count($statements) - 1);
            if (preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)/i', $stmt_sql)) {
                $stmt = $db->query($stmt_sql);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($isLast) $output = $rows;
            } else {
                $db->exec($stmt_sql);
            }
        }
        if ($output === null && isset($rows)) $output = $rows;
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
}
?>
<p>多语句执行注入</p>
<p>后端支持多语句执行，可使用分号分隔执行多条SQL。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['id'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box">
    <table><tr><th>ID</th><th>用户名</th><th>邮箱</th><th>角色</th></tr>
    <?php foreach($output as $row): ?>
    <tr><td><?= h($row['id']) ?></td><td><?= h($row['username']) ?></td><td><?= h($row['email']) ?></td><td><?= h($row['role']) ?></td></tr>
    <?php endforeach; ?></table>
</div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>提示:</strong> 使用分号执行多条语句: ?id=1; SELECT database()
</div>
