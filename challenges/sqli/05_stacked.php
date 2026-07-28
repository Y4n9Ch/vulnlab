<?php
// 堆叠注入 - 消息查询
$db = getVulnDB();
$result = null;
$extraResult = null;

if (isset($_POST['keyword'])) {
    $keyword = $_POST['keyword'];
    // 漏洞：使用 mysqli_multi_query 允许堆叠
    $sql = "SELECT id, username, content, created_at FROM messages WHERE content LIKE '%$keyword%'";
    // PDO 模拟多语句执行
    try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $db->query($sql);
        if ($stmt) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // 尝试执行第二条语句（堆叠注入的关键）
        if (strpos($keyword, ';') !== false) {
            $parts = explode(';', $sql);
            if (count($parts) > 1 && !empty(trim($parts[1]))) {
                try {
                    $db->query(trim($parts[1]));
                    $extraResult = '第二条语句执行成功';
                } catch (Exception $e) {
                    $extraResult = '第二条语句：' . $e->getMessage();
                }
            }
        }
    } catch (Exception $e) {
        $result = 'error:' . $e->getMessage();
    }
}
?>

<p>留言板搜索（存在堆叠注入漏洞）</p>
<p>后端允许多条SQL语句同时执行，可以 INSERT、UPDATE、DELETE 等操作。</p>

<form method="POST">
    <label>搜索关键词</label>
    <input type="text" name="keyword" placeholder="搜索留言内容" value="<?= h($_POST['keyword'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if (is_string($result) && strpos($result, 'error:') === 0): ?>
    <div class="result-box" style="color: var(--danger);">错误：<?= h(substr($result, 6)) ?></div>
<?php elseif (is_array($result)): ?>
    <div class="result-box">
        <?php if (empty($result)): ?>
            <span style="color:var(--text-muted);">未找到匹配的留言</span>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>用户</th><th>内容</th><th>时间</th></tr>
                <?php foreach ($result as $row): ?>
                <tr>
                    <td><?= h($row['id']) ?></td>
                    <td><?= h($row['username']) ?></td>
                    <td><?= h($row['content']) ?></td>
                    <td><?= h($row['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($extraResult): ?>
    <div class="result-box" style="color: var(--warning);">堆叠执行结果：<?= h($extraResult) ?></div>
<?php endif; ?>
