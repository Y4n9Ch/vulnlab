<?php
// SVG文件XXE
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['svg'])) {
    $svg = file_get_contents($_FILES['svg']['tmp_name']);
    // 漏洞：直接解析SVG（SVG是XML格式）
    $xml = simplexml_load_string($svg);
    if ($xml) {
        $output = "SVG解析成功:\n" . $xml->asXML();
    } else {
        $output = "SVG解析失败";
    }
}
?>

<p>SVG图片查看（SVG XXE）</p>
<p>SVG是XML格式，上传恶意SVG可触发XXE漏洞。</p>

<form method="POST" enctype="multipart/form-data">
    <label>上传SVG文件</label>
    <input type="file" name="svg" accept=".svg" required>
    <button type="submit" class="btn btn-primary">上 传</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">恶意SVG内容：</strong>
    <br>&lt;?xml version="1.0"?&gt;
    <br>&lt;!DOCTYPE svg [&lt;!ENTITY xxe SYSTEM "file:///etc/passwd"&gt;]&gt;
    <br>&lt;svg xmlns="http://www.w3.org/2000/svg"&gt;&lt;text&gt;&amp;xxe;&lt;/text&gt;&lt;/svg&gt;
</div>
