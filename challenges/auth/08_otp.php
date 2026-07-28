<?php
// 验证码爆破
session_start();
$output = null;

if (!isset($_SESSION['otp'])) {
    $_SESSION['otp'] = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $_SESSION['otp_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'] ?? '';

    // 漏洞：无速率限制，无错误次数限制
    if ($otp === $_SESSION['otp']) {
        $output = "验证码正确！登录成功！";
        unset($_SESSION['otp']);
    } else {
        $output = "验证码错误！";
        // 不重新生成验证码（漏洞）
    }
}
?>

<p>两步验证（验证码爆破）</p>
<p>4位数字验证码，无速率限制，可暴力枚举（10000种可能）。</p>

<form method="POST">
    <label>验证码（4位数字）</label>
    <input type="text" name="otp" placeholder="输入验证码" maxlength="4" pattern="[0-9]{4}">
    <button type="submit" class="btn btn-primary">验 证</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:0.5rem; font-size:0.85rem;">
    当前验证码: <strong><?= $_SESSION['otp'] ?></strong>（仅供练习）
</p>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">爆破方法：</strong>
    <br>1. 使用Burp Intruder对0000-9999进行枚举
    <br>2. 4位数字只有10000种组合，几分钟即可爆破完
    <br>3. 正确实现应限制错误次数或使用TOTP
</div>
