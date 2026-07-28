<?php
// 异或注入
$db = getVulnDB();
$result = null;

if (isset($_POST['username'])) {
    $user = $_POST['username'];
    // 漏洞：异或注入，当所有关键字被过滤时可用
    $sql = "SELECT id, username, email, role FROM users_info WHERE username = '$user'";
    try {
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = 'not_found';
        }
    } catch (Exception $e) {
        $result = 'error:' . $e->getMessage();
    }
}
?>

<p>用户查询（异或注入）</p>
<p>当常规关键字被过滤时，可以使用异或(^)运算进行注入。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="输入用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>

<?php if ($result === 'not_found'): ?>
    <div class="result-box" style="color:var(--warning);">未找到用户</div>
<?php elseif (is_string($result) && strpos($result, 'error:') === 0): ?>
    <div class="result-box" style="color:var(--danger);">错误：<?= h(substr($result, 6)) ?></div>
<?php elseif (is_array($result)): ?>
    <div class="result-box">
        <table><tr><th>ID</th><th>用户名</th><th>邮箱</th><th>角色</th></tr>
        <?php foreach ($result as $row): ?>
        <tr><td><?= h($row['id']) ?></td><td><?= h($row['username']) ?></td><td><?= h($row['email']) ?></td><td><?= h($row['role']) ?></td></tr>
        <?php endforeach; ?></table>
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    异或注入：1'^1=0, 1'^0=1。利用异或运算的特性判断条件。
    <br>例如：'^(ascii(substr(database(),1,1))>100)^'
</p>
