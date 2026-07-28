<?php
// 存储型XSS - 留言板
$db = getVulnDB();

// 处理留言提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $content = $_POST['content'];
    $username = $_SESSION['username'] ?? 'anonymous';
    // 漏洞：未过滤直接存入数据库
    $stmt = $db->prepare("INSERT INTO messages (username, content) VALUES (?, ?)");
    $stmt->execute([$username, $content]);
}

// 获取所有留言
$messages = $db->query("SELECT * FROM messages ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<p>留言板（存在存储型XSS）</p>
<p>提交的留言会存储到数据库，所有用户访问时都会看到。</p>

<form method="POST">
    <label>留言内容</label>
    <textarea name="content" placeholder="说点什么..."></textarea>
    <button type="submit" class="btn btn-primary">发 表</button>
</form>

<div class="result-box">
    <strong>留言列表：</strong>
    <?php if (empty($messages)): ?>
        <br><span style="color:var(--text-muted);">暂无留言</span>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div style="border-bottom:1px solid var(--border); padding:0.5rem 0;">
                <span style="color:var(--accent);"><?= h($msg['username']) ?></span>
                <span style="color:var(--text-muted); font-size:0.75rem;"><?= h($msg['created_at']) ?></span>
                <br><?= $msg['content'] ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
