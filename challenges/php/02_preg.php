<?php
// preg_replace /e 代码执行
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pattern'], $_POST['replacement'], $_POST['subject'])) {
    $pattern = $_POST['pattern'];
    $replacement = $_POST['replacement'];
    $subject = $_POST['subject'];

    // 漏洞：使用 /e 修饰符（PHP < 7.0）
    ob_start();
    try {
        // 模拟 preg_replace /e 的行为
        if (strpos($pattern, '/e') !== false) {
            // 手动实现 /e 的行为（因为新版PHP已移除）
            $cleanPattern = str_replace('/e', '/', $pattern);
            $result = preg_replace_callback($cleanPattern, function($matches) use ($replacement) {
                $code = str_replace('$0', $matches[0], $replacement);
                ob_start();
                eval('echo ' . $code . ';');
                return ob_get_clean();
            }, $subject);
            echo $result;
        } else {
            echo preg_replace($pattern, $replacement, $subject);
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    $output = ob_get_clean();
}
?>

<p>正则替换（preg_replace /e 代码执行）</p>
<p>后端使用 preg_replace 的 /e 修饰符，替换内容会被当作PHP代码执行。</p>

<form method="POST">
    <label>正则表达式</label>
    <input type="text" name="pattern" placeholder="/(.*)/e" value="<?= h($_POST['pattern'] ?? '/(.*)/') ?>">
    <label>替换内容</label>
    <input type="text" name="replacement" placeholder="替换为" value="<?= h($_POST['replacement'] ?? 'strtoupper("$0")') ?>">
    <label>目标字符串</label>
    <input type="text" name="subject" placeholder="被替换的字符串" value="<?= h($_POST['subject'] ?? 'hello world') ?>">
    <button type="submit" class="btn btn-primary">执 行</button>
</form>

<?php if ($output !== null): ?>
    <div class="result-box"><?= h($output) ?: '(无输出)' ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    /e 修饰符会将替换部分作为PHP代码执行。<br>
    试试：pattern=/(.*)/e, replacement=system('whoami'), subject=test
</p>
