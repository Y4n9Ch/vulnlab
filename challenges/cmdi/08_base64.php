<?php
// Base64编码命令注入
$output = null;
if (isset($_GET['data'])) {
    $data = $_GET['data'];
    // 看似安全：只接受Base64数据
    if (preg_match('/^[A-Za-z0-9+\/=]+$/', $data)) {
        $decoded = base64_decode($data);
        // 漏洞：解码后直接作为命令
        $output = shell_exec($decoded . ' 2>&1');
    } else {
        $output = "只接受Base64编码的数据！";
    }
}
?>

<p>数据解码（Base64编码注入）</p>
<p>只接受Base64输入，但解码后直接执行命令。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>Base64数据</label>
    <input type="text" name="data" placeholder="输入Base64编码" value="<?= h($_GET['data'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">解码执行</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    Base64编码命令：
    <br>whoami → d2hvYW1p
    <br>cat /etc/passwd → Y2F0IC9ldGMvcGFzc3dk
    <br>id → aWQ=
</p>
