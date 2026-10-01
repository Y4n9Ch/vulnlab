<?php
// SVG注入XSS
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['svg'])) {
    $svg = $_POST['svg'];
    $msg = '<div style="border:1px solid var(--border);padding:1rem;border-radius:8px;">' . $svg . '</div>';
    if (is_string($svg) && preg_match('/<script\b|\son\w+\s*=|javascript:/i', $svg)) {
        renderChallengeSuccess($challenge, '含脚本的 SVG 内容被后端原样渲染输出');
    }
}
?>

<p>SVG图片渲染（SVG注入XSS）</p>
<p>后端直接渲染用户上传的SVG内容，SVG中可嵌入JavaScript。</p>

<form method="POST">
    <label>SVG代码</label>
    <textarea name="svg" placeholder="输入SVG代码"><?= h($_POST['svg'] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="50"><text x="10" y="30" font-size="20">Hello</text></svg>') ?></textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    SVG中可以嵌入事件处理器：
    <br>&lt;svg onload="alert(1)"&gt;
    <br>&lt;svg&gt;&lt;script&gt;alert(1)&lt;/script&gt;&lt;/svg&gt;
    <br>&lt;svg&gt;&lt;a xlink:href="javascript:alert(1)"&gt;&lt;text x="0" y="20"&gt;Click&lt;/text&gt;&lt;/a&gt;&lt;/svg&gt;
</p>
