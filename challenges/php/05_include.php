<?php
// 动态包含执行
$output = null;
if (isset($_GET['func'])) {
    $func = $_GET['func'];
    // 漏洞：用户控制的函数名
    $allowed = ['strlen', 'strtoupper', 'strtolower', 'md5', 'sha1'];
    if (in_array($func, $allowed)) {
        $input = $_GET['input'] ?? '';
        $output = $func($input);
    } else {
        // 漏洞：错误信息泄露
        $output = "函数 {$func} 不在白名单中。允许的函数: " . implode(', ', $allowed);
    }
}
?>

<p>函数调用（动态函数执行）</p>
<p>白名单可被枚举，且错误信息泄露了允许的函数列表。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>函数名</label>
    <input type="text" name="func" placeholder="函数名" value="<?= h($_GET['func'] ?? '') ?>">
    <label>参数</label>
    <input type="text" name="input" placeholder="输入参数" value="<?= h($_GET['input'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">执 行</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    安全问题：
    <br>1. 泄露了白名单函数列表
    <br>2. 可尝试利用白名单函数的副作用
    <br>3. md5/sha1可用于信息收集
</p>
