<?php
// JSON转XML的XXE
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = $_POST['json'] ?? '';

    if ($json) {
        $data = json_decode($json, true);
        if ($data) {
            // 简单的JSON转XML（不安全）
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<root>' . "\n";
            foreach ($data as $key => $value) {
                $xml .= "  <{$key}>{$value}</{$key}>" . "\n";
            }
            $xml .= '</root>';

            libxml_disable_entity_loader(false);
            $doc = simplexml_load_string($xml);
            if ($doc) {
                if (preg_match('/<!ENTITY/i', $xml)) {
                    renderChallengeSuccess($challenge, 'JSON 值中的实体定义进入了 XML 解析器');
                }
                $output = "转换结果:\n" . $doc->asXML();
            } else {
                $output = "XML转换失败";
            }
        } else {
            $output = "JSON解析失败";
        }
    }
}
?>

<p>数据转换（JSON转XML XXE）</p>
<p>JSON数据转换为XML处理，可在JSON值中注入XML实体。</p>

<form method="POST">
    <label>JSON数据</label>
    <textarea name="json" rows="4" placeholder='{"name":"test","data":"value"}'>{"name":"test","data":"value"}</textarea>
    <button type="submit" class="btn btn-primary">转 换</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    注入方法：在JSON值中包含XML标记
    <br>{"name":"&lt;!DOCTYPE foo [&lt;!ENTITY xxe SYSTEM 'file:///etc/passwd'&gt;]&gt;&lt;test/&amp;xxe;"}
</p>
