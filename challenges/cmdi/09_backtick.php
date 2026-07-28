<?php
// 反引号命令替换
$output = null;
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // 过滤了 ; | & 但没过滤反引号
    $file = str_replace([';', '|', '&'], '', $file);
    $cmd = "file " . $file;
    $output = shell_exec($cmd);
}
?>

<p>文件类型检测（反引号注入）</p>
<p>过滤了 ; | & 但可使用反引号 `` 进行命令替换。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>文件路径</label>
    <input type="text" name="file" placeholder="输入文件路径" value="<?= h($_GET['file'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">检 测</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    反引号命令替换：
    <br>`whoami` — 执行whoami命令
    <br>`cat /etc/passwd` — 读取文件
    <br>file `whoami` — 嵌入到原命令中
</p>
