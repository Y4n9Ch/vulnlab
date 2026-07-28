<?php
$pageTitle = '注册';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <span class="auth-icon">&#9760;</span>
            <h2>注册 VulnLab</h2>
            <p>开始你的安全学习之旅</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" placeholder="至少3个字符" minlength="3" maxlength="50" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" placeholder="至少4个字符" minlength="4" maxlength="4096" autocomplete="new-password" required>
            </div>
            <div class="form-group">
                <label>确认密码</label>
                <input type="password" name="confirm" placeholder="再次输入密码" minlength="4" maxlength="4096" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">注 册</button>
        </form>
        <div class="auth-footer">
            已有账号？<a href="/login.php">去登录</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
