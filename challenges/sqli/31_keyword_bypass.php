<?php
// 关键字双写绕过
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    // 过滤关键字（只替换一次）
    $id = str_replace('select', '', $id);
    $id = str_replace('union', '', $id);
    $id = str_replace('from', '', $id);
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if ($output && is_string($id) && preg_match('/union|select|from/i', $id)) {
        renderChallengeSuccess($challenge, '双写关键字在过滤器删除一次后重组生效，联合查询返回数据');
    }
}
?>
<p>关键字双写绕过</p>
<p>过滤器只替换一次关键字，双写可绕过。</p>
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
    <strong>过滤规则:</strong> 替换 select, union, from 为空（只替换一次）
    <br><strong>提示:</strong> 双写绕过: selselectect, ununionion, frfromom
</div>
