<?php
// IDOR越权
$result = null;
$db = getVulnDB();

$id = intval($_GET['uid'] ?? 0);
if ($id > 0) {
    // 漏洞：未验证当前用户是否有权限查看该ID
    $stmt = $db->prepare("SELECT id, username, email, role FROM users_info WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
}
?>

<p>个人信息查询（存在IDOR越权漏洞）</p>
<p>通过URL参数中的ID获取用户信息，未验证权限。</p>

<div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&uid=1" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">用户1</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&uid=2" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">用户2</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&uid=3" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">用户3</a>
    <a href="?id=<?= h($_GET['id'] ?? '') ?>&uid=4" class="btn" style="background:var(--bg-secondary); color:var(--text-secondary);">用户4</a>
</div>

<?php if ($result): ?>
    <div class="result-box">
        <table>
            <tr><th>字段</th><th>值</th></tr>
            <tr><td>ID</td><td><?= h($result['id']) ?></td></tr>
            <tr><td>用户名</td><td><?= h($result['username']) ?></td></tr>
            <tr><td>邮箱</td><td><?= h($result['email']) ?></td></tr>
            <tr><td>角色</td><td><?= h($result['role']) ?></td></tr>
        </table>
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    当前用户应该是普通用户，但可以通过修改 uid 参数查看其他用户（包括admin）的信息。
</p>
