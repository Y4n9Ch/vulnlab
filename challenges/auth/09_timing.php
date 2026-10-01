<?php
// 时序攻击绕过认证
$output = null;
$correct_user = 'admin';
$correct_pass = 'SuperSecretPassword123!';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $start = microtime(true);

    // 漏洞：逐字符比较，时间差异可泄露信息
    $user_ok = false;
    $pass_ok = false;

    // 用户名比较（快速失败）
    if (strlen($username) === strlen($correct_user)) {
        for ($i = 0; $i < strlen($username); $i++) {
            if ($username[$i] !== $correct_user[$i]) break;
            usleep(10000); // 10ms延迟
            if ($i === strlen($username) - 1) $user_ok = true;
        }
    }

    // 密码比较（逐字符延迟）
    if ($user_ok && strlen($password) === strlen($correct_pass)) {
        for ($i = 0; $i < strlen($password); $i++) {
            if ($password[$i] !== $correct_pass[$i]) break;
            usleep(50000); // 50ms延迟
            if ($i === strlen($password) - 1) $pass_ok = true;
        }
    }

    $elapsed = microtime(true) - $start;

    if ($user_ok && $pass_ok) {
        renderChallengeSuccess($challenge, '逐字符比较的认证被时序信息逐位还原');
        $output = "登录成功！";
    } else {
        $output = "登录失败（耗时: " . round($elapsed * 1000) . "ms）";
    }
}
?>

<p>登录系统（时序攻击）</p>
<p>认证使用逐字符比较，响应时间差异可泄露正确字符。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="password" name="password" placeholder="密码">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">时序攻击：</strong>
    <br>每猜对一个字符，响应时间增加约50ms
    <br>用Burp Intruder逐字符爆破
    <br>从a-z, 0-9逐个测试，选择耗时最长的字符
</div>
