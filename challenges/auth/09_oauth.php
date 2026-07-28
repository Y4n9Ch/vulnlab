<?php
// OAuth回调劫持
$output = null;
$client_id = 'vuln_app_123';
$redirect_uri = $_GET['redirect_uri'] ?? 'http://localhost/callback';

if (isset($_GET['code'])) {
    // 模拟获取access_token
    $code = $_GET['code'];
    $output = "收到授权码: {$code}<br>";
    $output .= "正在用授权码换取access_token...<br>";
    $output .= "Token: access_" . md5($code . time());
}
?>

<p>OAuth登录（OAuth回调劫持）</p>
<p>OAuth的redirect_uri参数未严格校验，可劫持授权码。</p>

<div style="margin-bottom:1rem;">
    <strong>当前redirect_uri:</strong> <?= h($redirect_uri) ?>
</div>

<a href="?id=<?= h($_GET['id'] ?? '') ?>&redirect_uri=http://localhost/callback&code=AUTH_CODE_<?= rand(1000, 9999) ?>" class="btn btn-primary">模拟授权回调</a>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">OAuth劫持：</strong>
    <br>1. 构造恶意授权链接，redirect_uri指向攻击者服务器
    <br>2. 诱导用户点击授权
    <br>3. 用户授权后，授权码发送到攻击者服务器
    <br>4. 攻击者用授权码获取用户token
</div>
