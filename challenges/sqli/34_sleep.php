<?php
// SLEEP时间盲注
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = ''; $time = 0;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    $start = microtime(true);
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        $time = round((microtime(true) - $start) * 1000);
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
}
?>
<p>SLEEP时间盲注</p>
<p>使用SLEEP函数进行时间盲注，页面无回显。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['id'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<div class="result-box">查询耗时: <?= $time ?>ms</div>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>提示:</strong> ?id=1' AND IF(1=1,SLEEP(3),0) --+ 延迟3秒表示条件为真
</div>
