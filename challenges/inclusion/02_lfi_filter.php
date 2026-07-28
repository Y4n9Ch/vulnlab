<?php
// LFI过滤绕过
$page = $_GET['page'] ?? '';
$content = null;

if ($page) {
    // 漏洞：过滤不严格
    $page = str_replace('../', '', $page); // 只替换一次
    $file = $page;
    if (file_exists($file)) {
        $content = file_get_contents($file);
    } else {
        $content = '文件不存在：' . $file;
    }
}
?>

<p>页面加载（LFI过滤绕过）</p>
<p>后端过滤了 ../ 路径穿越符，但只替换一次。</p>

<div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&page=pages/home.php" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">首页</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&page=pages/about.php" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">关于</a>
</div>

<?php if ($content): ?>
    <div class="result-box"><?= h($content) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤规则：str_replace('../', '', $page) — 只替换一次<br>
    绕过方法：双写 ....// → 删除 ../ 后变成 ../
    <br>或使用 URL编码：%2e%2e%2f
</p>
