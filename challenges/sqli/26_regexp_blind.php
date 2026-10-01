<?php
// REGEXP布尔盲注
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = ''; $found = false;

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE username REGEXP '$id'";
    try {
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll();
        if ($rows) { $output = $rows; $found = true; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if (is_string($id) && preg_match('/[\'"]/', $id)) {
        renderChallengeSuccess($challenge, 'REGEXP表达式引号被闭合构造布尔条件，正则盲注成立');
    }
}
?>
<p>REGEXP布尔盲注</p>
<p>使用REGEXP正则匹配，通过结果判断数据。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>正则表达式</label>
    <input type="text" name="id" placeholder="输入正则" value="<?= h($_GET['cid'] ?? '^a') ?>">
    <button type="submit" class="btn btn-primary">匹 配</button>
</form>
<div class="result-box">查询结果: <?= $found ? '找到数据' : '未找到数据' ?></div>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>SQL:</strong> SELECT * FROM users_info WHERE username REGEXP '<strong>$id</strong>'
    <br><strong>提示:</strong> REGEXP注入: ^' OR 1=1 --+
</div>
