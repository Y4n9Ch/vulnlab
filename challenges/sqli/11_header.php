<?php
// HTTP头注入 - 访问日志
$db = getVulnDB();
$result = null;

// 记录访问日志（漏洞：直接将HTTP头写入数据库）
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$ref = $_SERVER['HTTP_REFERER'] ?? 'none';
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// 漏洞点：未转义直接拼接
if (preg_match('/[\'"]/', $ua . $ref)) {
    renderChallengeSuccess($challenge, '请求头未过滤直接拼进INSERT语句，HTTP头注入生效');
}
$sql = "INSERT INTO logs (ip, user_agent, url) VALUES ('$ip', '$ua', '$ref')";
try {
    $db->exec($sql);
} catch (Exception $e) {
    // 静默处理
}

// 查询最近日志
$logs = $db->query("SELECT * FROM logs ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>

<p>访问日志记录系统（存在HTTP头注入漏洞）</p>
<p>后端将 User-Agent 和 Referer 头直接写入数据库，未做任何过滤。</p>

<div class="result-box">
    <strong>最近访问日志：</strong>
    <?php if (empty($logs)): ?>
        <br><span style="color:var(--text-muted);">暂无日志</span>
    <?php else: ?>
        <table>
            <tr><th>ID</th><th>IP</th><th>User-Agent</th><th>Referer</th><th>时间</th></tr>
            <?php foreach ($logs as $row): ?>
            <tr>
                <td><?= h($row['id']) ?></td>
                <td><?= h($row['ip']) ?></td>
                <td><?= h($row['user_agent']) ?></td>
                <td><?= h($row['url']) ?></td>
                <td><?= h($row['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    提示：用 Burp Suite 修改 User-Agent 头，在其中注入SQL语句。
    <br>例如：', '127.0.0.1'); UPDATE users_info SET role='admin' WHERE username='user1'; --
</p>
