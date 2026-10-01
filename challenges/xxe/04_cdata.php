<?php
// CDATA绕过XXE
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xml = file_get_contents('php://input');
    if (empty($xml)) {
        $xml = $_POST['xml'] ?? '';
    }

    if ($xml) {
        libxml_disable_entity_loader(false);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOENT);
        if ($doc) {
            if (preg_match('/<!ENTITY[^>]*SYSTEM\s+["\']file:/i', $xml)) {
                renderChallengeSuccess($challenge, '实体定义在解析层被展开用于读取本地内容');
            }
            // 输出所有文本内容
            $output = "解析结果:\n" . $doc->asXML();
        } else {
            $output = "XML解析失败";
        }
    }
}
?>

<p>数据解析（CDATA XXE）</p>
<p>使用CDATA包裹特殊字符，绕过XML解析限制读取文件。</p>

<form method="POST">
    <label>XML数据</label>
    <textarea name="xml" rows="8" placeholder="输入XML数据">&lt;?xml version="1.0"?&gt;
&lt;!DOCTYPE data [
  &lt;!ENTITY file SYSTEM "file:///etc/passwd"&gt;
]&gt;
&lt;data&gt;&lt;![CDATA[&amp;file;]]]&gt;&lt;/data&gt;</textarea>
    <button type="submit" class="btn btn-primary">解 析</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    CDATA用于包裹不需要解析的文本
    <br>但在实体引用展开后再放入CDATA，仍可泄露数据
</p>
