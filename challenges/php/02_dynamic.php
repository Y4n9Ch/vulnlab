<?php
// 动态函数调用
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['func'], $_POST['param'])) {
    $func = $_POST['func'];
    $param = $_POST['param'];

    // 漏洞：变量函数调用
    ob_start();
    try {
        if (is_callable($func)) {
            $result = $func($param);
            if ($result !== null && $result !== false) {
                renderChallengeSuccess($challenge, '变量函数调用执行了受控函数名');
                echo $result;
            }
        } else {
            echo '不可调用的函数：' . $func;
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    $output = ob_get_clean();
}
?>

<p>动态函数调用</p>
<p>后端使用变量函数调用方式 $func($param)，两个参数均可控。</p>

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
    PHP变量函数：$func = 'system'; $func('whoami'); 等同于 system('whoami');<br>
    试试：func=system, param=whoami 或 func=exec, param=id
</p>
