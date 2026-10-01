<?php
// 事件处理绕过
$search = $_GET['q'] ?? $_POST['q'] ?? '';
$filtered = $search;

if ($filtered) {
    // 过滤常见事件属性
    $events = ['onclick', 'onload', 'onerror', 'onsubmit', 'onfocus',
               'onblur', 'onchange', 'onkeydown', 'onkeyup', 'onkeypress',
               'onmouseover', 'onmouseout', 'onmouseenter', 'onmouseleave',
               'onmousedown', 'onmouseup', 'ondragstart', 'ondragend'];
    foreach ($events as $evt) {
        $filtered = preg_replace('/' . $evt . '/i', '', $filtered);
    }
}
?>
<p>用户输入展示（存在XSS，事件属性被过滤）</p>
<p>后端过滤了常见的事件处理器属性。</p>

<form method="GET" style="display:flex; gap:0.5rem;">
    <input type="text" name="q" placeholder="输入内容..." value="<?= h($search) ?>" style="flex:1;">
    <button type="submit" class="btn btn-primary">提 交</button>
</form>

<?php if ($search): ?>
    <?php if (is_string($filtered) && preg_match('/<[a-zA-Z][^<>]*\son\w+\s*=/i', $filtered)) renderChallengeSuccess($challenge, '过滤后的事件处理器仍被原样回显到页面'); ?>
    <div class="result-box">
        显示内容：<?= $filtered ?>
    </div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">已过滤事件属性：</strong>
    <br><code style="color:var(--text-muted); font-size:0.75rem;">onclick, onload, onerror, onsubmit, onfocus, onblur, onchange, onkeydown, onkeyup, onkeypress, onmouseover, onmouseout, onmouseenter, onmouseleave, onmousedown, onmouseup, ondragstart, ondragend</code>
    <br><br>
    <span style="color:var(--text-muted);">绕过思路：试试 onfocus、onanimationstart、ontouchstart、onpointerover 等较少见的事件。
    <br>或者利用 autofocus 属性配合 onfocus 事件。</span>
</div>
