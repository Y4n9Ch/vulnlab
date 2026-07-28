<?php
// 命令分割符注入
$output = null;
if (isset($_GET['host'])) {
    $host = $_GET['host'];
    // 只过滤了 | 和 &
    $host = str_replace(['|', '&'], '', $host);
    $cmd = "ping -c 1 " . $host;
    $output = shell_exec($cmd);
}
?>

<p>网络检测（命令分割符注入）</p>
<p>过滤了 | 和 &，但可使用其他命令分割符。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>目标地址</label>
    <input type="text" name="host" placeholder="输入IP或域名" value="<?= h($_GET['host'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">检 测</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    命令分割符：
    <br>; 分号 — 顺序执行
    <br>%0a 换行符 — 绕过单行限制
    <br>|| 或 && — 条件执行（需巧妙构造）
    <br>`反引号` — 命令替换
</p>
