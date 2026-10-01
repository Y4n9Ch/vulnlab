<?php
// WAF绕过SSTI
$output = null;
if (isset($_POST['template'])) {
    $template = $_POST['template'];

    // WAF过滤
    $blocked = ['system', 'exec', 'passthru', 'shell_exec', 'eval', 'assert', 'file_get_contents', 'phpinfo'];
    foreach ($blocked as $word) {
        if (stripos($template, $word) !== false) {
            $output = "WAF拦截: 检测到禁用函数 {$word}";
            break;
        }
    }

    if (!$output) {
        // 简单模板引擎
        $result = $template;
        if (preg_match('/\{\{(.+?)\}\}/', $result, $matches)) {
            $expr = $matches[1];
            try {
                $evalResult = eval("return {$expr};");
                if ($evalResult !== null) {
                    renderChallengeSuccess($challenge, 'WAF 未覆盖的函数调用完成表达式求值');
                }
                $result = str_replace($matches[0], $evalResult, $result);
            } catch (\Throwable $e) {
                $result = str_replace($matches[0], '[Render Error]', $result);
            }
        }
        $output = $result;
    }
}
?>

<p>页面渲染（WAF绕过SSTI）</p>
<p>WAF过滤了常见危险函数，需使用替代方法绕过。</p>

<form method="POST">
    <label>模板内容</label>
    <textarea name="template" rows="4" placeholder="输入模板">{{7*7}}</textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">WAF绕过方法：</strong>
    <br>{{`id`}} — 反引号执行
    <br>{{$_GET[0]($_GET[1])}} — 动态函数调用
    <br>{{['id']|map('system')}} — 数组方法
    <br>{{['id']|filter('system')}} — 过滤器
    <br>字符串拼接：sys"+"tem
</div>
