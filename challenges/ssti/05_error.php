<?php
// 错误信息泄露SSTI
$output = null;
if (isset($_POST['template'])) {
    $template = $_POST['template'];

    // 简单模板引擎
    $result = $template;

    // 变量替换
    $result = str_replace('${name}', 'Guest', $result);

    // 漏洞：错误信息泄露模板细节
    if (preg_match('/\$\{(.+?)\}/', $result, $matches)) {
        $expr = $matches[1];
        try {
            $evalResult = eval("return {$expr};");
            if ($evalResult !== null) {
                renderChallengeSuccess($challenge, '模板占位符表达式被后端求值');
            }
            $result = str_replace($matches[0], $evalResult, $result);
            $output = $result;
        } catch (\Throwable $e) {
            // 泄露错误信息
            $output = "模板渲染错误:\n" . $e->getMessage();
            $output .= "\n\n表达式: {$expr}";
        }
    } else {
        $output = $result;
    }
}
?>

<p>页面渲染（错误信息SSTI）</p>
<p>错误信息泄露模板引擎细节，辅助构造攻击Payload。</p>

<form method="POST">
    <label>模板内容</label>
    <textarea name="template" rows="4" placeholder="输入模板">Hello ${name}! ${invalid_func()}</textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    利用错误信息：
    <br>1. 故意触发错误获取服务器信息
    <br>2. 确认模板引擎类型和版本
    <br>3. 发现可用的函数和类
</p>
