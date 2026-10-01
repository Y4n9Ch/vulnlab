<?php
// 重定向SSRF
$output = null;
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    // 检查是否是合法URL
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        // 检查是否是内网地址（可绕过）
        $host = parse_url($url, PHP_URL_HOST);
        if (!preg_match('/^(127\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $host)) {
            // 漏洞：跟随重定向
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $output = curl_exec($ch);
            $effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
            if ($output !== false && preg_match('#//(127\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.|localhost)#i', (string) $effective)) {
                renderChallengeSuccess($challenge, '重定向把服务端请求带进了内网地址');
            }
        } else {
            $output = "禁止访问内网地址！";
        }
    } else {
        $output = "无效的URL";
    }
}
?>

<p>URL预览（重定向SSRF）</p>
<p>检查了目标地址，但跟随重定向可绕过检查。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>URL地址</label>
    <input type="text" name="url" placeholder="http://example.com" value="<?= h($_GET['url'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">预 览</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">重定向绕过：</strong>
    <br>1. 在攻击者服务器设置302重定向到内网地址
    <br>2. http://attacker.com/redirect?url=http://127.0.0.1
    <br>3. 服务器跟随重定向访问内网
</div>
