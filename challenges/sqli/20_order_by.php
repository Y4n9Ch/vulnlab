<?php
// ORDER BY注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

$sort = $_GET['sort'] ?? 'id';
$allowed_cols = ['id', 'username', 'email', 'role'];
if (!in_array($sort, $allowed_cols)) {
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info ORDER BY $sort";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        $output = $rows;
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
} else {
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info ORDER BY $sort";
    $stmt = $db->query($sql);
    $output = $stmt->fetchAll();
}
if (!in_array($sort, $allowed_cols, true) && is_string($sort) && $output && empty($error)) {
    renderChallengeSuccess($challenge, 'ORDER BY子句拼接自定义排序字段成功执行，排序注入成立');
}
?>
<p>ORDER BY注入</p>
<p>排序字段直接拼接到SQL中，可在ORDER BY子句注入。</p>
<div style="display:flex;gap:0.5rem;margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['cid'] ?? '') ?>&sort=id" class="btn btn-primary">按ID</a>
    <a href="?id=<?= h($_GET['cid'] ?? '') ?>&sort=username" class="btn btn-primary">按用户名</a>
    <a href="?id=<?= h($_GET['cid'] ?? '') ?>&sort=email" class="btn btn-primary">按邮箱</a>
</div>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>自定义排序字段</label>
    <input type="text" name="sort" placeholder="排序字段" value="<?= h($sort) ?>">
    <button type="submit" class="btn btn-primary">排 序</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT * FROM users_info ORDER BY <strong>$sort</strong>
    <br><strong>提示:</strong> ORDER BY注入: ?sort=id AND (SELECT 1 FROM(SELECT COUNT(*),CONCAT(version(),FLOOR(RAND(0)*2))x FROM information_schema.tables GROUP BY x)a)
</div>
