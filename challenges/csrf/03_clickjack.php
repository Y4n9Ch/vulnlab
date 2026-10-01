<?php
// 点击劫持 - 页面可被iframe嵌入
// 注意：故意不设置 X-Frame-Options 头
if (isset($_GET['framed'])) {
    renderChallengeSuccess($challenge, '页面被第三方页面 iframe 嵌入且未设置防嵌头');
}
?>
<script>
if (window.self !== window.top) {
    var params = new URLSearchParams(location.search);
    params.set('framed', '1');
    new Image().src = '?' + params.toString();
}
</script>
<p>用户设置页面（存在点击劫持漏洞）</p>
<p>该页面没有设置 X-Frame-Options 或 CSP frame-ancestors 头，可以被任意页面通过iframe嵌入。</p>

<div style="background:var(--bg-secondary); padding:1.5rem; border-radius:var(--radius-sm); text-align:center;">
    <h3 style="color:var(--accent); margin-bottom:1rem;">用户设置</h3>
    <p>当前账户：<?= h($_SESSION['username'] ?? 'guest') ?></p>
    <button class="btn btn-primary" onclick="alert('账户已注销！（模拟）')" style="margin:1rem 0;">
        注销账户
    </button>
    <br>
    <button class="btn" style="background:var(--danger); color:#fff;" onclick="alert('数据已删除！（模拟）')">
        删除所有数据
    </button>
</div>

<div style="margin-top:1.5rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">点击劫持攻击示例：</strong>
    <pre style="color:var(--text-muted); margin-top:0.5rem;">&lt;html&gt;
&lt;head&gt;&lt;style&gt;
  iframe { position:absolute; top:0; left:0; width:100%; height:100%; opacity:0.001; z-index:1; }
  .overlay { position:relative; z-index:0; }
&lt;/style&gt;&lt;/head&gt;
&lt;body&gt;
  &lt;div class="overlay"&gt;
    &lt;h1&gt;点击领取奖励！&lt;/h1&gt;
    &lt;button&gt;立即领取&lt;/button&gt;  <!-- 实际点击的是下面iframe中的按钮 -->
  &lt;/div&gt;
  &lt;iframe src="目标页面URL"&gt;&lt;/iframe&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
</div>
