<?php
// 命令注入过滤绕过
$output = null;
$blocked = false;

if (isset($_POST['ip'])) {
    $ip = $_POST['ip'];

    // 过滤部分分隔符
    $blacklist = [';', '|', '&', '&&', '||'];
    foreach ($blacklist as $char) {
        if (strpos($ip, $char) !== false) {
            $blocked = true;
            break;
        }
    }

    if ($blocked) {
        $output = 'blocked';
    } else {
        $cmd = "ping -c 3 " . $ip;
        $output = shell_exec($cmd);
    }
}
?>

<p>Ping工具（命令注入过滤绕过）</p>
<p>后端过滤了 ;、|、&、&&、|| 等命令分隔符。</p>

<form method="POST">
    <label>IP地址</label>
    <input type="text" name="ip" placeholder="例如：127.0.0.1" value="<?= h($_POST['ip'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">Ping</button>
</form>

<?php if ($output === 'blocked'): ?>
    <div class="result-box" style="color:var(--danger);">检测到非法字符！</div>
<?php elseif ($output): ?>
    <div class="result-box"><?= h($output) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤了：; | & && ||<br>
    绕过方法：试试 %0a（换行符）或反引号 `whoami` 或 $(whoami)
</p>
