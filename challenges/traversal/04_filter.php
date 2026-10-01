<?php
// 过滤绕过
$content = null;
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // 漏洞：过滤不严格
    $file = str_replace('../', '', $file);
    $path = __DIR__ . '/../../uploads/' . $file;
    $content = @file_get_contents($path);
    if ($content === false) {
        $content = '文件不存在：' . h($path);
    } elseif (escapedBaseDir($path, __DIR__ . '/../../uploads')) {
        renderChallengeSuccess($challenge, '过滤规则被绕过，读取到了目录之外的文件');
    }
}
?>

<p>文件下载（目录遍历过滤绕过）</p>
<p>后端过滤了 ../ 但只替换一次。</p>

<?php if ($content !== null): ?>
    <div class="result-box"><?= h($content) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤规则：str_replace('../', '', $file)<br>
    绕过：双写 ....// → 删除后变成 ../
</p>
