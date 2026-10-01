<?php
// Smarty风格SSTI
$output = null;
if (isset($_POST['template'])) {
    $template = $_POST['template'];

    // 简单模拟Smarty模板
    $result = $template;

    // 变量替换
    $result = str_replace('{$name}', 'Guest', $result);
    $result = str_replace('{$user}', 'admin', $result);

    // 漏洞：{php}标签执行代码
    if (preg_match('/\{php\}(.+?)\{\/php\}/s', $result, $matches)) {
        ob_start();
        try {
            eval($matches[1]);
            renderChallengeSuccess($challenge, '{php} 标签内的代码被执行');
        } catch (\Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
        $phpOutput = ob_get_clean();
        $result = str_replace($matches[0], $phpOutput, $result);
    }

    // {literal}标签绕过
    $result = str_replace(['{literal}', '{/literal}'], '', $result);

    $output = $result;
}
?>

<p>页面渲染（Smarty风格SSTI）</p>
<p>Smarty模板引擎的{php}标签可执行任意PHP代码。</p>

<form method="POST">
    <label>模板内容</label>
    <textarea name="template" rows="4" placeholder="输入Smarty模板">Hello {$name}! {php}echo 'Code executed!';{/php}</textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">Smarty SSTI：</strong>
    <br>{php}phpinfo();{/php} — PHP代码执行
    <br>{php}system('id');{/php} — 系统命令
    <br>{php}echo file_get_contents('/etc/passwd');{/php} — 文件读取
</div>
