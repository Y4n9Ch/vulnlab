<?php
// 弱签名算法
$output = null;
$secret = 'my_secret_key';

function sign($data) {
    global $secret;
    // 漏洞：使用MD5作为签名
    return md5($secret . $data);
}

function verify($data, $signature) {
    return sign($data) === $signature;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'sign') {
        $data = $_POST['data'] ?? '';
        $output = "数据: {$data}<br>签名: " . sign($data);
    } elseif ($action === 'verify') {
        $data = $_POST['verify_data'] ?? '';
        $sig = $_POST['signature'] ?? '';
        if (verify($data, $sig)) {
            renderChallengeSuccess($challenge, '弱 MD5 签名被伪造并通过验证');
            $output = "签名验证成功！数据: {$data}";
        } else {
            $output = "签名验证失败！";
        }
    }
}

// 漏洞：可获取任意数据的签名
if (isset($_GET['sign'])) {
    $data = $_GET['sign'];
    $output = "签名: " . sign($data);
}
?>

<p>数据签名（弱签名算法）</p>
<p>使用MD5做签名，且可请求任意数据的签名。</p>

<form method="POST">
    <input type="hidden" name="action" value="sign">
    <label>数据签名</label>
    <input type="text" name="data" placeholder="输入数据" value="<?= h($_POST['data'] ?? '') ?>">
    <button type="submit" name="sign" value="1" class="btn btn-primary">签 名</button>
</form>

<form method="POST" style="margin-top:0.5rem;">
    <input type="hidden" name="action" value="verify">
    <label>验证数据</label>
    <input type="text" name="verify_data" placeholder="数据" value="<?= h($_POST['verify_data'] ?? '') ?>">
    <label>签名</label>
    <input type="text" name="signature" placeholder="签名" value="<?= h($_POST['signature'] ?? '') ?>">
    <button type="submit" name="verify" value="1" class="btn btn-primary">验 证</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    利用方法：
    <br>1. 请求 ?sign=admin 的签名
    <br>2. 使用该签名伪造管理员请求
    <br>应使用HMAC-SHA256等安全签名算法
</p>
