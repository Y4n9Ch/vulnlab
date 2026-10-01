<?php
// ZIP文件路径穿越
$output = null;
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip'])) {
    $zipFile = $_FILES['zip']['tmp_name'];
    $zip = new ZipArchive();
    if ($zip->open($zipFile) === true) {
        // 漏洞：直接解压，不检查文件名
        $extractPath = $uploadDir . 'extracted/';
        if (!is_dir($extractPath)) mkdir($extractPath, 0777, true);
        $zip->extractTo($extractPath);
        $output = "ZIP解压成功！文件列表:\n";
        $slip = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_contains($name, '../') || str_contains($name, '..\\')) $slip = true;
            $output .= $name . "\n";
        }
        $zip->close();
        if ($slip) renderChallengeSuccess($challenge, '解压未校验条目路径，ZIP 包中的穿越文件名被接受');
    } else {
        $output = "无法打开ZIP文件";
    }
}
?>

<p>文件解压（ZIP路径穿越）</p>
<p>直接解压ZIP文件，不检查文件名中的路径穿越字符。</p>

<form method="POST" enctype="multipart/form-data">
    <label>上传ZIP文件</label>
    <input type="file" name="zip" accept=".zip" required>
    <button type="submit" class="btn btn-primary">解 压</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">ZIP Slip攻击：</strong>
    <br>创建包含 ../../evil.php 的ZIP文件
    <br>解压后文件会跳出目标目录
    <br>python -c "import zipfile; z=zipfile.ZipFile('evil.zip','w'); z.writestr('../../shell.php','&lt;?php phpinfo();?&gt;')"
</div>
