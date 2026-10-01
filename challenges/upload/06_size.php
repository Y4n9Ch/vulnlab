<?php
// 大小写绕过文件上传
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = $file['name'];
    $ext = pathinfo($name, PATHINFO_EXTENSION);

    // 只检查小写扩展名（漏洞：没有先转小写）
    $blocked = ['php', 'phtml', 'php3'];

    if (in_array($ext, $blocked)) {
        $output = "不允许的文件类型: {$ext}";
    } else {
        $target = 'uploads/' . $name;
        move_uploaded_file($file['tmp_name'], $target);
        if (preg_match('/^(php\d*|phtml)$/i', $ext)) {
            renderChallengeSuccess($challenge, '大小写变体绕过了小写黑名单');
        }
        $output = "文件上传成功: {$target}";
    }
}
?>

<p>文件上传（大小写绕过）</p>
<p>黑名单只检查小写扩展名，使用大写或混合大小写可绕过。</p>

<form method="POST" enctype="multipart/form-data">
    <label>上传文件</label>
    <input type="file" name="file" required>
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    大小写绕过：
    <br>上传 shell.PhP 或 shell.pHp
    <br>Windows系统不区分大小写，Apache可能解析为PHP
</p>
