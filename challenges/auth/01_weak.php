<?php
// 弱口令
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    // 弱口令字典
    $weakPasswords = [
        'admin' => ['admin', 'admin123', '123456', 'password', 'root', 'admin888'],
        'root' => ['root', 'toor', 'root123', '123456'],
        'test' => ['test', 'test123', '123456'],
    ];
    if (isset($weakPasswords[$user]) && in_array($pass, $weakPasswords[$user])) {
        renderChallengeSuccess($challenge, '常见弱口令组合通过了认证');
        $_SESSION['auth01_user'] = (string) $user;
        $msg = '<span style="color:var(--accent);">登录成功！欢迎 ' . h($user) . '</span>';
    } else {
        $msg = '<span style="color:var(--danger);">用户名或密码错误</span>';
    }
}

// 登录状态条放在登录处理之后渲染，保证本次登录立即生效
renderLoginStatus('auth01_user');
?>

<p>后台登录（弱口令漏洞）</p>
<p>系统使用了常见的弱口令组合。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="text" name="password" placeholder="密码" value="<?= h($_POST['password'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    提示：试试常见的弱口令组合 admin/admin123、root/toor、test/test123
</p>
