<?php
// 截断绕过
$content = null;
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // 漏洞：后缀强制添加，但存在空字节截断（PHP < 5.3.4）
    $path = __DIR__ . '/../../uploads/' . $file . '.html';
    try {
        $content = @file_get_contents($path);
    } catch (ValueError $e) {
        // PHP 8 对含空字节的路径抛出 ValueError，这里模拟旧版行为避免整页崩溃
        $content = false;
    }
    if ($content === false) {
        $content = '文件不存在：' . h($path);
    } elseif (escapedBaseDir($path, __DIR__ . '/../../uploads')) {
        renderChallengeSuccess($challenge, '空字节截断了强制后缀，读取到了目录之外的文件');
    }
}
?>

<p>文件下载（截断绕过）</p>
<p>后端强制添加 .html 后缀，但在旧版PHP中可使用空字节截断。</p>

<?php if ($content !== null): ?>
    <div class="result-box"><?= h($content) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    后端逻辑：$path = 'uploads/' . $file . '.html'<br>
    绕过（PHP &lt; 5.3.4）：?file=../config/database.php%00
    <br>空字节 %00 会截断后面的 .html
</p>
