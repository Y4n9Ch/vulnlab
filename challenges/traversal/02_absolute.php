<?php
// 绝对路径目录遍历
$output = null;
if (isset($_GET['path'])) {
    $path = $_GET['path'];
    // 只检查是否包含 ..
    if (strpos($path, '..') !== false) {
        $output = "不允许路径穿越！";
    } else {
        // 漏洞：允许绝对路径
        $content = @file_get_contents($path);
        if ($content !== false) {
            if (preg_match('#^/|^\\\\|^[A-Za-z]:[\\\\/]#', $path)) renderChallengeSuccess($challenge, '绝对路径绕过了相对路径边界，读取了任意文件');
            $output = $content;
        } else {
            $output = "无法读取文件: {$path}";
        }
    }
}
?>

<p>文件读取（绝对路径遍历）</p>
<p>过滤了 .. 但允许使用绝对路径直接读取任意文件。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>文件路径</label>
    <input type="text" name="path" placeholder="输入文件路径" value="<?= h($_GET['path'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">读 取</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    绝对路径读取：
    <br>/etc/passwd — Linux密码文件
    <br>C:\Windows\System32\drivers\etc\hosts — Windows hosts
    <br>/proc/self/environ — 环境变量
    <br>/proc/self/cmdline — 启动命令
</p>
