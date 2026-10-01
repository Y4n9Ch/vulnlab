<?php
// 路径绕过访问控制
$output = null;

// 模拟受保护的管理页面
$adminPath = '/admin/dashboard';

if (isset($_GET['page'])) {
    $page = $_GET['page'];

    // 漏洞：路径检查不严格
    if (strpos($page, '/admin') === 0) {
        // 检查是否登录（简化）
        if (!isset($_COOKIE['admin_token'])) {
            // 检查路径是否绕过
            if ($page === '/admin/dashboard' || $page === '/admin/') {
                $output = "403 Forbidden - 需要管理员权限";
            } else {
                // 漏洞：其他admin路径不检查
                renderChallengeSuccess($challenge, '路径校验被绕过，未登录访问到了管理页面');
                $output = "欢迎访问管理页面: {$page}";
            }
        } else {
            $output = "管理员已登录，欢迎访问: {$page}";
        }
    } else {
        $output = "页面内容: {$page}";
    }
}
?>

<p>页面访问（路径绕过访问控制）</p>
<p>管理页面的访问控制存在路径绕过漏洞。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>页面路径</label>
    <input type="text" name="page" placeholder="/admin/dashboard" value="<?= h($_GET['page'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">访 问</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">路径绕过方法：</strong>
    <br>/admin/dashboard — 被拦截
    <br>/admin/dashboard/ — 可能绕过
    <br>/admin/Dashboard — 大小写
    <br>/admin%2fdashboard — URL编码
    <br>/./admin/dashboard — 路径规范化
</div>
