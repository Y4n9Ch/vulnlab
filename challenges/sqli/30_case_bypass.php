<?php
// 大小写绕过
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    // 过滤小写关键字
    $id = str_replace(['select', 'union', 'from', 'where', 'and', 'or'], '', $id);
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if ($output && is_string($id) && preg_match('/union|select/i', $id)) {
        renderChallengeSuccess($challenge, '大小写混写绕过小写关键字过滤，联合查询成功返回数据');
    }
}
?>
<p>大小写绕过</p>
<p>过滤了小写关键字，使用大小写混写绕过。</p>
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
    <strong>过滤规则:</strong> 替换 select, union, from, where, and, or 为空
    <br><strong>提示:</strong> 大小写绕过: SeLeCt, UnIoN, FrOm
</div>
