<?php
// 通配符目录遍历
$output = null;
if (isset($_GET['pattern'])) {
    $pattern = $_GET['pattern'];
    // 过滤了 .. 和 /
    if (strpos($pattern, '..') !== false || strpos($pattern, '/') !== false) {
        $output = "不允许路径穿越或绝对路径！";
    } else {
        $files = glob("files/" . $pattern);
        if ($files) {
            $output = implode("\n", $files);
        } else {
            $output = "没有匹配的文件";
        }
    }
}
?>

<p>文件列表（通配符遍历）</p>
<p>使用通配符列出文件，可发现敏感文件名。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>文件模式</label>
    <input type="text" name="pattern" placeholder="例如：*.txt" value="<?= h($_GET['pattern'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    通配符模式：
    <br>* — 匹配任意字符
    <br>? — 匹配单个字符
    <br>[abc] — 匹配方括号中的字符
    <br>{php,txt} — 匹配多个模式
</p>
