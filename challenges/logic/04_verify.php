<?php
// 验证码绕过
$msg = null;
$captcha = null;

// 生成验证码（前端可预测）
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = rand(1000, 9999);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captchaInput = $_POST['captcha'] ?? '';

    // 漏洞：验证码验证后未清除，可重复使用
    if ($captchaInput == $_SESSION['captcha']) {
        if ($username === 'admin' && $password === 'admin123') {
            $msg = '<span style="color:var(--accent);">登录成功！</span>';
        } else {
            $msg = '<span style="color:var(--danger);">用户名或密码错误</span>';
            // 注意：验证码未清除！可以重复使用
        }
    } else {
        $msg = '<span style="color:var(--danger);">验证码错误</span>';
        $_SESSION['captcha'] = rand(1000, 9999); // 只在错误时更新
    }
}
?>

<p>登录验证码（验证码绕过）</p>
<p>验证码存在多个缺陷：可预测、验证后未失效。</p>

<div style="background:var(--bg-secondary); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1rem;">
    当前验证码：<code style="color:var(--accent);"><?= $_SESSION['captcha'] ?></code>
    <br><span style="font-size:0.75rem; color:var(--text-muted);">（正常情况下不应显示，这里方便练习）</span>
</div>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="admin" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="text" name="password" placeholder="admin123" value="<?= h($_POST['password'] ?? '') ?>">
    <label>验证码</label>
    <input type="text" name="captcha" placeholder="输入验证码" value="<?= h($_POST['captcha'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    缺陷：
    <br>1. 验证码在前端可见（可预测）
    <br>2. 验证码输入错误后才刷新，正确后不刷新（可重放）
    <br>3. 没有验证码过期机制
</p>
