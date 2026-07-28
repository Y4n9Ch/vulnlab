<?php
// 基础XXE
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xml'])) {
    $xml = $_POST['xml'];
    // 漏洞：允许加载外部实体
    libxml_disable_entity_loader(false);
    $doc = new DOMDocument();
    @$doc->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD);
    $result = $doc->saveXML();
}
?>

<p>XML解析接口（存在XXE漏洞）</p>
<p>后端解析XML时允许加载外部实体，可读取服务器文件。</p>

<form method="POST">
    <label>XML数据</label>
    <textarea name="xml" placeholder="输入XML数据"><?= h($_POST['xml'] ?? '<?xml version="1.0" encoding="UTF-8"?>
<user>
    <name>test</name>
    <email>test@test.com</email>
</user>') ?></textarea>
    <button type="submit" class="btn btn-primary">解 析</button>
</form>

<?php if ($result): ?>
    <div class="result-box"><?= h($result) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    XXE Payload示例：
    <br>&lt;?xml version="1.0"?&gt;
    <br>&lt;!DOCTYPE foo [&lt;!ENTITY xxe SYSTEM "file:///etc/passwd"&gt;]&gt;
    <br>&lt;user&gt;&lt;name&gt;&amp;xxe;&lt;/name&gt;&lt;/user&gt;
</p>
