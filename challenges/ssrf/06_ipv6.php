<?php
// IPv6 SSRF
$output = null;
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    $host = parse_url($url, PHP_URL_HOST);

    // 只检查IPv4内网地址
    if (!preg_match('/^(127\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $host)) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($ch);
        curl_close($ch);
    } else {
        $output = "禁止访问内网地址！";
    }
}
?>

<p>URL获取（IPv6 SSRF）</p>
<p>只检查IPv4内网地址，可使用IPv6地址绕过。</p>

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
    <strong style="color:var(--warning);">IPv6绕过：</strong>
    <br>http://[::1]/ — IPv6的127.0.0.1
    <br>http://[0:0:0:0:0:0:0:1]/ — 完整写法
    <br>http://[::ffff:127.0.0.1]/ — IPv4映射地址
</div>
