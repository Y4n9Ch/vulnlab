<?php
// Session固定
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    if ($user === 'admin' && $pass === 'admin123') {
        // 漏洞：登录后不重新生成Session ID
        $_SESSION['loggedin'] = true;
        $_SESSION['role'] = 'admin';
        $_SESSION['auth03_user'] = (string) $user;
        renderChallengeSuccess($challenge, '登录后 Session 标识未轮换，可被固定利用');
        $msg = '<span style="color:var(--accent);">登录成功！Session ID 未改变。</span>';
        $msg .= '<br>当前Session ID：<code>' . h(session_id()) . '</code>';
    } else {
        $msg = '<span style="color:var(--danger);">登录失败</span>';
    }
}
?>

<p>用户登录（Session固定漏洞）</p>
<p>登录后Session ID不变，攻击者可诱导用户使用已知的Session ID登录。</p>

<div style="background:var(--bg-secondary); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1rem;">
    <span style="color:var(--text-secondary);">当前Session ID：</span>
    <code style="color:var(--accent);"><?= h(session_id()) ?></code>
</div>

<?php renderLoginStatus('auth03_user'); ?>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="admin" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="text" name="password" placeholder="admin123" value="<?= h($_POST['password'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    攻击步骤：
    <br>1. 获取一个Session ID（访问页面即可）
    <br>2. 诱导目标使用该Session ID（通过URL参数 ?PHPSESSID=xxx）
    <br>3. 目标登录后，攻击者使用相同的Session ID获得登录状态
</p>
