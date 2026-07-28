<?php
require_once __DIR__ . '/functions.php';

// 处理注册
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $db = getDB();

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = '页面凭证已失效，请刷新后重试';
    }

    if (empty($error) && $_POST['action'] === 'register') {
        $username = trim(postString('username'));
        $password = postString('password');
        $confirm  = postString('confirm');

        if (strlen($username) < 3) {
            $error = '用户名至少3个字符';
        } elseif (strlen($username) > 50) {
            $error = '用户名不能超过50个字符';
        } elseif (strlen($password) < 4) {
            $error = '密码至少4个字符';
        } elseif (strlen($password) > 4096) {
            $error = '密码长度超出限制';
        } elseif ($password !== $confirm) {
            $error = '两次密码不一致';
        } else {
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = '用户名已存在';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $hash]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['username'] = $username;
                unset($_SESSION['csrf_token']);
                header('Location: /index.php');
                exit;
            }
        }
    }

    if (empty($error) && $_POST['action'] === 'login') {
        $username = trim(postString('username'));
        $password = postString('password');

        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            unset($_SESSION['csrf_token']);
            header('Location: /index.php');
            exit;
        } else {
            $error = '用户名或密码错误';
        }
    }
}
