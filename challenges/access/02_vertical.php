<?php
// 垂直越权
$result = null;
$currentUser = $_SESSION['username'] ?? 'guest';
$currentRole = 'user'; // 假设当前用户是普通用户

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    // 漏洞：未验证用户角色，直接执行管理操作
    if ($action === 'manage_users') {
        $db = getVulnDB();
        $users = $db->query("SELECT id, username, role FROM users_info")->fetchAll(PDO::FETCH_ASSOC);
        $result = '<strong>用户管理面板：</strong><table><tr><th>ID</th><th>用户名</th><th>角色</th></tr>';
        foreach ($users as $u) {
            $result .= '<tr><td>' . h($u['id']) . '</td><td>' . h($u['username']) . '</td><td>' . h($u['role']) . '</td></tr>';
        }
        $result .= '</table>';
    }
    if ($action === 'system_info') {
        $result = '<strong>系统信息：</strong><br>PHP版本：' . phpversion() . '<br>操作系统：' . php_uname();
    }
    if ($action === 'delete_user' && isset($_GET['uid'])) {
        $result = '<span style="color:var(--danger);">模拟删除用户 #' . h($_GET['uid']) . ' 成功（越权操作）</span>';
    }
}
?>

<p>管理后台（存在垂直越权漏洞）</p>
<p>普通用户可以直接访问管理员接口，后端未验证角色。</p>

<div style="background:var(--bg-secondary); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1rem;">
    当前用户：<?= h($currentUser) ?>（角色：<?= h($currentRole) ?>）
</div>

<div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&action=manage_users" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">用户管理</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&action=system_info" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">系统信息</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&action=delete_user&uid=3" class="btn" style="background:rgba(255,71,87,0.2); color:var(--danger);">删除用户</a>
</div>

<?php if ($result): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    你是普通用户，但这些管理接口没有验证角色权限，可以直接访问。
</p>
