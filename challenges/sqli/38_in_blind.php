<?php
// IN子查询盲注
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = ''; $found = false;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id IN ($id)";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; $found = true; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
}
?>
<p>IN子查询盲注</p>
<p>IN子句参数可控，可进行子查询注入。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>IN值</label>
    <input type="text" name="id" placeholder="输入ID列表" value="<?= h($_GET['id'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<div class="result-box">查询结果: <?= $found ? '找到数据' : '未找到数据' ?></div>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT * FROM users_info WHERE id IN (<strong>$id</strong>)
    <br><strong>提示:</strong> IN注入: (1) UNION SELECT 1,2,3,4 --+
</div>
