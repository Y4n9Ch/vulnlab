<?php
// HEX编码绕过
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    // 过滤引号和关键字
    $id = str_replace("'", '', $id);
    $id = preg_replace('/select/i', '', $id);
    $id = preg_replace('/union/i', '', $id);
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE username = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if ($output && is_string($id) && preg_match('/0x[0-9a-f]{4,}|char\s*\(/i', $id)) {
        renderChallengeSuccess($challenge, 'HEX编码字符串绕过引号与关键字过滤，查询返回了目标数据');
    }
}
?>
<p>HEX编码绕过</p>
<p>过滤了引号和关键字，使用十六进制编码绕过。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>输入用户名</label>
    <input type="text" name="id" placeholder="输入用户名" value="<?= h($_GET['cid'] ?? 'admin') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>过滤规则:</strong> 替换引号和select/union为空
    <br><strong>提示:</strong> HEX编码: 0x61646D696E 代替 'admin', 宽字节: %bf%27
</div>
