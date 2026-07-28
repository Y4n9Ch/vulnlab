<?php
// JSON注入
$db = getVulnDB();
$result = null;

if (isset($_POST['data'])) {
    $data = $_POST['data'];
    // 漏洞：JSON字段注入
    $sql = "SELECT id, username, email, role FROM users_info WHERE username = JSON_EXTRACT('$data', '$.name')";
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

<p>用户查询（JSON注入）</p>
<p>后端使用JSON_EXTRACT处理用户输入，存在注入点。</p>

<form method="POST">
    <label>JSON数据</label>
    <input type="text" name="data" placeholder='{"name":"admin"}' value="<?= h($_POST['data'] ?? '') ?>">
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
    JSON注入：利用JSON_EXTRACT函数的特性进行注入。
    <br>试试：{"name":"' OR 1=1 -- "}
</p>
