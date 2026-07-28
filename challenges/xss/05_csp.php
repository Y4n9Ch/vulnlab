<?php
// CSP绕过
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;");

$search = $_GET['q'] ?? $_POST['q'] ?? '';
?>
<p>搜索功能（存在CSP保护，需要绕过）</p>
<p>页面设置了CSP策略，限制了脚本来源。</p>

<form method="GET" style="display:flex; gap:0.5rem;">
    <input type="text" name="q" placeholder="搜索..." value="<?= h($search) ?>" style="flex:1;">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if ($search): ?>
    <div class="result-box">
        搜索结果：<strong><?= $search ?></strong>
    </div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">当前CSP策略：</strong>
    <br><code style="color:var(--text-muted);">default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;</code>
    <br><br>
    <strong style="color:var(--text-secondary);">分析：</strong>
    <br>- 允许内联脚本（unsafe-inline）
    <br>- 允许来自 cdn.jsdelivr.net 的脚本
    <br><br>
    <span style="color:var(--text-muted);">绕过思路：由于允许 unsafe-inline，可以直接使用内联事件处理器。</span>
</div>
