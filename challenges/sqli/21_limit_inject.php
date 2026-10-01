<?php
// LIMIT注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

$page = intval($_GET['page'] ?? 1);
$limit = $_GET['limit'] ?? '5';
$offset = ($page - 1) * 5;

$db = getVulnDB();
$sql = "SELECT * FROM users_info LIMIT $limit OFFSET $offset";
try {
    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll();
    if ($rows) { $output = $rows; } else { $error = '没有数据'; }
} catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
if ($output && is_string($limit) && preg_match('/\b(union|select|procedure|into|or|and)\b|--|#|\|\|/i', $limit)) {
    renderChallengeSuccess($challenge, 'LIMIT子句拼接成功执行，越过分页直接控制了返回内容');
}
?>
<p>LIMIT注入</p>
<p>LIMIT子句参数可控，可进行注入。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>每页数量</label>
    <input type="text" name="limit" placeholder="数量" value="<?= h($limit) ?>">
    <label>页码</label>
    <input type="number" name="page" value="<?= $page ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT * FROM users_info LIMIT <strong>$limit</strong> OFFSET $offset
    <br><strong>提示:</strong> LIMIT注入: ?limit=5 UNION SELECT 1,2,3,4
</div>
