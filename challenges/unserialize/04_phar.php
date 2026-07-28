<?php
// Phar反序列化
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
    $filename = $_POST['filename'];
    // 漏洞：文件存在性检查可触发 phar:// 反序列化
    $path = __DIR__ . '/../../uploads/' . $filename;
    if (file_exists($path)) {
        $result = '<span style="color:var(--accent);">文件存在：' . h($filename) . '</span>';
    } else {
        $result = '<span style="color:var(--warning);">文件不存在：' . h($filename) . '</span>';
    }
}

// 上传Phar文件
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['phar_file'])) {
    $file = $_FILES['phar_file'];
    $dest = __DIR__ . '/../../uploads/' . $file['name'];
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $result = '<span style="color:var(--accent);">Phar文件上传成功：' . h($file['name']) . '</span>';
    }
}
?>

<p>文件管理器（Phar反序列化）</p>
<p>文件存在性检查功能可触发 phar:// 协议的反序列化。</p>

<h4 style="color:var(--accent); margin-bottom:0.5rem;">上传Phar文件</h4>
<form method="POST" enctype="multipart/form-data">
    <label>选择Phar文件</label>
    <input type="file" name="phar_file">
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<h4 style="color:var(--accent); margin:1rem 0 0.5rem;">检查文件是否存在</h4>
<form method="POST">
    <label>文件名</label>
    <input type="text" name="filename" placeholder="example.jpg" value="<?= h($_POST['filename'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">检 查</button>
</form>

<?php if ($result): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">Phar反序列化利用：</strong>
    <pre style="color:var(--text-muted);">// 生成phar文件
$phar = new Phar('evil.phar');
$phar->startBuffering();
$phar->setStub('&lt;?php __HALT_COMPILER(); ?&gt;');
$phar->setMetadata($payload); // 序列化对象
$phar->addFromString('test.txt', 'test');
$phar->stopBuffering();

// 触发：filename=phar://evil.phar/test.txt</pre>
</div>
