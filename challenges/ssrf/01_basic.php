<?php
// 基础SSRF
$result = null;
if (isset($_POST['url'])) {
    $url = $_POST['url'];
    // 漏洞：直接请求用户指定的URL
    $result = @file_get_contents($url);
    if ($result === false) {
        $result = '请求失败：' . h($url);
    }
}
?>

<p>URL采集工具（存在SSRF漏洞）</p>
<p>后端会请求用户指定的URL，可被利用访问内网资源或本地文件。</p>

<form method="POST">
    <label>目标URL</label>
    <input type="text" name="url" placeholder="http://example.com" value="<?= h($_POST['url'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">采 集</button>
</form>

<?php if ($result): ?>
    <div class="result-box" style="max-height:300px; overflow-y:auto;"><?= h($result) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    试试：http://127.0.0.1 或 file:///etc/passwd 或 file:///c:/windows/win.ini
</p>
