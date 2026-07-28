<?php
// 水平越权
$msg = null;
$db = getVulnDB();

// 假设当前用户ID为2
$myId = 2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = intval($_POST['user_id'] ?? $myId);
    $newEmail = $_POST['email'] ?? '';

    // 漏洞：直接使用POST中的user_id，未验证是否为当前用户
    $stmt = $db->prepare("UPDATE users_info SET email = ? WHERE id = ?");
    $stmt->execute([$newEmail, $targetId]);
    $msg = '<span style="color:var(--accent);">用户 #' . h($targetId) . ' 的邮箱已更新为：' . h($newEmail) . '</span>';
}

// 显示所有用户
$users = $db->query("SELECT id, username, email FROM users_info")->fetchAll(PDO::FETCH_ASSOC);
?>

<p>个人资料修改（存在水平越权漏洞）</p>
<p>修改资料时，可以通过修改请求中的 user_id 来修改其他用户的资料。</p>

<div class="result-box">
    <strong>当前用户列表：</strong>
    <table>
        <tr><th>ID</th><th>用户名</th><th>邮箱</th></tr>
        <?php foreach ($users as $u): ?>
        <tr><td><?= h($u['id']) ?></td><td><?= h($u['username']) ?></td><td><?= h($u['email']) ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>

<form method="POST" style="margin-top:1rem;">
    <label>用户ID（隐藏字段）</label>
    <input type="number" name="user_id" value="<?= h($_POST['user_id'] ?? $myId) ?>">
    <label>新邮箱</label>
    <input type="text" name="email" placeholder="new@email.com" value="<?= h($_POST['email'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">修改邮箱</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    你的用户ID是 2，但可以修改 user_id 参数为其他用户ID来修改他们的资料。
</p>
