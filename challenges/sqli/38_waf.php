<?php
// WAF绕过 - 带过滤的注入
$db = getVulnDB();
$result = null;
$blocked = false;

if (isset($_POST['keyword'])) {
    $keyword = $_POST['keyword'];

    // WAF 过滤
    $blacklist = ['select', 'union', 'from', 'where', 'and', 'or', '--', '#', '/*'];
    $lower = strtolower($keyword);
    foreach ($blacklist as $word) {
        if (strpos($lower, $word) !== false) {
            $blocked = true;
            break;
        }
    }

    if ($blocked) {
        $result = 'blocked';
    } else {
        // 漏洞：WAF过滤不严格，可以绕过
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
        if (is_array($result) && is_string($keyword) && preg_match('/\|\||&&/', $keyword)) {
            renderChallengeSuccess($challenge, '用逻辑运算符替换被拦截的关键字绕过WAF，搜索返回了全部用户');
        }
    }
}
?>

<p>用户搜索系统（存在WAF，需要绕过）</p>
<p>后端有WAF过滤，拦截了 select、union、from、where、and、or 等关键字。</p>

<form method="POST">
    <label>搜索关键词</label>
    <input type="text" name="keyword" placeholder="搜索用户" value="<?= h($_POST['keyword'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if ($result === 'blocked'): ?>
    <div class="result-box" style="color: var(--danger);">&#9888; WAF拦截！检测到恶意关键字。</div>
<?php elseif ($result === 'not_found'): ?>
    <div class="result-box" style="color: var(--warning);">未找到匹配用户</div>
<?php elseif (is_string($result) && strpos($result, 'error:') === 0): ?>
    <div class="result-box" style="color: var(--danger);">错误：<?= h(substr($result, 6)) ?></div>
<?php elseif (is_array($result)): ?>
    <div class="result-box">
        <table>
            <tr><th>ID</th><th>用户名</th><th>邮箱</th><th>角色</th></tr>
            <?php foreach ($result as $row): ?>
            <tr>
                <td><?= h($row['id']) ?></td>
                <td><?= h($row['username']) ?></td>
                <td><?= h($row['email']) ?></td>
                <td><?= h($row['role']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    WAF黑名单：select, union, from, where, and, or, --, #, /*<br>
    绕过思路：大小写混写 SeLeCt、双写 selselectect、内联注释 /*!select*/、%0a换行等
</p>
