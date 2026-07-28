<?php
// eval代码执行
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = $_POST['code'];
    // 漏洞：直接eval用户输入
    ob_start();
    try {
        eval($code);
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    $output = ob_get_clean();
}
?>

<p>PHP代码执行（eval）</p>
<p>后端将用户输入直接传入 eval() 函数执行。</p>

<form method="POST">
    <label>PHP代码</label>
    <textarea name="code" placeholder="输入PHP代码"><?= h($_POST['code'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">执 行</button>
</form>

<?php if ($output !== null): ?>
    <div class="result-box"><?= h($output) ?: '(无输出)' ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    试试：phpinfo(); 或 system('whoami'); 或 echo file_get_contents('/etc/passwd');
</p>
