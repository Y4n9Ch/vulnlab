<?php
// 类型混淆漏洞
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $input_hash = $_POST['hash'] ?? '';

    // 模拟密码哈希
    $correct_hash = md5('secret123');

    // 漏洞：松散比较
    if ($input_hash == $correct_hash) {
        if ($input_hash !== $correct_hash) {
            renderChallengeSuccess($challenge, '松散比较被类型差异绕过');
        }
        $output = "密码验证成功！";
    } else {
        $output = "密码错误！哈希值: {$correct_hash}";
    }

    // 另一个漏洞：类型混淆
    $code = $_POST['code'] ?? '';
    if ($code) {
        // 漏洞：松散比较绕过
        if ($code == 0) {
            $output .= "<br>验证码正确！（类型混淆）";
        }
    }
}
?>

<p>认证系统（类型混淆漏洞）</p>
<p>PHP松散比较（==）可导致类型混淆绕过。</p>

<form method="POST">
    <label>密码哈希</label>
    <input type="text" name="hash" placeholder="输入MD5哈希" value="<?= h($_POST['hash'] ?? '') ?>">
    <label>验证码</label>
    <input type="text" name="code" placeholder="输入验证码" value="<?= h($_POST['code'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">验 证</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">类型混淆：</strong>
    <br>"0e123" == "0e456" → true（科学计数法都等于0）
    <br>"abc" == 0 → true（字符串转数字）
    <br>应使用 === 严格比较
    <br>MD5以0e开头的值：240610708, QLTHNDT7090
</div>
