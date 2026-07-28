<?php
// 基础命令注入 - Ping工具
$output = null;
if (isset($_POST['ip'])) {
    $ip = $_POST['ip'];
    // 漏洞：直接拼接到系统命令
    $cmd = "ping -c 3 " . $ip;
    $output = shell_exec($cmd);
}
?>

<p>Ping工具（存在命令注入漏洞）</p>
<p>输入IP地址进行Ping测试，后端直接拼接到系统命令。</p>

<form method="POST">
    <label>IP地址</label>
    <input type="text" name="ip" placeholder="例如：127.0.0.1" value="<?= h($_POST['ip'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">Ping</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= h($output) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    执行命令：ping -c 3 <?= h($_POST['ip'] ?? '输入的IP') ?><br>
    绕过方法：127.0.0.1; whoami 或 127.0.0.1 | cat /etc/passwd
</p>
