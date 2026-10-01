<?php
// GEOMETRY报错注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if ($error !== '' && is_string($id) && preg_match('/st_[a-z_]+\s*\(|polygon\s*\(|multipoint\s*\(|multipolygon\s*\(/i', $id)) {
        renderChallengeSuccess($challenge, '几何函数构造非法数据触发报错，报错注入回显查询结果');
    }
}
?>
<p>GEOMETRY报错注入</p>
<p>使用几何函数触发报错注入。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['cid'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?>
<div class="result-box"><?php foreach($output as $row): ?><div>ID:<?= h($row['id']) ?> 用户:<?= h($row['username']) ?> 邮箱:<?= h($row['email']) ?></div><?php endforeach; ?></div>
<?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>提示:</strong> ?id=1' AND (SELECT ST_LatFromGeoHash(CONCAT(0x7e,(SELECT version()),0x7e))) --+
    <br>或使用polygon, multipoint等几何函数
</div>
