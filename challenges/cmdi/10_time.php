<?php
// 时间盲注命令注入
$output = null;
if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
    // 输出被抑制，只能通过时间延迟判断
    $start = microtime(true);
    @shell_exec($cmd . ' 2>/dev/null');
    $elapsed = microtime(true) - $start;
    $output = "命令执行完成，耗时: " . round($elapsed, 2) . " 秒";
    if ($elapsed >= 2) {
        renderChallengeSuccess($challenge, '注入的命令产生了可观测的执行延迟');
    }
}
?>

<p>系统检测（时间盲注命令注入）</p>
<p>命令执行结果不回显，只能通过时间延迟判断命令是否执行成功。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>检测命令</label>
    <input type="text" name="cmd" placeholder="输入命令" value="<?= h($_GET['cmd'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">检 测</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">时间盲注：</strong>
    <br>sleep 5 — 延迟5秒
    <br>ping -c 5 127.0.0.1 — ping5次
    <br>if [ $(id|grep root) ]; then sleep 5; fi — 条件延迟
</div>
