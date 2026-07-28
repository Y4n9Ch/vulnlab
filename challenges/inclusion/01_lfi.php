<?php
// 本地文件包含
$page = $_GET['page'] ?? '';
$content = null;

if ($page) {
    // 漏洞：直接包含用户指定的文件
    $file = $page;
    if (file_exists($file)) {
        $content = file_get_contents($file);
    } else {
        $content = '文件不存在：' . $file;
    }
}
?>

<p>页面加载功能（存在本地文件包含漏洞）</p>
<p>通过URL参数加载本地文件，未限制路径。</p>

<div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&page=pages/home.php" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">首页</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&page=pages/about.php" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">关于</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&page=pages/contact.php" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">联系</a>
</div>

<?php if ($content): ?>
    <div class="result-box"><?= h($content) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    当前参数：page=<?= h($page) ?><br>
    试试：?page=../../../../etc/passwd 或 ?page=../../../../windows/win.ini
</p>
