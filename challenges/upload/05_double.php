<?php
// 双扩展名绕过
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];

    // 漏洞：只删除一次 .php，可双写绕过
    $filename = str_replace('.php', '', $filename);
    $filename = str_replace('.phtml', '', $filename);

    $dest = __DIR__ . '/../../uploads/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        if (str_contains(strtolower($filename), '.php') || str_contains(strtolower($filename), '.phtml')) {
            renderChallengeSuccess($challenge, '单次删除后文件名中仍保留了脚本扩展名');
        }
        $msg = '<span style="color:var(--accent);">上传成功！文件名：' . h($filename) . '</span>';
    } else {
        $msg = '<span style="color:var(--danger);">上传失败</span>';
    }
}
?>

<p>文件上传（双写绕过）</p>
<p>后端会删除文件名中的 .php 扩展名，但只删除一次。</p>

<form method="POST" enctype="multipart/form-data">
    <label>选择文件</label>
    <input type="file" name="file">
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    过滤逻辑：str_replace('.php', '', $filename) — 只替换一次<br>
    绕过方法：shell.pphphp → 删除一次 .php 后变成 shell.php
</p>
