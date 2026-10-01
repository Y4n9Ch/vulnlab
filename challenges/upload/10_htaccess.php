<?php
// .htaccess上传绕过
$output = null;
$uploaded = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // 只允许上传图片
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($ext, $allowed)) {
        $target = 'uploads/' . $file['name'];
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $output = "文件上传成功: {$target}";
            $uploaded = true;
        }
    } else {
        // 检查是否是.htaccess文件
        if ($file['name'] === '.htaccess') {
            $target = 'uploads/.htaccess';
            move_uploaded_file($file['tmp_name'], $target);
            renderChallengeSuccess($challenge, '解析配置文件被上传，服务器解析规则被改写');
            $output = ".htaccess上传成功！现在可以上传任意扩展名的PHP文件了。";
        } else {
            $output = "不允许的文件类型: {$ext}";
        }
    }
}
?>

<p>文件上传（.htaccess绕过）</p>
<p>服务器只允许图片，但可上传.htaccess文件改变Apache解析规则。</p>

<form method="POST" enctype="multipart/form-data">
    <label>上传文件</label>
    <input type="file" name="file" required>
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">攻击步骤：</strong>
    <br>1. 上传 .htaccess 文件，内容：AddType application/x-httpd-php .jpg
    <br>2. 上传包含PHP代码的 .jpg 文件
    <br>3. 访问 .jpg 文件，Apache会将其作为PHP执行
</div>
