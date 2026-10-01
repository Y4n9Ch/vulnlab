<?php
// mysql_fetch_array注入
require_once __DIR__ . '/../../config/database.php';
$output = null; $error = '';

if (isset($_GET['cid'])) {
    $id = $_GET['cid'];
    $db = getVulnDB();
    $sql = "SELECT * FROM users_info WHERE id = '$id'";
    try {
        $stmt = $db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        if ($row) { $output = "ID:{$row[0]} 用户:{$row[1]} 邮箱:{$row[3]}"; } else { $error = '查询为空'; }
    } catch (PDOException $e) { $error = "SQL错误: " . $e->getMessage(); }
    if ($output && is_string($id) && preg_match('/[\'"]/', $id)) {
        renderChallengeSuccess($challenge, '单引号闭合后注入生效，FETCH_NUM方式返回了注入的数据');
    }
}
?>
<p>fetch方式差异</p>
<p>使用FETCH_NUM方式获取结果，返回数字索引数组。</p>
<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>输入ID</label>
    <input type="text" name="id" placeholder="请输入ID" value="<?= h($_GET['cid'] ?? '1') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>
<?php if ($output): ?><div class="result-box"><?= $output ?></div><?php endif; ?>
<?php if ($error): ?><div class="result-box" style="border-color:var(--danger);"><?= h($error) ?></div><?php endif; ?>
<div style="margin-top:1rem;font-size:0.8rem;color:var(--text-muted);">
    <strong>提示:</strong> 使用fetch_num时列名为数字，UNION注入需注意列数匹配
</div>
