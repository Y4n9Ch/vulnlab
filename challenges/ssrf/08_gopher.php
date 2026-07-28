<?php
// Gopher协议SSRF
$output = null;
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    // 只禁止了file://
    $url = str_replace('file://', '', $url);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        $output = "错误: " . $error;
    }
}
?>

<p>URL获取（Gopher协议SSRF）</p>
<p>只禁止了file://协议，可使用gopher://协议攻击内网服务。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>URL地址</label>
    <input type="text" name="url" placeholder="http://example.com" value="<?= h($_GET['url'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">获 取</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">Gopher协议攻击：</strong>
    <br>gopher://127.0.0.1:6379/_*1%0d%0a$4%0d%0aINFO%0d%0a — Redis
    <br>gopher://127.0.0.1:25/_HELO%20test%0d%0a — SMTP
    <br>可构造任意TCP数据包
</div>
