<?php
$pageTitle = '登录';
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('页面凭证已失效，请返回后重试');
    }
    session_destroy();
    header('Location: /login.php');
    exit;
}

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
            <h2>登录 VulnLab</h2>
            <p>安全靶场练习平台</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" placeholder="请输入用户名" minlength="3" maxlength="50" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" placeholder="请输入密码" minlength="4" maxlength="4096" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">登 录</button>
        </form>
        <div class="auth-footer">
            还没有账号？<a href="/register.php">立即注册</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
