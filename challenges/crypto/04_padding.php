<?php
// Padding Oracle攻击
$output = null;
$key = 'SuperSecretKey12';
$iv = 'FixedIVVector1234';

function encrypt($plaintext) {
    global $key, $iv;
    return base64_encode(openssl_encrypt($plaintext, 'AES-128-CBC', $key, 0, $iv));
}

function decrypt($ciphertext) {
    global $key, $iv;
    $decoded = base64_decode($ciphertext);
    $result = openssl_decrypt($decoded, 'AES-128-CBC', $key, 0, $iv);
    return $result;
}

if (isset($_POST['encrypt'])) {
    $plaintext = $_POST['plaintext'] ?? '';
    $output = "密文: " . encrypt($plaintext);
}

if (isset($_POST['decrypt'])) {
    $ciphertext = $_POST['ciphertext'] ?? '';
    $result = decrypt($ciphertext);
    if ($result === false) {
        // 漏洞：解密失败时返回不同错误信息
        $output = "解密失败：填充格式错误！";
    } else {
        $output = "明文: " . $result;
    }
}
?>

<p>加解密工具（Padding Oracle攻击）</p>
<p>解密失败时返回不同错误信息，可利用Padding Oracle解密任意密文。</p>

<form method="POST">
    <label>明文加密</label>
    <input type="text" name="plaintext" placeholder="输入明文" value="<?= h($_POST['plaintext'] ?? '') ?>">
    <button type="submit" name="encrypt" value="1" class="btn btn-primary">加 密</button>
</form>

<form method="POST" style="margin-top:0.5rem;">
    <label>密文解密</label>
    <input type="text" name="ciphertext" placeholder="输入密文" value="<?= h($_POST['ciphertext'] ?? '') ?>">
    <button type="submit" name="decrypt" value="1" class="btn btn-primary">解 密</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">Padding Oracle攻击：</strong>
    <br>1. 修改密文的最后一个字节
    <br>2. 发送修改后的密文，观察错误信息
    <br>3. 如果返回"填充错误"，说明填充无效
    <br>4. 逐字节爆破，还原明文
</div>
