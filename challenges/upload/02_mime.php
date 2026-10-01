<?php
// MIME校验绕过
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    // 漏洞：只检查 Content-Type 头，可伪造
    if (in_array($file['type'], $allowedTypes)) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newName = uniqid() . '.' . $ext;
        $dest = __DIR__ . '/../../uploads/' . $newName;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            if (preg_match('/^(php\d*|phtml|pht)$/i', $ext)) {
                renderChallengeSuccess($challenge, '伪造 MIME 后脚本文件通过了类型检查');
            }
            $msg = '<span style="color:var(--accent);">上传成功！' . h($newName) . '</span>';
        } else {
            $msg = '<span style="color:var(--danger);">上传失败</span>';
        }
    } else {
        $msg = '<span style="color:var(--danger);">文件类型不允许！当前类型：' . h($file['type']) . '</span>';
    }
}
?>

<p>图片上传（MIME校验绕过）</p>
<p>后端仅检查 HTTP 请求中的 Content-Type 头来判断文件类型。</p>

<form method="POST" enctype="multipart/form-data">
    <label>选择图片</label>
    <input type="file" name="file">
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    绕过方法：使用Burp Suite拦截请求，将 Content-Type 从 application/x-php 改为 image/jpeg。
</p>
