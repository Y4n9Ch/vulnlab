<?php
// URL编码目录遍历
$output = null;
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // 过滤 ../
    if (strpos($file, '../') !== false) {
        $output = "不允许路径穿越！";
    } else {
        // 漏洞：先过滤后解码
        $file = urldecode($file);
        $path = "files/" . $file;
        $content = @file_get_contents($path);
        if ($content !== false) {
            $output = $content;
        } else {
            $output = "文件不存在: {$path}";
        }
    }
}
?>

<p>文件查看（URL编码目录遍历）</p>
<p>过滤 ../ 后再URL解码，可使用URL编码绕过。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>文件名</label>
    <input type="text" name="file" placeholder="输入文件名" value="<?= h($_GET['file'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">查 看</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    URL编码绕过：
    <br>%2e%2e%2f → ../
    <br>%252e%252e%252f → 双重编码
    <br>..%00/ → 空字节截断
</p>
