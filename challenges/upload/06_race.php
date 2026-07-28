<?php
// 条件竞争 - 先保存再检查
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newName = 'uploads/' . uniqid() . '.' . $ext;
    $dest = __DIR__ . '/../../' . $newName;

    // 漏洞：先保存文件，再检查扩展名
    move_uploaded_file($file['tmp_name'], $dest);

    // 检查扩展名（有延迟）
    usleep(100000); // 100ms 延迟，增大竞争窗口

    $badExts = ['php', 'phtml', 'php3', 'php5'];
    if (in_array($ext, $badExts)) {
        // 删除不合法文件
        unlink($dest);
        $msg = '<span style="color:var(--danger);">文件类型不允许，已删除！</span>';
    } else {
        $msg = '<span style="color:var(--accent);">上传成功！' . h($newName) . '</span>';
    }
}
?>

<p>文件上传（条件竞争）</p>
<p>后端先保存文件，再检查扩展名，如果不合法则删除。存在时间窗口可利用。</p>

<form method="POST" enctype="multipart/form-data">
    <label>选择文件</label>
    <input type="file" name="file">
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    漏洞流程：保存文件 → 延迟100ms → 检查扩展名 → 不合法则删除<br>
    攻击方法：使用Burp Intruder持续上传PHP文件，同时用另一个Intruder持续请求上传后的文件路径。
    <br>在文件被删除之前访问到它，即可执行代码。
</p>
