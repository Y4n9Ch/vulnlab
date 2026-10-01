<?php
// 环境变量注入
$output = null;
if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
    // 过滤了常见命令
    $blocked = ['cat', 'ls', 'dir', 'type', 'whoami', 'id', 'pwd'];
    foreach ($blocked as $word) {
        if (stripos($cmd, $word) !== false) {
            $output = "命令 {$word} 被禁止！";
            break;
        }
    }
    if (!$output) {
        $output = shell_exec($cmd . ' 2>&1');
        if ($output !== null && $output !== false && preg_match('#/|\$\{|\\\\|\?|\*\[|ca\?|c[a]t#', $cmd)) {
            renderChallengeSuccess($challenge, '黑名单命令被变形写法绕过并执行');
        }
    }
}
?>

<p>系统工具（环境变量注入）</p>
<p>过滤了常见命令，可利用环境变量或路径绕过。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>命令</label>
    <input type="text" name="cmd" placeholder="输入命令" value="<?= h($_GET['cmd'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">执 行</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">绕过方法：</strong>
    <br>/bin/cat /etc/passwd — 使用绝对路径
    <br>c\u\r\u /etc/passwd — 变量拼接
    <br>${PATH:0:1}etc${PATH:0:1}passwd — 环境变量构造路径
    <br>ca? /etc/passwd — 通配符
</div>
