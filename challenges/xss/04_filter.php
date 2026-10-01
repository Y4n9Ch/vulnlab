<?php
// XSS过滤绕过
$search = $_GET['q'] ?? $_POST['q'] ?? '';
$filtered = $search;

// 过滤逻辑（不严格）
if ($filtered) {
    // 只过滤了 <script> 标签，大小写敏感
    $filtered = preg_replace('/<script>/i', '', $filtered);
    $filtered = preg_replace('/<\/script>/i', '', $filtered);
}
?>
<p>搜索功能（存在XSS，需要绕过过滤）</p>
<p>后端过滤了 &lt;script&gt; 标签，但过滤不够严格。</p>

<form method="GET" style="display:flex; gap:0.5rem;">
    <input type="text" name="q" placeholder="搜索..." value="<?= h($search) ?>" style="flex:1;">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if ($search): ?>
    <?php if (is_string($filtered) && preg_match('/<script\b|<[a-zA-Z][^<>]*\son\w+\s*=/i', $filtered)) renderChallengeSuccess($challenge, '过滤后的输出仍含可执行标记并被原样回显'); ?>
    <div class="result-box">
        搜索结果：<strong><?= $filtered ?></strong>
        <br><span style="color:var(--text-muted); font-size:0.8rem;">原始输入已过滤 script 标签</span>
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤规则：移除 &lt;script&gt; 和 &lt;/script&gt;（大小写不敏感，但只过滤一次）<br>
    绕过思路：双写 &lt;scrscriptipt&gt;、嵌套标签、事件属性等
</p>
