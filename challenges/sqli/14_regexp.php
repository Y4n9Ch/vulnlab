<?php
// 正则注入
$db = getVulnDB();
$result = null;

if (isset($_POST['pattern'])) {
    $pattern = $_POST['pattern'];
    // 漏洞：REGEXP注入
    $sql = "SELECT id, username, email FROM users_info WHERE username REGEXP '$pattern'";
    try {
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount() > 0) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = 'not_found';
        }
    } catch (Exception $e) {
        $result = 'error:' . $e->getMessage();
    }
    if (is_string($pattern) && preg_match('/[\'"]/', $pattern) && preg_match('/\||\^|\b(or|and)\b/i', $pattern)) {
        renderChallengeSuccess($challenge, '正则的或运算配合引号闭合构造永真条件，REGEXP注入成立');
    }
}
?>

<p>用户搜索（正则注入）</p>
<p>后端使用REGEXP进行正则匹配，存在注入点。</p>

<form method="POST">
    <label>正则表达式</label>
    <input type="text" name="pattern" placeholder="例如：^a" value="<?= h($_POST['pattern'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if ($result === 'not_found'): ?>
    <div class="result-box" style="color:var(--warning);">未找到匹配用户</div>
<?php elseif (is_string($result) && strpos($result, 'error:') === 0): ?>
    <div class="result-box" style="color:var(--danger);">错误：<?= h(substr($result, 6)) ?></div>
<?php elseif (is_array($result)): ?>
    <div class="result-box">
        <table><tr><th>ID</th><th>用户名</th><th>邮箱</th></tr>
        <?php foreach ($result as $row): ?>
        <tr><td><?= h($row['id']) ?></td><td><?= h($row['username']) ?></td><td><?= h($row['email']) ?></td></tr>
        <?php endforeach; ?></table>
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    REGEXP注入：利用正则的 | (或) 运算符。
    <br>例如：^a|1=1 匹配所有以a开头或条件为真的记录。
</p>
