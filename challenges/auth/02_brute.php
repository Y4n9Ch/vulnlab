<?php
// 暴力破解
$msg = null;
$attempts = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    // 无验证码、无频率限制
    $db = getVulnDB();
    $stmt = $db->prepare("SELECT * FROM users_info WHERE username = ? AND password = MD5(?)");
    $stmt->execute([$user, $pass]);
    if ($row = $stmt->fetch()) {
        renderChallengeSuccess($challenge, '无频率限制的登录接口被爆破命中');
        $_SESSION['auth04_user'] = (string) $row['username'];
        $msg = '<span style="color:var(--accent);">登录成功！</span>';
    } else {
        $msg = '<span style="color:var(--danger);">用户名或密码错误</span>';
    }
}
?>

<p>管理后台登录（暴力破解）</p>
<p>登录接口没有验证码和频率限制，可以进行暴力破解。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="text" name="password" placeholder="密码" value="<?= h($_POST['password'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php renderLoginStatus('auth04_user'); ?>
<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    该接口无任何防护：无验证码、无频率限制、无账号锁定。<br>
    使用 Burp Intruder 或 hydra 进行密码爆破。<br>
    常见密码字典：rockyou.txt、top1000.txt
</p>
