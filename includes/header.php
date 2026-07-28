<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?>VulnLab 安全靶场</title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= (int) filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <a href="/index.php" class="nav-logo">
            <span class="logo-icon">&#9760;</span>
            <span>VulnLab</span>
        </a>
        <div class="nav-links">
            <a href="/index.php" class="nav-link">靶场</a>
            <a href="/index.php#learning-path" class="nav-link">学习路线</a>
            <?php if (isLoggedIn()): ?>
                <span class="nav-user"><?= h($_SESSION['username'] ?? '') ?></span>
                <form method="POST" action="/login.php" class="nav-logout-form">
                    <input type="hidden" name="action" value="logout">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <button type="submit" class="nav-link btn-outline">退出</button>
                </form>
            <?php else: ?>
                <a href="/login.php" class="nav-link">登录</a>
                <a href="/register.php" class="nav-link btn-primary">注册</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="main-content">
