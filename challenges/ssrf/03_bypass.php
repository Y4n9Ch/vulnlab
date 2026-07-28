<?php
// SSRF过滤绕过
$result = null;
$blocked = false;

if (isset($_POST['url'])) {
    $url = $_POST['url'];

    // 过滤 127.0.0.1 和 localhost
    if (preg_match('/127\.0\.0\.1/i', $url) || preg_match('/localhost/i', $url)) {
        $blocked = true;
    }

    if ($blocked) {
        $result = 'blocked';
    } else {
        $result = @file_get_contents($url);
        if ($result === false) {
            $result = '请求失败';
        }
    }
}
?>

<p>URL采集工具（SSRF过滤绕过）</p>
<p>后端过滤了 127.0.0.1 和 localhost，但IP有多种表示方式。</p>

<form method="POST">
    <label>目标URL</label>
    <input type="text" name="url" placeholder="http://example.com" value="<?= h($_POST['url'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">采 集</button>
</form>

<?php if ($result === 'blocked'): ?>
    <div class="result-box" style="color:var(--danger);">禁止访问内网地址！</div>
<?php elseif ($result): ?>
    <div class="result-box" style="max-height:300px; overflow-y:auto;"><?= h($result) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤了：127.0.0.1、localhost<br>
    绕过方式：
    <br>- 0x7f000001（十六进制）
    <br>- 2130706433（十进制）
    <br>- 0.0.0.0
    <br>- [::1]（IPv6）
    <br>- 127.1
    <br>- 0177.0.0.1（八进制）
    <br>- dns rebinding
</p>
