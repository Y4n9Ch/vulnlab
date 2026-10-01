<?php
// 回调函数
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['func'], $_POST['param'])) {
    $func = $_POST['func'];
    $param = $_POST['param'];

    // 漏洞：call_user_func 参数可控
    ob_start();
    try {
        $result = call_user_func($func, $param);
        if ($result !== false) {
            renderChallengeSuccess($challenge, '可控回调被 call_user_func 调用');
            echo $result;
        }
    } catch (\Throwable $e) {
        echo 'Error: ' . $e->getMessage();
    }
    $output = ob_get_clean();
}
?>

<p>回调函数利用</p>
<p>后端使用 call_user_func 调用用户指定的函数和参数。</p>

<form method="POST">
    <label>函数名</label>
    <input type="text" name="func" placeholder="函数名" value="<?= h($_POST['func'] ?? 'strlen') ?>">
    <label>参数</label>
    <input type="text" name="param" placeholder="参数" value="<?= h($_POST['param'] ?? 'hello') ?>">
    <button type="submit" class="btn btn-primary">执 行</button>
</form>

<?php if ($output !== null): ?>
    <div class="result-box"><?= h($output) ?: '(无输出)' ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    call_user_func($func, $param) — 两个参数均可控。<br>
    试试：func=system, param=whoami 或 func=phpinfo, param=
</p>
