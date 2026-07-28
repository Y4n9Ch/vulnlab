<?php
// 中文字符绕过文件上传
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = $file['name'];

    // 获取最后一个点后面的扩展名
    $parts = explode('.', $name);
    $ext = strtolower(end($parts));

    $blocked = ['php', 'phtml', 'php5'];

    if (in_array($ext, $blocked)) {
        $output = "不允许的文件类型: {$ext}";
    } else {
        $target = 'uploads/' . $name;
        move_uploaded_file($file['tmp_name'], $target);
        $output = "文件上传成功: {$target}";
    }
}
?>

<p>文件上传（中文字符绕过）</p>
<p>使用中文点号或其他特殊Unicode字符可绕过扩展名检测。</p>

<form method="POST" enctype="multipart/form-data">
    <label>上传文件</label>
    <input type="file" name="file" required>
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    中文字符绕过：
    <br>shell.php。jpg（中文句号）
    <br>shell.php\x00.jpg（空字节截断）
    <br>利用编码差异绕过扩展名检测
</p>
