<?php
// 扩展名绕过 - 黑名单过滤
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // 漏洞：黑名单不完整
    $blacklist = ['php', 'asp', 'aspx', 'jsp', 'exe'];
    if (in_array($ext, $blacklist)) {
        $msg = '<span style="color:var(--danger);">不允许上传 .' . h($ext) . ' 文件！</span>';
    } else {
        $newName = uniqid() . '.' . $ext;
        $dest = __DIR__ . '/../../uploads/' . $newName;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $msg = '<span style="color:var(--accent);">上传成功！' . h($newName) . '</span>';
        } else {
            $msg = '<span style="color:var(--danger);">上传失败</span>';
        }
    }
}
?>

<p>文件上传（扩展名黑名单绕过）</p>
<p>后端使用黑名单过滤了 .php、.asp、.aspx、.jsp、.exe 等扩展名，但黑名单不完整。</p>

<form method="POST" enctype="multipart/form-data">
    <label>选择文件</label>
    <input type="file" name="file">
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    黑名单：php, asp, aspx, jsp, exe<br>
    绕过思路：试试 .php3, .php5, .php7, .phtml, .pht, .phps 等PHP可执行的扩展名。
</p>
