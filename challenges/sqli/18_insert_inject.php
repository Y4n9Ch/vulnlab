<?php
// INSERT语句注入
require_once __DIR__ . '/../../config/database.php';
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $db = getVulnDB();
    $sql = "INSERT INTO users_info (username, password, email) VALUES ('$username', '$password', '$email')";
    try {
        $db->exec($sql);
        $output = "注册成功！SQL: " . $sql;
    } catch (PDOException $e) { $output = "SQL错误: " . $e->getMessage(); }
    if (is_string($output) && strpos($output, '注册成功') === 0 && is_string($username) && preg_match('/\'\s*\)|\)\s*,\s*\(/', $username)) {
        renderChallengeSuccess($challenge, 'VALUES子句被闭合改写，INSERT注入成功写入数据');
    }
}
?>
<p>INSERT注入</p>
<p>用户注册功能，INSERT语句中存在注入。</p>
<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="password" name="password" placeholder="密码">
    <label>邮箱</label>
    <input type="text" name="email" placeholder="邮箱" value="<?= h($_POST['email'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">注 册</button>
</form>
<?php if ($output): ?><div class="result-box"><?= $output ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> INSERT INTO users_info (username, password, email) VALUES ('<strong>$username</strong>', ...)
    <br><strong>提示:</strong> 在username中注入: admin'), ( 'hacker','pass','hacker@evil.com' --+
</div>
