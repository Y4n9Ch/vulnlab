<?php
// 二次注入 - 注册 + 修改密码
$db = getVulnDB();
$msg = null;

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'register') {
        $user = $_POST['reg_user'] ?? '';
        $pass = $_POST['reg_pass'] ?? '';
        if ($user && $pass) {
            // 注册时做了转义，但存入数据库的是原始值
            $safeUser = addslashes($user);
            $sql = "INSERT INTO users_info (username, password, email, role) VALUES ('$safeUser', MD5('$pass'), 'new@vulnlab.com', 'user')";
            try {
                $db->exec($sql);
                $msg = '<span style="color:var(--accent);">注册成功！用户名：' . h($user) . '</span>';
            } catch (Exception $e) {
                $msg = '<span style="color:var(--danger);">注册失败：' . h($e->getMessage()) . '</span>';
            }
        }
    }

    if ($_POST['action'] === 'changepass') {
        $user = $_POST['cp_user'] ?? '';
        $newpass = $_POST['cp_newpass'] ?? '';
        if ($user && $newpass) {
            // 漏洞：直接从数据库取用户名拼接SQL，未转义
            $stmt = $db->prepare("SELECT username FROM users_info WHERE username = ?");
            $stmt->execute([$user]);
            $row = $stmt->fetch();
            if ($row) {
                $dbUser = $row['username']; // 数据库中的原始值
                // 直接使用数据库中的值拼接SQL（二次注入）
                $sql = "UPDATE users_info SET password = MD5('$newpass') WHERE username = '$dbUser'";
                try {
                    $db->exec($sql);
                    $msg = '<span style="color:var(--accent);">密码修改成功！</span>';
                    if (strpos($dbUser, "'") !== false || strpos($dbUser, '"') !== false) {
                        renderChallengeSuccess($challenge, '注册时存入的恶意字符在更新语句中生效，二次注入成立');
                    }
                } catch (Exception $e) {
                    $msg = '<span style="color:var(--danger);">错误：' . h($e->getMessage()) . '</span>';
                }
            } else {
                $msg = '<span style="color:var(--danger);">用户不存在</span>';
            }
        }
    }
}
?>

<p>用户注册与密码修改（存在二次注入漏洞）</p>
<p>注册时对输入做了转义，但修改密码时直接使用了数据库中存储的值。</p>

<div style="margin-bottom:1.5rem;">
    <h4 style="color:var(--accent); margin-bottom:0.5rem;">注册新用户</h4>
    <form method="POST">
        <input type="hidden" name="action" value="register">
        <label>用户名</label>
        <input type="text" name="reg_user" placeholder="用户名">
        <label>密码</label>
        <input type="text" name="reg_pass" placeholder="密码">
        <button type="submit" class="btn btn-primary">注 册</button>
    </form>
</div>

<div>
    <h4 style="color:var(--accent); margin-bottom:0.5rem;">修改密码</h4>
    <form method="POST">
        <input type="hidden" name="action" value="changepass">
        <label>用户名</label>
        <input type="text" name="cp_user" placeholder="用户名">
        <label>新密码</label>
        <input type="text" name="cp_newpass" placeholder="新密码">
        <button type="submit" class="btn btn-primary">修改密码</button>
    </form>
</div>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>
