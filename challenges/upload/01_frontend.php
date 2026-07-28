<?php
// 前端校验绕过 - 文件上传
$msg = null;
$uploaded = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newName = uniqid() . '.' . $ext;
    $dest = __DIR__ . '/../../uploads/' . $newName;

    // 漏洞：后端完全没有验证，只有前端JS校验
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $uploaded = '/uploads/' . $newName;
        $msg = '<span style="color:var(--accent);">上传成功！文件路径：' . h($uploaded) . '</span>';
        // 记录
        $db = getVulnDB();
        $db->exec("INSERT INTO uploads (filename, filepath) VALUES ('" . addslashes($file['name']) . "', '" . addslashes($uploaded) . "')");
    } else {
        $msg = '<span style="color:var(--danger);">上传失败</span>';
    }
}
?>

<p>头像上传（前端校验绕过）</p>
<p>上传功能仅在前端JavaScript校验文件类型，后端无任何验证。</p>

<form method="POST" enctype="multipart/form-data" id="uploadForm">
    <label>选择文件</label>
    <input type="file" name="file" id="fileInput" accept=".jpg,.png,.gif">
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<script>
// 前端校验（可绕过）
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    var file = document.getElementById('fileInput').files[0];
    if (file) {
        var ext = file.name.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif'].indexOf(ext) === -1) {
            e.preventDefault();
            alert('只允许上传图片文件！');
        }
    }
});
</script>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    绕过方法：禁用JavaScript、使用Burp Suite直接修改请求、或在浏览器控制台删除事件监听。
</p>
