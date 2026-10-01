<?php
// 文件移动后缀绕过
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = $file['name'];

    // 先上传到临时目录
    $tempDir = 'uploads/temp/';
    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

    $tempPath = $tempDir . $name;
    move_uploaded_file($file['tmp_name'], $tempPath);

    // 检查扩展名（在移动之前）
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $blocked = ['php', 'phtml', 'php5', 'php7'];

    if (in_array($ext, $blocked)) {
        // 删除危险文件
        unlink($tempPath);
        $output = "不允许的文件类型: {$ext}";
    } else {
        // 移动到最终目录
        $finalPath = 'uploads/' . $name;
        rename($tempPath, $finalPath);
        if (preg_match('/^(pht|phps|phar|php4|php6|inc|shtml)$/i', $ext)) {
            renderChallengeSuccess($challenge, '黑名单之外的脚本扩展名通过移动流程落盘');
        }
        $output = "文件已保存: {$finalPath}";
    }
}
?>

<p>文件上传（移动绕过）</p>
<p>服务器先保存文件再检查扩展名，存在条件竞争窗口。</p>

<form method="POST" enctype="multipart/form-data">
    <label>上传文件</label>
    <input type="file" name="file" required>
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    利用时间窗口：
    <br>文件保存到temp目录后，删除前有短暂时间可访问
    <br>使用Burp Intruder持续发送请求访问临时文件
</p>
