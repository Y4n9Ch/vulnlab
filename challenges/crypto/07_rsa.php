<?php
// RSA小公钥指数攻击
$output = null;

// 漏洞：使用小公钥指数e=3
$e = 3;
$n = 3233; // 极小的n（仅用于演示）
$d = 2753;

function rsa_encrypt($m) {
    global $e, $n;
    return pow($m, $e) % $n;
}

function rsa_decrypt($c) {
    global $d, $n;
    return pow($c, $d) % $n;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'encrypt') {
        $num = intval($_POST['number'] ?? 0);
        if ($num > 0 && $num < $n) {
            $output = "密文: " . rsa_encrypt($num);
        } else {
            $output = "数字必须在 1-{$n} 之间";
        }
    } elseif ($action === 'decrypt') {
        $c = intval($_POST['cipher'] ?? 0);
        $output = "明文: " . rsa_decrypt($c);
    }
}
?>

<p>RSA加密（小公钥指数攻击）</p>
<p>使用e=3的小公钥指数，当明文较小时可直接开立方根。</p>

<form method="POST">
    <input type="hidden" name="action" value="encrypt">
    <label>明文（数字）</label>
    <input type="number" name="number" placeholder="输入数字" value="<?= h($_POST['number'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">加 密</button>
</form>

<form method="POST" style="margin-top:0.5rem;">
    <input type="hidden" name="action" value="decrypt">
    <label>密文</label>
    <input type="number" name="cipher" placeholder="输入密文" value="<?= h($_POST['cipher'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">解 密</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">小指数攻击：</strong>
    <br>当 e=3 且 m^3 < n 时
    <br>密文 c = m^3，直接开立方根得到明文
    <br>m = cbrt(c)
</div>
