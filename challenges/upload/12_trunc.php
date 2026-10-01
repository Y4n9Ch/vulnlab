<?php
// 截断上传绕过
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = $file['name'];
    $saveName = $_POST['filename'] ?? $name;

    // 检查最终保存的文件扩展名
    $ext = strtolower(pathinfo($saveName, PATHINFO_EXTENSION));
    $blocked = ['php', 'phtml', 'php5'];

    if (in_array($ext, $blocked)) {
        $output = "不允许的文件类型: {$ext}";
    } else {
        $target = 'uploads/' . $saveName;
        if (str_contains($saveName, "\0")) {
            renderChallengeSuccess($challenge, '空字节截断构造被接受为保存文件名');
        }
        try {
            move_uploaded_file($file['tmp_name'], $target);
        } catch (\ValueError $e) {
            // PHP 8 对含空字节的路径抛出 ValueError，这里模拟旧版截断行为避免整页崩溃
        }
        $output = "文件保存为: {$target}";
    }
}
?>

<p>文件上传（截断绕过）</p>
<p>可自定义保存文件名，利用空字节截断绕过扩展名检测。</p>

<form method="POST" enctype="multipart/form-data">
    <label>上传文件</label>
    <input type="file" name="file" required>
    <label>保存文件名</label>
    <input type="text" name="filename" placeholder="自定义文件名" value="<?= h($_POST['filename'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    截断绕过（PHP < 5.3.4）：
    <br>文件名: shell.php%00.jpg
    <br>系统在空字节处截断，实际保存为 shell.php
</p>
