<?php
// 联合查询注入 - 登录页面
$db = getVulnDB();
$result = null;
$logged = false;

if (isset($_POST['username'], $_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    // 漏洞：直接拼接用户输入
    $sql = "SELECT id, username, email, role FROM users_info WHERE username = '$user' AND password = MD5('$pass')";
    $sqlError = null;
    try {
        $stmt = $db->query($sql);
    } catch (PDOException $e) {
        // 注入导致 SQL 报错时把错误显示出来，方便学员调整列数与语法
        $stmt = null;
        $sqlError = $e->getMessage();
    }
    if ($stmt && $stmt->rowCount() > 0) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $logged = true;
        $_SESSION['sqli01_user'] = (string) ($result[0]['username'] ?? '');
    } elseif ($sqlError !== null) {
        $result = 'sql_error';
    } else {
        $result = 'login_failed';
    }
    if ($logged && is_string($user) && preg_match('/union[\s\/\*+!-]*select/i', $user . ' ' . $pass)) {
        renderChallengeSuccess($challenge, '联合查询注入绕过登录验证并读取了用户表数据');
    }
}

// 登录状态条放在登录处理之后渲染，保证本次登录立即生效
renderLoginStatus('sqli01_user');
?>

<p>用户登录系统（存在SQL注入漏洞）</p>
<p>测试账号：admin / 123456（密码以 MD5 存储）。</p>

<form method="POST" id="loginForm">
    <label>用户名</label>
    <input type="text" name="username" placeholder="请输入用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="text" name="password" placeholder="请输入密码" value="<?= h($_POST['password'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php if ($result === 'login_failed'): ?>
    <div class="result-box" style="color: var(--danger);">用户名或密码错误</div>
<?php elseif ($result === 'sql_error'): ?>
    <div class="result-box" style="color: var(--danger);">SQL执行错误：<?= h($sqlError) ?></div>
<?php elseif (is_array($result)): ?>
    <div class="result-box">
        <?php if ($logged): ?><p style="color: var(--accent);">登录成功！</p><?php endif; ?>
        <table>
            <tr><th>ID</th><th>用户名</th><th>邮箱</th><th>角色</th></tr>
            <?php foreach ($result as $row): ?>
            <tr>
                <td><?= h($row['id']) ?></td>
                <td><?= h($row['username']) ?></td>
                <td><?= h($row['email']) ?></td>
                <td><?= h($row['role']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var fd = new FormData(form);
    fetch(location.href, { method: 'POST', body: fd })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var newResult = doc.querySelector('.result-box');
            var oldResult = document.querySelector('.result-box');
            if (oldResult) oldResult.remove();
            if (!newResult) {
                newResult = document.createElement('div');
                newResult.className = 'result-box';
                newResult.style.color = 'var(--danger)';
                newResult.textContent = '请求处理失败，请检查输入后重试';
            }
            form.insertAdjacentElement('afterend', newResult);
            var newStatus = doc.querySelector('.login-status');
            var oldStatus = document.querySelector('.login-status');
            if (oldStatus) oldStatus.remove();
            if (newStatus) form.insertAdjacentElement('beforebegin', newStatus);
        });
});
</script>
