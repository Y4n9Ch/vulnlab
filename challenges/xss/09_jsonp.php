<?php
// JSONP注入XSS
$callback = $_GET['callback'] ?? $_POST['callback'] ?? '';
$data = json_encode(['username' => 'guest', 'role' => 'user', 'email' => 'guest@test.com']);

if ($callback) {
    // 漏洞：callback参数未过滤
    header('Content-Type: application/javascript');
    if (is_string($callback) && preg_match('/[^A-Za-z0-9_$.]/', $callback)) {
        renderChallengeSuccess($challenge, '非函数名字符的 callback 内容被原样拼进 JS 响应');
    }
    echo $callback . '(' . $data . ');';
    exit;
}
?>

<p>JSONP接口（JSONP注入XSS）</p>
<p>JSONP接口的callback参数未过滤，可注入任意JavaScript。</p>

<form method="GET" style="margin-bottom:1rem;">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>Callback函数名</label>
    <input type="text" name="callback" placeholder="例如：handleData" value="<?= h($callback) ?>">
    <button type="submit" class="btn btn-primary">请 求</button>
</form>

<div class="result-box">
    <strong>接口地址：</strong> /challenges/xss/09_jsonp.php?callback=handleData
    <br><strong>返回格式：</strong> handleData({"username":"guest","role":"user","email":"guest@test.com"});
</div>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    JSONP注入：callback参数可注入任意JS代码。
    <br>例如：?callback=alert(1)//
    <br>或：?callback=eval(atob('YWxlcnQoMSk='))
</p>
