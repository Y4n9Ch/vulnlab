<?php
// OR/AND绕过
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    // 过滤OR和AND
    $id = preg_replace('/or/i', '', $id);
    $id = preg_replace('/and/i', '', $id);
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if ($output && ((is_string($_GET['cid']) && preg_match('/oorr|aandd/i', $_GET['cid'])) || (is_string($id) && preg_match('/\|\||&&/', $id)))) {
        renderChallengeSuccess($challenge, '双写或逻辑运算符绕过OR/AND过滤，条件注入生效');
    }
}
?>
<p>OR/AND绕过</p>
<p>过滤了OR和AND关键字，使用替代写法。</p>
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
    <strong>过滤规则:</strong> 替换 or, and 为空（不区分大小写）
    <br><strong>提示:</strong> 双写: oorr, aandd 或使用 ||, && 代替
</div>
