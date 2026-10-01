<?php
// 注释符绕过
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    // 过滤注释符
    $id = str_replace(['--', '#', '/**/'], '', $id);
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if ($output && is_string($_GET['cid']) && preg_match('/[\'"]/', $_GET['cid'])) {
        renderChallengeSuccess($challenge, '不依赖注释符、直接闭合引号的注入方式生效，查询返回数据');
    }
}
?>
<p>注释符绕过</p>
<p>过滤了常见注释符，使用其他方式闭合。</p>
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
    <strong>过滤规则:</strong> 替换 --, #, /**/ 为空
    <br><strong>提示:</strong> 使用 %00 或引号闭合: ?id=1' OR '1'='1
</div>
