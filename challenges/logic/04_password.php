<?php
// 密码修改逻辑漏洞
session_start();
$output = null;

// 模拟当前用户
$_SESSION['logic_user'] = $_SESSION['logic_user'] ?? 'user1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change') {
        $old_pass = $_POST['old_pass'] ?? '';
        $new_pass = $_POST['new_pass'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        // 漏洞：未验证旧密码
        if ($new_pass && $new_pass === $confirm) {
            renderChallengeSuccess($challenge, '不提供旧密码也完成了密码修改');
            $output = "密码修改成功！新密码: {$new_pass}";
        } elseif ($new_pass !== $confirm) {
            $output = "两次密码不一致！";
        }
    }
}
?>

<p>密码修改（密码修改逻辑漏洞）</p>
<p>修改密码时未验证旧密码，且可绕过确认检查。</p>

<form method="POST">
    <input type="hidden" name="action" value="change">
    <label>当前用户: <strong><?= $_SESSION['logic_user'] ?></strong></label>
    <label>旧密码</label>
    <input type="password" name="old_pass" placeholder="输入旧密码">
    <label>新密码</label>
    <input type="password" name="new_pass" placeholder="输入新密码">
    <label>确认密码</label>
    <input type="password" name="confirm" placeholder="再次输入">
    <button type="submit" class="btn btn-primary">修改密码</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    逻辑漏洞：
    <br>1. 旧密码字段为空也能修改成功
    <br>2. 可直接设置任意新密码
    <br>3. 正确实现应验证旧密码和CSRF Token
</p>
