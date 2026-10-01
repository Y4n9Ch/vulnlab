<?php
// 路径截断文件包含
$output = null;
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    // 只检查是否以.php结尾
    if (substr($page, -4) !== '.php') {
        $page .= '.php';
    }
    // 漏洞：路径截断（PHP < 5.3.4）
    $file = "pages/" . $page;
    if (file_exists($file)) {
        $output = file_get_contents($file);
        if (escapedBaseDir($file, getcwd() . '/pages')) {
            renderChallengeSuccess($challenge, '强制后缀的路径检查被绕过，包含越出了页面目录');
        }
    } else {
        $output = "文件不存在: {$file}";
    }
}
?>

<p>页面加载（路径截断文件包含）</p>
<p>自动添加.php后缀，可利用路径截断或长字符串绕过。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>页面名称</label>
    <input type="text" name="page" placeholder="例如：home" value="<?= h($_GET['page'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">加 载</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    路径截断绕过：
    <br>?page=../../../../etc/passwd%00
    <br>?page=../../../../etc/passwd/././././（大量./使路径超长）
</p>
