<?php
// URL解析差异SSRF
$output = null;
if (isset($_GET['url'])) {
    $url = $_GET['url'];

    // 使用parse_url解析
    $parsed = parse_url($url);
    $host = $parsed['host'] ?? '';

    // 检查黑名单
    $blacklist = ['127.0.0.1', 'localhost', '0.0.0.0'];
    if (in_array($host, $blacklist)) {
        $output = "目标地址被禁止！";
    } else {
        // 漏洞：URL解析差异
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($ch);
        curl_close($ch);
    }
}
?>

<p>URL获取（URL解析差异SSRF）</p>
<p>黑名单只检查精确匹配，利用URL解析差异可绕过。</p>

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
    <strong style="color:var(--warning);">绕过方法：</strong>
    <br>http://127.0.0.1.nip.io/ — DNS服务解析到127.0.0.1
    <br>http://0x7f000001/ — 十六进制IP
    <br>http://2130706433/ — 十进制IP
    <br>http://0177.0.0.1/ — 八进制IP
</div>
