<?php
// 空格绕过
$output = null;
$blocked = false;

if (isset($_POST['ip'])) {
    $ip = $_POST['ip'];

    // 过滤空格
    if (strpos($ip, ' ') !== false) {
        $blocked = true;
    }

    if ($blocked) {
        $output = 'blocked';
    } else {
        $cmd = "ping -c 3 " . $ip;
        $output = shell_exec($cmd);
    }
}
?>

<p>Ping工具（空格过滤绕过）</p>
<p>后端过滤了空格字符。</p>

<form method="POST">
    <label>IP地址</label>
    <input type="text" name="ip" placeholder="例如：127.0.0.1" value="<?= h($_POST['ip'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">Ping</button>
</form>

<?php if ($output === 'blocked'): ?>
    <div class="result-box" style="color:var(--danger);">检测到空格！</div>
<?php elseif ($output): ?>
    <div class="result-box"><?= h($output) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤了空格字符<br>
    绕过方法：$IFS、${IFS}、%09（Tab）、{cmd,arg}、&lt; 等方式代替空格
    <br>例如：127.0.0.1%0a${IFS}whoami
</p>
