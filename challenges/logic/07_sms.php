<?php
// 短信验证码绕过
session_start();
$output = null;

if (!isset($_SESSION['sms_code'])) {
    $_SESSION['sms_code'] = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['sms_send_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send') {
        $phone = $_POST['phone'] ?? '';
        $_SESSION['sms_code'] = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['sms_send_time'] = time();
        $_SESSION['sms_phone'] = $phone;
        $output = "验证码已发送到 {$phone}，验证码: " . $_SESSION['sms_code'];
    } elseif ($action === 'verify') {
        $code = $_POST['code'] ?? '';

        // 漏洞：验证码使用后未清除，可重复使用
        if ($code === $_SESSION['sms_code']) {
            renderChallengeSuccess($challenge, '验证码通过后未失效，被重复使用');
            $output = "验证成功！绑定手机号: " . ($_SESSION['sms_phone'] ?? '');
            // 漏洞：未清除验证码
            // unset($_SESSION['sms_code']);
        } else {
            $output = "验证码错误！";
        }
    }
}
?>

<p>手机绑定（短信验证码绕过）</p>
<p>短信验证码可重复使用，且无速率限制。</p>

<form method="POST">
    <input type="hidden" name="action" value="send">
    <label>手机号码</label>
    <input type="text" name="phone" placeholder="输入手机号" value="<?= h($_SESSION['sms_phone'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">发送验证码</button>
</form>

<form method="POST" style="margin-top:0.5rem;">
    <input type="hidden" name="action" value="verify">
    <label>验证码</label>
    <input type="text" name="code" placeholder="输入6位验证码" maxlength="6">
    <button type="submit" class="btn btn-primary">验 证</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:0.5rem; font-size:0.85rem;">
    当前验证码: <strong><?= $_SESSION['sms_code'] ?? '未生成' ?></strong>（仅供练习）
</p>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">绕过方法：</strong>
    <br>1. 验证码可重复使用（绑定多个手机号）
    <br>2. 无发送频率限制（短信轰炸）
    <br>3. 验证码未过期
</div>
