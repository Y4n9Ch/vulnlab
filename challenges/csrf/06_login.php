<?php
// 登录CSRF
session_start();
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // 模拟登录（无CSRF Token）
    if ($username && $password) {
        renderChallengeSuccess($challenge, '无防护的登录接口被跨站绑定到攻击者账号');
        $_SESSION['csrf_login_user'] = $username;
        $output = "已登录为: {$username}";
    }
}
?>

<p>用户登录（登录CSRF）</p>
<p>登录表单没有CSRF保护，攻击者可构造恶意页面让用户以攻击者账号登录。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="password" name="password" placeholder="密码">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php renderLoginStatus('csrf_login_user'); ?>
<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    登录CSRF攻击场景：
    <br>1. 攻击者让用户以攻击者账号登录
    <br>2. 用户在攻击者账号下操作（如绑定支付信息）
    <br>3. 攻击者随后接管该账号的所有关联数据
</p>
