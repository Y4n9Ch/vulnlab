<?php
// 基础路径穿越
$content = null;
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // 漏洞：直接拼接路径，无过滤
    $path = __DIR__ . '/../../uploads/' . $file;
    $content = @file_get_contents($path);
    if ($content === false) {
        $content = '文件不存在：' . h($path);
    } elseif (escapedBaseDir($path, __DIR__ . '/../../uploads')) {
        renderChallengeSuccess($challenge, '路径穿越读到了 uploads 目录之外的文件');
    }
}
?>

<p>文件下载（存在目录遍历漏洞）</p>
<p>通过URL参数指定文件名下载文件，未过滤路径分隔符。</p>

<div style="margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['cid'] ?? '') ?>&file=test.txt" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">下载示例文件</a>
</div>

<?php if ($content !== null): ?>
    <div class="result-box"><?= h($content) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    当前路径：uploads/<?= h($_GET['file'] ?? '') ?><br>
    试试：?file=../config/database.php 或 ?file=../sql/init.sql
</p>
