<?php
// Mutation XSS (mXSS)
$output = null;
if (isset($_POST['html'])) {
    $html = $_POST['html'];
    // 过滤了常见标签，但浏览器解析时会产生变异
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/on\w+\s*=/i', '', $html);
    $output = $html;
    if (is_string($output) && preg_match('/<(script|img|svg|iframe|math|noscript|style|template|form)\b/i', $output)) {
        renderChallengeSuccess($challenge, '过滤后的内容仍含可被浏览器变异执行的标签结构');
    }
}
?>

<p>HTML过滤器（Mutation XSS）</p>
<p>过滤器移除了script标签和事件属性，但浏览器解析HTML时可能产生变异。</p>

<form method="POST">
    <label>HTML内容</label>
    <textarea name="html" placeholder="输入HTML代码"><?= h($_POST['html'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">提 交</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">过滤规则：</strong>
    <br>- 移除 &lt;script&gt;...&lt;/script&gt;
    <br>- 移除 on开头的事件属性
    <br><br>
    <span style="color:var(--text-muted);">
        mXSS利用浏览器解析差异：
        <br>&lt;noscript&gt;&lt;p title="&lt;/noscript&gt;&lt;img src=x onerror=alert(1)&gt;"&gt;
        <br>浏览器会变异HTML结构，绕过过滤器。
    </span>
</div>
