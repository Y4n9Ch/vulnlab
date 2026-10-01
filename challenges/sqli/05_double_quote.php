<?php
// 双引号+括号字符型注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = ('$id')";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if ($output && is_string($id) && preg_match('/\'/', $id) && preg_match('/\)|\b(or|and|union)\b/i', $id)) {
        renderChallengeSuccess($challenge, '闭合引号与括号后注入生效，查询返回了数据');
    }
}
?>
<p>括号闭合字符型注入</p>
<p>SQL使用 ('$id') 方式包裹参数，需要闭合括号和引号。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['cid'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL语句:</strong> SELECT * FROM users_info WHERE id = ('<strong>$id</strong>')
    <br><strong>提示:</strong> 闭合括号和引号: ?id=1') --+
</div>
