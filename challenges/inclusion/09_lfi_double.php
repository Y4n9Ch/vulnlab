<?php
// 双重编码绕过文件包含
$output = null;
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    // 过滤 ../
    $page = str_replace('../', '', $page);
    // 漏洞：对已解码的内容再次解码
    $page = urldecode($page);
    $file = "pages/" . $page;
    $content = @file_get_contents($file);
    if ($content !== false) {
        $output = $content;
    } else {
        $output = "无法读取文件";
    }
}
?>

<p>页面加载（双重编码绕过）</p>
<p>过滤 ../ 后又进行URL解码，可使用双重编码绕过。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>页面路径</label>
    <input type="text" name="page" placeholder="页面名" value="<?= h($_GET['page'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">加 载</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    双重编码绕过：
    <br>?page=%252e%252e%252f%252e%252e%252fetc%252fpasswd
    <br>%25 是 % 的编码，解码后变成 %2e%2e%2f → ../
</p>
