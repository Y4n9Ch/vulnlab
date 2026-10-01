<?php
// 内联注释绕过
$db = getVulnDB();
$result = null;

if (isset($_POST['keyword'])) {
    $keyword = $_POST['keyword'];
    // WAF：过滤空格和部分关键字
    if (preg_match('/\bselect\b|\bunion\b|\bfrom\b|\bwhere\b/i', $keyword)) {
        $result = 'blocked';
    } else {
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
    }
    if (is_array($result) && is_string($_POST['keyword']) && preg_match('/\/\*!|%0b/i', $_POST['keyword'])) {
        renderChallengeSuccess($challenge, '内联注释绕过关键字正则WAF，注入查询成功返回数据');
    }
}
?>

<p>用户搜索（内联注释绕过）</p>
<p>WAF使用正则匹配关键字，利用MySQL内联注释绕过。</p>

<form method="POST">
    <label>搜索关键词</label>
    <input type="text" name="keyword" placeholder="搜索用户" value="<?= h($_POST['keyword'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if ($result === 'blocked'): ?>
    <div class="result-box" style="color:var(--danger);">WAF拦截！检测到恶意关键字。</div>
<?php elseif ($result === 'not_found'): ?>
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
    WAF使用 \b 单词边界匹配，但MySQL内联注释可以绕过。
    <br>例如：/*!select*/ /*!union*/ /*!from*/
    <br>或：%0b代替空格：%0b/*!union*/%0b/*!select*/%0b1,2,3
</p>
