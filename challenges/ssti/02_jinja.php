<?php
// Jinja2风格SSTI
$output = null;
if (isset($_POST['template'])) {
    $template = $_POST['template'];

    // 简单的模板引擎（模拟Jinja2）
    $result = $template;

    // 变量替换
    $vars = [
        '{{name}}' => 'Guest',
        '{{user}}' => 'admin',
        '{{version}}' => '1.0',
    ];

    $result = str_replace(array_keys($vars), array_values($vars), $result);

    // 漏洞：支持表达式执行
    if (preg_match('/\{\{(.+?)\}\}/', $result, $matches)) {
        $expr = $matches[1];
        // 危险：执行表达式
        try {
            $evalResult = eval("return {$expr};");
            if ($evalResult !== null) {
                renderChallengeSuccess($challenge, 'Jinja 风格表达式被后端求值');
            }
            $result = str_replace($matches[0], $evalResult, $result);
        } catch (\Throwable $e) {
            $result = str_replace($matches[0], '[Render Error]', $result);
        }
    }

    $output = $result;
}
?>

<p>页面渲染（Jinja2风格SSTI）</p>
<p>模板引擎支持表达式执行，可注入PHP代码。</p>

<form method="POST">
    <label>模板内容</label>
    <textarea name="template" rows="4" placeholder="输入模板">Hello {{name}}, welcome! {{7*7}}</textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">SSTI Payload：</strong>
    <br>{{7*7}} — 测试表达式执行
    <br>{{phpinfo()}} — 执行PHP函数
    <br>{{system('id')}} — 执行系统命令
    <br>{{file_get_contents('/etc/passwd')}} — 读取文件
</div>
