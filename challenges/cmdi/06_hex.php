<?php
// 十六进制编码注入
$output = null;
if (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    // 只允许数字和点
    if (preg_match('/^[0-9.]+$/', $ip)) {
        $cmd = "ping -c 1 " . $ip;
        $output = shell_exec($cmd);
    } else {
        $output = "IP地址格式错误！只允许数字和点。";
    }
}
?>

<p>IP检测（十六进制编码注入）</p>
<p>只允许数字和点，可利用IP地址的十六进制表示法。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>IP地址</label>
    <input type="text" name="ip" placeholder="例如：127.0.0.1" value="<?= h($_GET['ip'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">Ping</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    IP地址特殊表示：
    <br>127.0.0.1 = 0x7f000001 (十六进制)
    <br>127.0.0.1 = 0177.0.0.1 (八进制)
    <br>2130706433 (十进制)
    <br>这些都可以作为ping的目标
</p>
