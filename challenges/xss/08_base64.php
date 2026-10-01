<?php
// Base64编码XSS
$output = null;
if (isset($_POST['data'])) {
    $data = $_POST['data'];
    // 漏洞：解码后直接输出
    $decoded = base64_decode($data);
    if ($decoded !== false) {
        $output = $decoded;
        if (is_string($decoded) && preg_match('/<script\b|\son\w+\s*=|javascript:/i', $decoded)) {
            renderChallengeSuccess($challenge, 'Base64 解码出的脚本内容被原样输出到页面');
        }
    } else {
        $output = '无效的Base64数据';
    }
}
?>

<p>Base64解码工具（Base64编码绕过XSS）</p>
<p>后端解码Base64后直接输出到页面，可绕过基于关键字的过滤。</p>

<form method="POST">
    <label>Base64数据</label>
    <input type="text" name="data" placeholder="输入Base64编码" value="<?= h($_POST['data'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">解 码</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    将XSS Payload进行Base64编码：
    <br>&lt;script&gt;alert(1)&lt;/script&gt; → PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==
    <br>WAF通常不会解码Base64内容。
</p>
