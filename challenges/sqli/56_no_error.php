<?php
// 无错误回显注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $found = false;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; $found = true; }
    } catch (PDOException $e) {
        // 不显示错误信息
    }
}
?>
<p>无错误回显注入</p>
<p>页面不显示任何错误信息，只能通过布尔判断。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['id'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<div class="result-box">查询结果: <?= $found ? '找到数据' : '未找到数据' ?></div>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>特点:</strong> 无错误回显，只能布尔盲注或时间盲注
</div>
