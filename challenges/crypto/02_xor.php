<?php
// XOR加密
$msg = null;
$encrypted = null;

$key = 'key123'; // 密钥（短且可预测）

function xor_encrypt($data, $key) {
    $result = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $result .= $data[$i] ^ $key[$i % strlen($key)];
    }
    return base64_encode($result);
}

function xor_decrypt($data, $key) {
    $data = base64_decode($data);
    $result = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $result .= $data[$i] ^ $key[$i % strlen($key)];
    }
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'encrypt' && isset($_POST['plaintext'])) {
        $encrypted = xor_encrypt($_POST['plaintext'], $key);
        $msg = '<span style="color:var(--accent);">加密结果：' . h($encrypted) . '</span>';
    }
    if ($_POST['action'] === 'decrypt' && isset($_POST['ciphertext'])) {
        $decrypted = xor_decrypt($_POST['ciphertext'], $key);
        $msg = '<span style="color:var(--accent);">解密结果：' . h($decrypted) . '</span>';
    }
}
?>

<p>XOR加密解密（密钥可预测）</p>
<p>使用XOR加密，密钥较短且可推测。</p>

<div style="margin-bottom:1rem;">
    <h4 style="color:var(--accent); margin-bottom:0.5rem;">加密</h4>
    <form method="POST">
        <input type="hidden" name="action" value="encrypt">
        <label>明文</label>
        <input type="text" name="plaintext" placeholder="输入明文" value="<?= h($_POST['plaintext'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">加 密</button>
    </form>
</div>

<div>
    <h4 style="color:var(--accent); margin-bottom:0.5rem;">解密</h4>
    <form method="POST">
        <input type="hidden" name="action" value="decrypt">
        <label>密文（Base64）</label>
        <input type="text" name="ciphertext" placeholder="输入Base64密文" value="<?= h($_POST['ciphertext'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">解 密</button>
    </form>
</div>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    如果知道明文的一部分，可以用 C XOR P = K 恢复密钥。
    <br>例如，如果知道明文以 "flag" 开头，密文前4字节 XOR "flag" = 密钥前4字节。
</p>
