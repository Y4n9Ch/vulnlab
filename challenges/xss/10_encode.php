<?php
// 编码绕过XSS
$output = null;
if (isset($_POST['input'])) {
    $input = $_POST['input'];
    // 过滤了 < > " ' 等字符
    $filtered = str_replace(['<', '>', '"', "'"], '', $input);
    // 但URL编码后的内容会被解码
    $decoded = urldecode($filtered);
    $output = $decoded;
    if (is_string($decoded) && preg_match('/<[a-zA-Z!\/]/', $decoded)) {
        renderChallengeSuccess($challenge, '被编码的标记在过滤后才解码并原样进入页面');
    }
}
?>

<p>输入展示（编码绕过XSS）</p>
<p>过滤了尖括号和引号，但URL解码发生在过滤之后。</p>

<form method="POST">
    <label>输入内容</label>
    <input type="text" name="input" placeholder="输入内容" value="<?= h($_POST['input'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">提 交</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤 &lt; &gt; " ' 后再URL解码。
    <br>利用：%253Cscript%253Ealert(1)%253C/script%253E
    <br>双重URL编码：过滤器看到 %253C（不是尖括号），解码后变成 %3C，再解码变成 &lt;。
</p>
