<?php
// 未授权访问
$action = $_GET['action'] ?? '';
$result = null;

// 漏洞：管理接口无认证检查
if ($action === 'admin_panel') {
    renderChallengeSuccess($challenge, '未登录直接访问了管理接口');
    $db = getVulnDB();
    $users = $db->query("SELECT id, username, role FROM users_info")->fetchAll(PDO::FETCH_ASSOC);
    $result = '<strong>管理面板（无需登录）：</strong><table><tr><th>ID</th><th>用户名</th><th>角色</th></tr>';
    foreach ($users as $u) {
        $result .= '<tr><td>' . h($u['id']) . '</td><td>' . h($u['username']) . '</td><td>' . h($u['role']) . '</td></tr>';
    }
    $result .= '</table>';
}

if ($action === 'config') {
    $result = '<strong>系统配置：</strong><br>数据库：' . DB_HOST . ':' . DB_PORT . '<br>用户：' . DB_USER;
}

if ($action === 'phpinfo') {
    ob_start();
    phpinfo(INFO_GENERAL | INFO_MODULES);
    $result = ob_get_clean();
}
?>

<p>后台管理接口（未授权访问）</p>
<p>管理接口没有认证检查，任何人都可以访问。</p>

<div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['cid'] ?? '') ?>&action=admin_panel" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">管理面板</a>
    <a href="?id=<?= h($_GET['cid'] ?? '') ?>&action=config" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">系统配置</a>
    <a href="?id=<?= h($_GET['cid'] ?? '') ?>&action=phpinfo" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">PHP信息</a>
</div>

<?php if ($result): ?>
    <div class="result-box" style="max-height:300px; overflow-y:auto;"><?= $result ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    这些管理接口不需要登录即可访问，属于未授权访问漏洞。
</p>
