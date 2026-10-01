<?php
// 双写绕过注入
$db = getVulnDB();
$result = null;
$blocked = false;

if (isset($_POST['keyword'])) {
    $keyword = $_POST['keyword'];
    // WAF：删除关键字（只删一次）
    $keyword = str_replace('select', '', $keyword);
    $keyword = str_replace('union', '', $keyword);
    $keyword = str_replace('from', '', $keyword);

    $sql = "SELECT id, username, email, role FROM users_info WHERE username LIKE '%$keyword%'";
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
    if (is_array($result) && is_string($_POST['keyword']) && preg_match('/sel[\s\S]*select|un[\s\S]*union|fr[\s\S]*from/i', $_POST['keyword'])) {
        renderChallengeSuccess($challenge, '双写关键字绕过只删一次的WAF，注入语句执行并返回了数据');
    }
}
?>

<p>用户搜索（双写绕过注入）</p>
<p>WAF删除关键字但只删除一次，可双写绕过。</p>

<form method="POST">
    <label>搜索关键词</label>
    <input type="text" name="keyword" placeholder="搜索用户" value="<?= h($_POST['keyword'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if ($result === 'not_found'): ?>
    <div class="result-box" style="color:var(--warning);">未找到匹配用户</div>
<?php elseif (is_string($result) && strpos($result, 'error:') === 0): ?>
    <div class="result-box" style="color:var(--danger);">错误：<?= h(substr($result, 6)) ?></div>
<?php elseif (is_array($result)): ?>
    <div class="result-box">
        <table><tr><th>ID</th><th>用户名</th><th>邮箱</th><th>角色</th></tr>
        <?php foreach ($result as $row): ?>
        <tr><td><?= h($row['id']) ?></td><td><?= h($row['username']) ?></td><td><?= h($row['email']) ?></td><td><?= h($row['role']) ?></td></tr>
        <?php endforeach; ?></table>
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    WAF规则：str_replace删除 select, union, from（只删一次）<br>
    双写绕过：selselectect → 删除select → select
    <br>ununionion → 删除union → union
</p>
