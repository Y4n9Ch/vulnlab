<?php
// 约束注入
$db = getVulnDB();
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $user = $_POST['username'];
    // 漏洞：INSERT注入，利用约束截断
    $sql = "INSERT INTO users_info (username, password, email, role) VALUES ('$user', MD5('123456'), 'new@test.com', 'user')";
    try {
        $db->exec($sql);
        $result = '<span style="color:var(--accent);">注册成功！用户名：' . h($user) . '</span>';
    } catch (Exception $e) {
        $result = '<span style="color:var(--danger);">注册失败：' . h($e->getMessage()) . '</span>';
    }
}

// 显示所有用户
$users = $db->query("SELECT id, username, role FROM users_info")->fetchAll(PDO::FETCH_ASSOC);
?>

<p>用户注册（约束注入）</p>
<p>注册功能存在INSERT注入，利用VARCHAR长度约束截断获取admin权限。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="输入用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">注 册</button>
</form>

<?php if ($result): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<div class="result-box">
    <strong>当前用户列表：</strong>
    <table><tr><th>ID</th><th>用户名</th><th>角色</th></tr>
    <?php foreach ($users as $u): ?>
    <tr><td><?= h($u['id']) ?></td><td><?= h($u['username']) ?></td><td><?= h($u['role']) ?></td></tr>
    <?php endforeach; ?></table>
</div>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    约束注入：username字段VARCHAR(50)，输入超过50字符会被截断。
    <br>构造：admin(空格*45)x → 截断后变成 admin
    <br>即注册一个以admin开头+大量空格+任意字符的用户名。
</p>
