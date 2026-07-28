<?php
// 宽字节注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // 使用addslashes转义（可被宽字节绕过）
    $id = addslashes($id);
    $db = getVulnDB();
    $db->exec("SET NAMES gbk");
    $sql = "SELECT * FROM users_info WHERE username = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
}
?>
<p>宽字节注入</p>
<p>使用addslashes转义，但GBK编码下可使用宽字节绕过。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>输入用户名</label>
    <input type="text" name="id" placeholder="输入用户名" value="<?= h($_GET['id'] ?? 'admin') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>防护:</strong> addslashes转义引号
    <br><strong>提示:</strong> 宽字节: %bf%27 (%bf与转义的\组合成一个GBK字符)
</div>
