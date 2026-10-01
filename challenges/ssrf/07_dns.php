<?php
// DNS重绑定SSRF
$output = null;
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    $host = parse_url($url, PHP_URL_HOST);

    // 第一次DNS解析检查
    $ip = gethostbyname($host);

    if (!preg_match('/^(127\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $ip)) {
        // 漏洞：curl会再次DNS解析，可能得到不同IP
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($ch);
        $connectedIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
        curl_close($ch);
        if ($output !== false && preg_match('/^(127\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.|::1$)/', (string) $connectedIp)) {
            renderChallengeSuccess($challenge, '两次解析结果不一致，实际连接进入了内网地址');
        }
    } else {
        $output = "解析到内网地址: {$ip}，禁止访问！";
    }
}
?>

<p>URL获取（DNS重绑定SSRF）</p>
<p>通过DNS重绑定技术，第一次解析返回外网IP，第二次返回内网IP。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>URL地址</label>
    <input type="text" name="url" placeholder="http://example.com" value="<?= h($_GET['url'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">获 取</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">DNS重绑定：</strong>
    <br>1. 设置TTL=0的DNS记录
    <br>2. 第一次解析返回 1.2.3.4（通过检查）
    <br>3. 第二次解析返回 127.0.0.1（攻击目标）
    <br>工具：rbndr.us
</div>
