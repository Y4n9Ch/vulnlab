<?php
// SSTI过滤绕过
$result = null;
if (isset($_POST['template'])) {
    $tpl = $_POST['template'];

    // 过滤部分关键字
    $tpl = preg_replace('/\{\{/s', '', $tpl);
    $tpl = preg_replace('/\}\}/s', '', $tpl);
    $tpl = preg_replace('/_/s', '', $tpl);
    $tpl = preg_replace('/system/si', '', $tpl);
    $tpl = preg_replace('/exec/si', '', $tpl);
    $tpl = preg_replace('/passthru/si', '', $tpl);

    $result = $tpl;
    if (preg_match('/\{%.*?%\}|\{php\}.*?\{\/php\}|<\?php/s', $result)) {
        renderChallengeSuccess($challenge, '过滤器未覆盖的模板语法仍残留在输出中');
    }
}
?>

<p>用户输入展示（SSTI过滤绕过）</p>
<p>后端过滤了 {{、}}、_、system、exec、passthru 等关键字。</p>

<form method="POST">
    <label>输入内容</label>
    <textarea name="template" placeholder="输入内容"><?= h($_POST['template'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">提 交</button>
</form>

<?php if ($result !== null): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">过滤规则：</strong>
    <br>- 移除 {{ 和 }}
    <br>- 移除 _
    <br>- 移除 system、exec、passthru（大小写不敏感）
    <br><br>
    <span style="color:var(--text-muted);">
        绕过思路：
        <br>- 使用 {% %} 标签语法代替 {{ }}
        <br>- 字符串拼接绕过：sys.tem、syst'+'em
        <br>- 使用其他函数：shell_exec、popen、proc_open
        <br>- 用 {$_GET[cmd]} 直接执行
    </span>
</div>
