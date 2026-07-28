<?php
// UPDATE语句注入
require_once __DIR__ . '/../../config/database.php';
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $db = getVulnDB();
    $sql = "UPDATE users_info SET email = '$email' WHERE username = '$username'";
    try {
        $db->exec($sql);
        $output = "更新成功！SQL: " . $sql;
    } catch (PDOException $e) { $output = "SQL错误: " . $e->getMessage(); }
}
?>
<p>UPDATE注入</p>
<p>用户资料更新功能，email参数存在注入。</p>
<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="用户名" value="<?= h($_POST['username'] ?? 'admin') ?>">
    <label>新邮箱</label>
    <input type="text" name="email" placeholder="邮箱" value="<?= h($_POST['email'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">更 新</button>
</form>
<?php if ($output): ?><div class="result-box"><?= $output ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> UPDATE users_info SET email = '<strong>$email</strong>' WHERE username = '$username'
    <br><strong>提示:</strong> 在email中注入: test', password=hacked WHERE username='admin' --+
</div>
