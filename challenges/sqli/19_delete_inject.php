<?php
// DELETE注入
require_once __DIR__ . '/../../config/database.php';
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $db = getVulnDB();
    $sql = "DELETE FROM users_info WHERE id = $id";
    try {
        $db->exec($sql);
        $output = "删除成功！SQL: " . $sql;
    } catch (PDOException $e) { $output = "SQL错误: " . $e->getMessage(); }
    if (is_string($output) && strpos($output, '删除成功') === 0 && is_string($id) && preg_match('/\b(or|and|union)\b|--|#|\|\|/i', $id)) {
        renderChallengeSuccess($challenge, '数字型注入改写DELETE条件，删除范围被恶意扩大');
    }
}
?>
<p>DELETE注入</p>
<p>删除功能直接将ID拼接到DELETE语句中。</p>
<form method="POST">
    <label>要删除的ID</label>
    <input type="text" name="delete_id" placeholder="输入ID" value="<?= h($_POST['delete_id'] ?? '') ?>">
    <button type="submit" class="btn btn-danger">删 除</button>
</form>
<?php if ($output): ?><div class="result-box"><?= $output ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> DELETE FROM users_info WHERE id = <strong>$id</strong>
    <br><strong>提示:</strong> 数字型注入，直接拼接: 1 OR 1=1
</div>
