<?php
// Cookie伪造漏洞
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 简单验证
    if ($username === 'admin' && $password === 'admin123') {
        // 漏洞：Cookie使用弱编码（Base64）
        $cookie = base64_encode($username . ':' . $password);
        setcookie('auth', $cookie, time() + 3600, '/');
        $output = "登录成功！Cookie: {$cookie}";
    } elseif ($username && $password) {
        $output = "用户名或密码错误";
    }
}

// 检查Cookie
if (isset($_COOKIE['auth'])) {
    $decoded = base64_decode($_COOKIE['auth']);
    list($user, $pass) = explode(':', $decoded, 2);
    $output = "当前登录用户: {$user}";
}
?>

<p>登录系统（Cookie伪造）</p>
<p>Cookie使用Base64编码存储用户名密码，可直接解码伪造。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="用户名">
    <label>密码</label>
    <input type="password" name="password" placeholder="密码">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    Cookie伪造：
    <br>1. 获取正常用户的Cookie（Base64编码）
    <br>2. 解码得到 username:password
    <br>3. 修改为admin:admin123并重新编码
    <br>4. 替换Cookie即可冒充admin
</p>
