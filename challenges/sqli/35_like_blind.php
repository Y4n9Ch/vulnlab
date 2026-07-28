<?php
// LIKE布尔盲注
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = ''; $found = false;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE username LIKE '%$id%'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; $found = true; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
}
?>
<p>LIKE布尔盲注</p>
<p>使用LIKE模糊查询，通过布尔结果判断数据。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>搜索用户名</label>
    <input type="text" name="id" placeholder="输入关键词" value="<?= h($_GET['id'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>
<div class="result-box">查询结果: <?= $found ? '找到数据' : '未找到数据' ?></div>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT * FROM users_info WHERE username LIKE '%<strong>$id</strong>%'
    <br><strong>提示:</strong> LIKE注入: % ' AND SUBSTR(username,1,1)='a' --+
</div>
