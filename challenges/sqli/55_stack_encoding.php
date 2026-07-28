<?php
// 堆叠+字符集注入
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
<p>堆叠+字符集注入</p>
<p>支持多语句执行，可修改字符集辅助注入。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['id'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box">
    <table><tr><th>ID</th><th>用户名</th><th>邮箱</th></tr>
    <?php foreach($output as $row): ?>
    <tr><td><?= h($row['id']) ?></td><td><?= h($row['username']) ?></td><td><?= h($row['email']) ?></td></tr>
    <?php endforeach; ?></table>
</div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>提示:</strong> 堆叠+字符集: ?id=1';SET NAMES gbk;SELECT * FROM users_info WHERE id='1
</div>
