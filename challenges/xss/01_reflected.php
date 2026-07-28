<?php
// 反射型XSS - 搜索功能
$search = $_GET['q'] ?? $_POST['q'] ?? '';
?>
<p>站内搜索功能（存在反射型XSS）</p>
<p>搜索关键词直接回显到页面，未做过滤。</p>

<form method="GET" style="display:flex; gap:0.5rem;">
    <input type="text" name="q" placeholder="搜索内容..." value="<?= h($search) ?>" style="flex:1;">
    <button type="submit" class="btn btn-primary">搜 索</button>
</form>

<?php if ($search): ?>
    <div class="result-box">
        搜索结果：未找到 "<strong><?= $search ?></strong>" 的相关内容
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    提示：搜索框中输入的内容会直接显示在页面上。试试注入 &lt;script&gt; 标签。
</p>
