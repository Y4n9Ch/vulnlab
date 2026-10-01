<?php
// 验证码与CSRF
session_start();
$output = null;

if (!isset($_SESSION['csrf_captcha'])) {
    $_SESSION['csrf_captcha'] = rand(1000, 9999);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $captcha = $_POST['captcha'] ?? '';
    if ($captcha == $_SESSION['csrf_captcha']) {
        if (isset($_POST['action'])) {
            renderChallengeSuccess($challenge, '验证后未失效的验证码被跨站重复使用');
            $output = '操作执行成功: ' . h($_POST['action']);
        }
        $_SESSION['csrf_captcha'] = rand(1000, 9999);
    } else {
        $output = '验证码错误！当前验证码: ' . $_SESSION['csrf_captcha'];
    }
}
?>

<p>敏感操作（验证码绕过CSRF）</p>
<p>操作需要验证码，但验证码可被自动化脚本识别或直接获取。</p>

<form method="POST">
    <label>验证码: <strong><?= $_SESSION['csrf_captcha'] ?></strong></label>
    <input type="text" name="captcha" placeholder="输入验证码">
    <input type="hidden" name="action" value="transfer_money">
    <button type="submit" class="btn btn-danger">确认转账</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    验证码绕过：
    <br>1. 验证码在页面源码中直接可见
    <br>2. 攻击者可先请求页面获取验证码，再提交表单
    <br>3. 真实场景中可用OCR识别简单验证码
</p>
