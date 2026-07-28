<?php
// RC4弱加密
$output = null;
$key = 'weakkey';

function rc4_encrypt($data, $key) {
    $s = range(0, 255);
    $j = 0;
    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
        list($s[$i], $s[$j]) = array($s[$j], $s[$i]);
    }
    $i = $j = 0;
    $result = '';
    for ($k = 0; $k < strlen($data); $k++) {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;
        list($s[$i], $s[$j]) = array($s[$j], $s[$i]);
        $result .= $data[$k] ^ $s[($s[$i] + $s[$j]) % 256];
    }
    return base64_encode($result);
}

function rc4_decrypt($data, $key) {
    $data = base64_decode($data);
    return rc4_encrypt($data, $key); // RC4加解密相同
}

if (isset($_POST['encrypt'])) {
    $plaintext = $_POST['plaintext'] ?? '';
    $output = "密文: " . rc4_encrypt($plaintext, $key);
}

if (isset($_POST['decrypt'])) {
    $ciphertext = $_POST['ciphertext'] ?? '';
    $output = "明文: " . rc4_decrypt($ciphertext, $key);
}
?>

<p>加密通信（RC4弱加密）</p>
<p>RC4流密码已不再安全，存在多种攻击方法。</p>

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

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    RC4弱点：
    <br>1. 密钥流前几个字节存在偏差
    <br>2. 相同密钥加密多条消息可被破解
    <br>3. 已被TLS 1.3弃用
</p>
