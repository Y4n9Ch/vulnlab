<?php
// 协议利用 - SSRF
$result = null;
if (isset($_POST['url'])) {
    $url = $_POST['url'];
    // 只允许 http/https（但curl支持更多协议）
    if (preg_match('/^https?:\/\//i', $url)) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        if ($result !== false && preg_match('#^https?://(127\.|localhost|\[::1\])#i', $url)) {
            renderChallengeSuccess($challenge, '采集请求被指向了本机地址');
        }
        curl_close($ch);
        if ($result === false) {
            $result = '请求失败：' . h($err);
        }
    } else {
        $result = '只允许 http/https 协议！';
    }
}
?>

<p>URL采集工具（协议利用SSRF）</p>
<p>后端使用 curl 请求URL，虽然只允许http/https，但curl支持更多协议。</p>

<form method="POST">
    <label>目标URL</label>
    <input type="text" name="url" placeholder="http://example.com" value="<?= h($_POST['url'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">采 集</button>
</form>

<?php if ($result): ?>
    <div class="result-box" style="max-height:300px; overflow-y:auto;"><?= h($result) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    虽然过滤了 http/https，但试试：
    <br>gopher://127.0.0.1:3306/_ （攻击MySQL）
    <br>dict://127.0.0.1:6379/ （攻击Redis）
    <br>file:///etc/passwd
</p>
