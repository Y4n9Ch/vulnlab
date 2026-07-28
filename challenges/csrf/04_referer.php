<?php
// Referer校验绕过CSRF
session_start();
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'];

    // 只检查Referer是否包含当前域名
    if (strpos($referer, $host) !== false) {
        if (isset($_POST['email'])) {
            $success = true;
        }
    } else {
        $error = 'Referer验证失败！';
    }
}
?>

<p>修改邮箱（Referer校验CSRF）</p>
<p>服务器仅检查Referer头是否包含当前域名，可通过构造包含域名的Referer绕过。</p>

<form method="POST">
    <label>新邮箱地址</label>
    <input type="email" name="email" placeholder="new@example.com" required>
    <button type="submit" class="btn btn-primary">修 改</button>
</form>

<?php if ($success): ?>
    <div class="result-box" style="border-color:var(--success);">
        邮箱修改成功！
        <br>当前Referer: <?= h($_SERVER['HTTP_REFERER'] ?? '无') ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="result-box" style="border-color:var(--danger);"><?= $error ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">验证逻辑：</strong>
    <br>仅检查 Referer 头是否包含当前域名字符串
    <br><br>
    <span style="color:var(--text-muted);">
        绕过方法：在攻击者服务器创建页面，域名中包含目标域名
        <br>例如：http://attacker.com/victim.com/csrf.html
        <br>或在子域名：http://victim.com.attacker.com/csrf.html
    </span>
</div>
