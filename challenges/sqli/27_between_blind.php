<?php
// BETWEEN盲注
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = ''; $found = false;

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id BETWEEN $id AND 100";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; $found = true; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if (is_string($id) && preg_match('/\b(or|and|union)\b|--|#|\|\|/i', $id)) {
        renderChallengeSuccess($challenge, 'BETWEEN范围条件被拼接改写，范围盲注成立');
    }
}
?>
<p>BETWEEN范围盲注</p>
<p>使用BETWEEN范围查询，通过结果判断数据。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>起始值</label>
    <input type="text" name="id" placeholder="输入起始值" value="<?= h($_GET['cid'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<div class="result-box">查询结果: <?= $found ? '找到数据' : '未找到数据' ?></div>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT * FROM users_info WHERE id BETWEEN <strong>$id</strong> AND 100
    <br><strong>提示:</strong> BETWEEN注入: 1 OR 1=1 --+
</div>
