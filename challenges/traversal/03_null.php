<?php
// 截断绕过
$content = null;
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // 漏洞：后缀强制添加，但存在空字节截断（PHP < 5.3.4）
    $path = __DIR__ . '/../../uploads/' . $file . '.html';
    $content = @file_get_contents($path);
    if ($content === false) {
        $content = '文件不存在：' . h($path);
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
    绕过（PHP &lt; 5.3.4）：?file=../../../config/database.php%00
    <br>空字节 %00 会截断后面的 .html
</p>
