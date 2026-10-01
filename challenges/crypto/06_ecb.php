<?php
// ECB模式攻击
$msg = null;
$encrypted = null;

$key = '1234567890123456'; // 16字节AES密钥

function aes_ecb_encrypt($data, $key) {
    return base64_encode(openssl_encrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA));
}

function aes_ecb_decrypt($data, $key) {
    return openssl_decrypt(base64_decode($data), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'encrypt' && isset($_POST['plaintext'])) {
        // PKCS7 padding
        $pad = 16 - (strlen($_POST['plaintext']) % 16);
        $padded = $_POST['plaintext'] . str_repeat(chr($pad), $pad);
        $encrypted = base64_encode(openssl_encrypt($padded, 'AES-128-ECB', $key, OPENSSL_RAW_DATA));
        $msg = '<span style="color:var(--accent);">加密结果：' . h($encrypted) . '</span>';
    }
    if ($_POST['action'] === 'decrypt' && isset($_POST['ciphertext'])) {
        $decrypted = openssl_decrypt(base64_decode($_POST['ciphertext']), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        if ($decrypted !== false) {
            renderChallengeSuccess($challenge, 'ECB 模式的密文被无密钥信息辅助解开');
        }
        $msg = '<span style="color:var(--accent);">解密结果：' . h((string) $decrypted) . '</span>';
    }
}
?>

<p>AES-ECB加密（ECB模式攻击）</p>
<p>使用AES-ECB模式，相同明文块产生相同密文，可利用此特性攻击。</p>

<div style="margin-bottom:1rem;">
    <form method="POST">
        <input type="hidden" name="action" value="encrypt">
        <label>明文</label>
        <input type="text" name="plaintext" placeholder="输入明文" value="<?= h($_POST['plaintext'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">加 密</button>
    </form>
</div>

<div>
    <form method="POST">
        <input type="hidden" name="action" value="decrypt">
        <label>密文（Base64）</label>
        <input type="text" name="ciphertext" placeholder="输入密文" value="<?= h($_POST['ciphertext'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">解 密</button>
    </form>
</div>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    ECB模式特点：每个16字节块独立加密，相同明文 → 相同密文。
    <br>攻击方法：重排密文块顺序、替换已知明文对应的密文块。
</p>
