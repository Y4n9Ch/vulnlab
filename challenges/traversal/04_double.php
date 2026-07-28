<?php
// 双写绕过目录遍历
$output = null;
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // 过滤 ../ 替换为空
    $file = str_replace('../', '', $file);
    // 漏洞：只替换一次，双写可绕过
    $path = "files/" . $file;
    $content = @file_get_contents($path);
    if ($content !== false) {
        $output = $content;
    } else {
        $output = "文件不存在: {$path}";
    }
}
?>

<p>文件下载（双写绕过目录遍历）</p>
<p>过滤 ../ 替换为空，但只替换一次，可使用双写绕过。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>文件名</label>
    <input type="text" name="file" placeholder="输入文件名" value="<?= h($_GET['file'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">下 载</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    双写绕过：
    <br>....// — 过滤后变成 ../
    <br>..././ — 过滤后变成 ../
    <br>..\/ — 混合路径分隔符
</p>
