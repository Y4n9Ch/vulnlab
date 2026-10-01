<?php
// 基础SSTI
$result = null;
if (isset($_POST['template'])) {
    $tpl = $_POST['template'];
    // 漏洞：用户输入直接作为模板渲染
    // 模拟简单的模板引擎
    $result = $tpl;
    // 替换 {{表达式}}
    $executed = false;
    $result = preg_replace_callback('/\{\{(.+?)\}\}/s', function($m) use (&$executed) {
        try {
            ob_start();
            eval('echo ' . $m[1] . ';');
            $out = ob_get_clean();
            if (trim($out) !== '') $executed = true;
            return $out;
        } catch (\Throwable $e) {
            return '[Error]';
        }
    }, $result);
    if ($executed) {
        renderChallengeSuccess($challenge, '模板表达式被引擎当作代码求值');
    }
}
?>

<p>个人主页模板（存在SSTI漏洞）</p>
<p>用户输入直接作为模板内容渲染，可以注入模板表达式。</p>

<form method="POST">
    <label>页面模板内容</label>
    <textarea name="template" placeholder="输入模板内容，例如：你好，{{用户名}}"><?= h($_POST['template'] ?? '欢迎来到我的主页！') ?></textarea>
    <button type="submit" class="btn btn-primary">渲 染</button>
</form>

<?php if ($result !== null): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    试试：{{7*7}} 确认是否为SSTI
    <br>然后：{{phpinfo()}} 或 {{system('whoami')}}
</p>
