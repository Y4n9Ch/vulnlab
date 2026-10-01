<?php
// DOM型XSS - 纯前端漏洞
$nameParam = $_GET['name'] ?? '';
if (is_string($nameParam) && preg_match('/<[a-zA-Z!\/]/', $nameParam)) {
    renderChallengeSuccess($challenge, 'name 参数中的标记被原样交给了前端 innerHTML');
}
?>
<p>个人主页（存在DOM型XSS）</p>
<p>页面JavaScript从URL参数读取用户名并直接写入DOM，不经过服务端。</p>

<div id="userGreeting" class="result-box" style="min-height:60px;">
    <span style="color:var(--text-muted);">欢迎访问！请通过URL参数 name 指定用户名。</span>
</div>

<p>示例链接：<code>?id=<?= h($_GET['cid'] ?? '') ?>&name=guest</code></p>

<script>
// 漏洞代码：直接从URL取值写入DOM
var params = new URLSearchParams(window.location.search);
var name = params.get('name');
if (name) {
    // 漏洞点：innerHTML 直接写入未转义的内容
    document.getElementById('userGreeting').innerHTML = '你好，<strong>' + name + '</strong>！欢迎回来。';
}
</script>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    提示：这是一个纯前端漏洞，服务端不参与。
    <br>观察上面的JS代码，通过URL参数 name 注入脚本。
    <br>例如：?id=3&name=&lt;img src=x onerror=alert(1)&gt;
</p>
