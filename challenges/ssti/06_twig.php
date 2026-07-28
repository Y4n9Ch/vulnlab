<?php
// Twig风格SSTI
$output = null;
if (isset($_POST['template'])) {
    $template = $_POST['template'];

    // 简单模拟Twig模板
    $result = $template;

    // 变量替换
    $result = str_replace('{{ name }}', 'Guest', $result);
    $result = str_replace('{{ user }}', 'admin', $result);

    // 漏洞：支持函数调用
    if (preg_match('/\{\{(.+?)\}\}/', $result, $matches)) {
        $expr = trim($matches[1]);
        // 危险：直接执行
        $funcResult = @eval("return ({$expr});");
        $result = str_replace($matches[0], $funcResult, $result);
    }

    // {% %} 代码块
    if (preg_match('/\{%(.+?)%\}/', $result, $matches)) {
        $code = trim($matches[1]);
        ob_start();
        @eval($code);
        $codeOutput = ob_get_clean();
        $result = str_replace($matches[0], $codeOutput, $result);
    }

    $output = $result;
}
?>

<p>页面渲染（Twig风格SSTI）</p>
<p>Twig模板引擎存在代码注入漏洞。</p>

<form method="POST">
    <label>模板内容</label>
    <textarea name="template" rows="4" placeholder="输入Twig模板">Hello {{ name }}! {{ 7*7 }}</textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">Twig SSTI：</strong>
    <br>{{ 7*7 }} — 表达式执行
    <br>{{ phpinfo() }} — 函数调用
    <br>{% system('id') %} — 代码块执行
    <br>{{ dump(_self) }} — 对象dump
</div>
