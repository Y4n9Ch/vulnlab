<?php
// 沙箱逃逸
$result = null;
if (isset($_POST['template'])) {
    $tpl = $_POST['template'];

    // 模拟沙箱：只允许特定函数
    $allowed = ['strlen', 'strtolower', 'strtoupper', 'substr', 'str_replace', 'intval', 'floatval'];

    // 检查是否调用了不允许的函数
    if (preg_match_all('/(\w+)\s*\(/', $tpl, $matches)) {
        $called = $matches[1];
        $blocked = array_diff($called, $allowed);
        if (!empty($blocked)) {
            $result = '<span style="color:var(--danger);">沙箱拦截：不允许调用 ' . h(implode(', ', $blocked)) . '</span>';
        } else {
            ob_start();
            try {
                eval('echo ' . $tpl . ';');
                renderChallengeSuccess($challenge, '沙箱白名单内的表达式被当作代码执行');
            } catch (\Throwable $e) {
                echo 'Error: ' . $e->getMessage();
            }
            $result = ob_get_clean();
        }
    } else {
        ob_start();
        try {
            eval('echo ' . $tpl . ';');
            renderChallengeSuccess($challenge, '无函数调用的表达式被直接求值');
        } catch (\Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
        $result = ob_get_clean();
    }
}
?>

<p>模板渲染（沙箱逃逸）</p>
<p>模板引擎开启了沙箱模式，只允许调用特定的安全函数。</p>

<form method="POST">
    <label>模板表达式</label>
    <textarea name="template" placeholder="输入PHP表达式"><?= h($_POST['template'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($result): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">允许的函数：</strong>
    <br><code style="color:var(--text-muted);">strlen, strtolower, strtoupper, substr, str_replace, intval, floatval</code>
    <br><br>
    <span style="color:var(--text-muted);">
        沙箱逃逸思路：
        <br>- 利用PHP内部类（如 DirectoryIterator、FilesystemIterator）
        <br>- 利用反射（ReflectionClass、ReflectionFunction）
        <br>- 利用 PHP 的 $_GLOBALS、get_defined_vars() 等获取环境信息
        <br>- 利用 str_replace 等允许的函数组合构造危险调用
    </span>
</div>
