<?php
// 请求头绕过访问控制
$output = null;

if (isset($_GET['page'])) {
    $page = $_GET['page'];

    if ($page === 'admin') {
        // 漏洞：信任客户端请求头
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
        $role = $_SERVER['HTTP_X_ROLE'] ?? 'user';

        if ($role === 'admin' || $ip === '127.0.0.1') {
            $output = "管理员面板内容（欢迎，{$role}用户，IP: {$ip}）";
        } else {
            $output = "403 Forbidden - IP: {$ip}, Role: {$role}";
        }
    } else {
        $output = "普通页面内容";
    }
}
?>

<p>管理面板（请求头绕过访问控制）</p>
<p>服务器信任客户端请求头判断身份，可伪造请求头绕过。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>页面</label>
    <select name="page">
        <option value="home">首页</option>
        <option value="admin">管理面板</option>
    </select>
    <button type="submit" class="btn btn-primary">访 问</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">请求头伪造：</strong>
    <br>X-Forwarded-For: 127.0.0.1 — 伪造来源IP
    <br>X-Role: admin — 伪造用户角色
    <br>使用Burp Suite修改请求头即可绕过
</div>
