<?php
// Token绕过 - CSRF Token可预测
$msg = null;

// 生成可预测的Token（漏洞：基于用户名+时间戳）
$currentUser = $_SESSION['username'] ?? 'guest';
$token = md5($currentUser . date('Ymd')); // 每天固定

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['token'] ?? '';
    $newpass = $_POST['newpass'] ?? '';

    // 验证Token（但Token可预测）
    if ($submittedToken === $token) {
        renderChallengeSuccess($challenge, '可预测的 CSRF Token 被攻击者推导并复用');
        $msg = '<span style="color:var(--accent);">密码已修改为：' . h($newpass) . '</span>';
    } else {
        $msg = '<span style="color:var(--danger);">Token验证失败！</span>';
    }
}
?>

<p>修改密码功能（CSRF Token可预测）</p>
<p>使用了CSRF Token保护，但Token生成规则可预测。</p>

<form method="POST">
    <input type="hidden" name="token" value="<?= h($token) ?>">
    <label>新密码</label>
    <input type="text" name="newpass" placeholder="输入新密码">
    <button type="submit" class="btn btn-primary">修改密码</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<div style="margin-top:1.5rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">分析Token生成规则：</strong>
    <br>当前Token：<code style="color:var(--accent);"><?= h($token) ?></code>
    <br><br>
    <span style="color:var(--text-muted);">
        观察Token是否每天变化？是否与用户名相关？
        <br>如果能预测Token生成规则，就能构造有效的CSRF攻击。
    </span>
</div>
