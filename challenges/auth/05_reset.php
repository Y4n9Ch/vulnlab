<?php
// 密码重置漏洞
$output = null;
$users = [
    'admin' => ['email' => 'admin@test.com', 'password' => 'admin123'],
    'user1' => ['email' => 'user1@test.com', 'password' => 'pass123'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'request') {
        $email = $_POST['email'] ?? '';
        foreach ($users as $username => $info) {
            if ($info['email'] === $email) {
                // 漏洞：重置token可预测
                $token = md5($username . time());
                $output = "重置链接已发送到 {$email}<br>";
                $output .= "Token: {$token}<br>";
                $output .= "<a href='?id=" . h($_GET['id']) . "&token={$token}&user={$username}'>点击重置</a>";
                break;
            }
        }
        if (!$output) $output = "邮箱不存在";
    } elseif ($action === 'reset') {
        $token = $_GET['token'] ?? $_POST['token'] ?? '';
        $user = $_GET['user'] ?? $_POST['user'] ?? '';
        $newpass = $_POST['newpass'] ?? '';

        // 漏洞：token未绑定用户，且无过期验证
        if ($token && $newpass) {
            $output = "密码重置成功！用户: {$user}，新密码: {$newpass}";
        }
    }
}
?>

<p>密码重置（密码重置漏洞）</p>
<p>密码重置流程存在多个漏洞：token可预测、未绑定用户、无过期验证。</p>

<form method="POST">
    <input type="hidden" name="action" value="request">
    <label>注册邮箱</label>
    <input type="email" name="email" placeholder="输入注册邮箱" value="<?= h($_POST['email'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">发送重置邮件</button>
</form>

<?php if (isset($_GET['token'])): ?>
<form method="POST" style="margin-top:1rem;">
    <input type="hidden" name="action" value="reset">
    <input type="hidden" name="token" value="<?= h($_GET['token']) ?>">
    <input type="hidden" name="user" value="<?= h($_GET['user'] ?? '') ?>">
    <label>新密码</label>
    <input type="password" name="newpass" placeholder="输入新密码" required>
    <button type="submit" class="btn btn-danger">重置密码</button>
</form>
<?php endif; ?>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">漏洞点：</strong>
    <br>1. Token可预测（基于用户名+时间的MD5）
    <br>2. Token未绑定用户（任何人可用）
    <br>3. Token无过期机制
    <br>4. 重置链接直接返回到页面（生产环境应发邮件）
</div>
