<?php
// 注册逻辑漏洞
$output = null;
$users = ['admin', 'user1', 'user2'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($username && $password) {
        // 漏洞：用户名大小写不敏感检查
        $exists = false;
        foreach ($users as $u) {
            if (strtolower($u) === strtolower($username)) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $output = "用户名 {$username} 已存在！";
        } else {
            // 漏洞：注册时邮箱未验证
            if (strtolower($username) !== $username) {
                renderChallengeSuccess($challenge, '大小写变体绕过了重名检查');
            }
            $output = "注册成功！用户名: {$username}, 邮箱: {$email}";
            $users[] = $username;
        }
    }
}
?>

<p>用户注册（注册逻辑漏洞）</p>
<p>注册流程存在多个逻辑漏洞：大小写绕过、邮箱未验证、参数污染。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="输入用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="password" name="password" placeholder="输入密码">
    <label>邮箱</label>
    <input type="email" name="email" placeholder="输入邮箱" value="<?= h($_POST['email'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">注 册</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">漏洞点：</strong>
    <br>1. Admin、ADMIN 可绕过用户名检查
    <br>2. 邮箱字段可填写任意值
    <br>3. 可注册 admin 后缀账号如 admin123
</div>
