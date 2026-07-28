<?php
// 文件内容绕过 - 文件头检查
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // 检查文件头（魔数）
    $handle = fopen($file['tmp_name'], 'rb');
    $header = fread($handle, 8);
    fclose($handle);

    $isImage = false;
    if (substr($header, 0, 3) === "\xFF\xD8\xFF") $isImage = true; // JPEG
    if (substr($header, 0, 8) === "\x89PNG\r\n\x1a\n") $isImage = true; // PNG
    if (substr($header, 0, 3) === "GIF") $isImage = true; // GIF

    // 漏洞：只检查文件头，不检查扩展名
    if ($isImage) {
        $newName = uniqid() . '.' . $ext;
        $dest = __DIR__ . '/../../uploads/' . $newName;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $msg = '<span style="color:var(--accent);">上传成功！' . h($newName) . '</span>';
        } else {
            $msg = '<span style="color:var(--danger);">上传失败</span>';
        }
    } else {
        $msg = '<span style="color:var(--danger);">文件头校验失败！只允许上传图片文件。</span>';
    }
}
?>

<p>文件上传（文件头校验绕过）</p>
<p>后端检查文件的前几个字节（文件头/魔数）来判断是否为图片。</p>

<form method="POST" enctype="multipart/form-data">
    <label>选择文件</label>
    <input type="file" name="file">
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    绕过方法：在PHP文件开头添加图片文件头，如 GIF89a 或 PNG文件头。
    <br>例如：GIF89a&lt;?php phpinfo(); ?&gt;
</p>
