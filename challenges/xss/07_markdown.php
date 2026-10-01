<?php
// Markdown注入XSS
$output = null;
if (isset($_POST['md'])) {
    $md = $_POST['md'];
    // 简单的Markdown转换（不安全）
    $html = $md;
    $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);
    $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $html);
    $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
    $html = nl2br($html);
    $output = $html;
    if (is_string($output) && preg_match('/<script\b|\son\w+\s*=|javascript:/i', $output)) {
        renderChallengeSuccess($challenge, 'Markdown 渲染保留了可执行的脚本内容');
    }
}
?>

<p>Markdown渲染器（Markdown注入XSS）</p>
<p>简单的Markdown转换器，未过滤HTML标签。</p>

<form method="POST">
    <label>Markdown内容</label>
    <textarea name="md" placeholder="输入Markdown内容" rows="6"><?= h($_POST['md'] ?? '# 标题\n**粗体** 和 *斜体*\n[链接](http://example.com)') ?></textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    Markdown中可嵌入HTML：
    <br>[click](javascript:alert(1))
    <br>或直接写 &lt;img src=x onerror=alert(1)&gt;
</p>
