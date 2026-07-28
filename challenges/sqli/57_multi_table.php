<?php
// 多表联合注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = getVulnDB();
    $sql = "SELECT u.id, u.username, u.email, u.role, o.product, o.price FROM users_info u LEFT JOIN orders o ON u.id = o.user_id WHERE u.id = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
}
?>
<p>多表联合注入</p>
<p>多表JOIN查询，需要理解表结构进行注入。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['id'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?> 产品:<?= h($row['product'] ?? 'N/A') ?> 价格:<?= h($row['price'] ?? 'N/A') ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT u.*, o.* FROM users_info u LEFT JOIN orders o ON u.id = o.user_id WHERE u.id = '<strong>$id</strong>'
    <br><strong>提示:</strong> 多表UNION: ?id=-1' UNION SELECT 1,username,password,email,5,6,7,8 FROM users --+
</div>
