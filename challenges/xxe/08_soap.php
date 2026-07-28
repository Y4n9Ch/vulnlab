<?php
// SOAP请求XXE
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $soap = $_POST['soap'] ?? '';

    if ($soap) {
        libxml_disable_entity_loader(false);
        $doc = simplexml_load_string($soap);
        if ($doc) {
            // 模拟SOAP处理
            $output = "SOAP请求已处理:\n" . $doc->asXML();
        } else {
            $output = "SOAP请求解析失败";
        }
    }
}
?>

<p>SOAP接口（SOAP XXE）</p>
<p>SOAP接口接受XML格式请求，可注入外部实体读取服务器文件。</p>

<form method="POST">
    <label>SOAP请求</label>
    <textarea name="soap" rows="10" placeholder="输入SOAP XML">&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"&gt;
  &lt;soap:Body&gt;
    &lt;getUser&gt;
      &lt;id&gt;1&lt;/id&gt;
    &lt;/getUser&gt;
  &lt;/soap:Body&gt;
&lt;/soap:Envelope&gt;</textarea>
    <button type="submit" class="btn btn-primary">发 送</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    SOAP XXE注入：
    <br>在SOAP头或体中注入DTD和外部实体
    <br>很多SOAP框架默认启用了外部实体解析
</p>
