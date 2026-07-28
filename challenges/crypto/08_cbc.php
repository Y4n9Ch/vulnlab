<?php
// CBC字节翻转攻击
$output = null;
$key = 'AES128SecretKey';
$iv = 'InitVector123456';

$cookie = ['role' => 'user', 'id' => '1001'];

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'set') {
        $data = json_encode($cookie);
        $encrypted = openssl_encrypt($data, 'AES-128-CBC', $key, 0, $iv);
        setcookie('profile', base64_encode($encrypted), time() + 3600, '/');
        $output = "Cookie已设置: " . base64_encode($encrypted);
    } elseif ($_GET['action'] === 'get') {
        if (isset($_COOKIE['profile'])) {
            $encrypted = base64_decode($_COOKIE['profile']);
            $decrypted = openssl_decrypt($encrypted, 'AES-128-CBC', $key, 0, $iv);
            if ($decrypted) {
                $output = "解密Cookie: {$decrypted}";
                $data = json_decode($decrypted, true);
                if ($data) {
                    $output .= "<br>角色: " . ($data['role'] ?? 'unknown');
                }
            } else {
                $output = "解密失败！";
            }
        } else {
            $output = "Cookie不存在";
        }
    }
}
?>

<p>用户Cookie（CBC字节翻转）</p>
<p>CBC模式加密Cookie，可修改密文使解密后明文改变。</p>

<div style="display:flex;gap:1rem;margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&action=set" class="btn btn-primary">设置Cookie</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&action=get" class="btn btn-primary">读取Cookie</a>
</div>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">CBC字节翻转：</strong>
    <br>CBC模式中，修改前一个密文块的第i字节
    <br>会影响当前明文块的第i字节
    <br>公式：C'[i] = C[i] XOR P[i] XOR desired_byte
    <br>可将 "user" 翻转为 "admin"
</div>
