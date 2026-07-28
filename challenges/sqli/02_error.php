<?php
// 报错注入 - 用户查询
$db = getVulnDB();
$result = null;

if (isset($_POST['username'])) {
    $user = $_POST['username'];
    // 漏洞：错误信息直接回显
    $sql = "SELECT * FROM users_info WHERE username = '$user'";
    try {
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = 'not_found';
        }
    } catch (PDOException $e) {
        $result = 'error:' . $e->getMessage();
    }
}
?>

<p>用户信息查询（存在报错注入漏洞）</p>
<p>后端开启了错误回显，可以利用报错函数提取数据。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="输入用户名查询" value="<?= h($_POST['username'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>

<?php if ($result === 'not_found'): ?>
    <div class="result-box" style="color: var(--warning);">未找到该用户</div>
<?php elseif (is_string($result) && strpos($result, 'error:') === 0): ?>
    <div class="result-box" style="color: var(--danger);">SQL错误：<?= h(substr($result, 6)) ?></div>
<?php elseif (is_array($result)): ?>
    <div class="result-box">
        <table>
            <tr><th>ID</th><th>用户名</th><th>密码(MD5)</th><th>邮箱</th><th>角色</th></tr>
            <?php foreach ($result as $row): ?>
            <tr>
                <td><?= h($row['id']) ?></td>
                <td><?= h($row['username']) ?></td>
                <td><?= h($row['password']) ?></td>
                <td><?= h($row['email']) ?></td>
                <td><?= h($row['role']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>
