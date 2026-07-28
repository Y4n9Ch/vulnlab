<?php
// 无字母数字RCE
$output = null;
$blocked = false;

if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];

    // 过滤所有字母和数字
    if (preg_match('/[a-zA-Z0-9]/', $cmd)) {
        $blocked = true;
    }

    if ($blocked) {
        $output = 'blocked';
    } else {
        // 漏洞：eval执行用户输入
        ob_start();
        try {
            eval($cmd);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
        $output = ob_get_clean();
    }
}
?>

<p>代码执行（无字母数字RCE）</p>
<p>后端过滤了所有字母和数字字符，但执行用户输入的PHP代码。</p>

<form method="POST">
    <label>PHP代码</label>
    <textarea name="cmd" placeholder="输入PHP代码（不包含字母和数字）"><?= h($_POST['cmd'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">执 行</button>
</form>

<?php if ($output === 'blocked'): ?>
    <div class="result-box" style="color:var(--danger);">不允许使用字母和数字！</div>
<?php elseif ($output !== null): ?>
    <div class="result-box"><?= h($output) ?: '(无输出)' ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤了所有字母 (a-zA-Z) 和数字 (0-9)<br>
    绕过思路：利用 ${_}、XOR、取反等方式构造字母
    <br>例如：$_=chr(115).chr(121).chr(115).chr(116).chr(101).chr(109); $_('whoami');
    <br>或使用取反：(～某一串十六进制)('参数')
</p>
