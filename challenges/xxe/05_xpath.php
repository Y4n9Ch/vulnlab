<?php
// XPath注入结合XXE
$output = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xml = $_POST['xml'] ?? '';
    $xpath = $_POST['xpath'] ?? '';

    if ($xml && $xpath) {
        libxml_disable_entity_loader(false);
        $doc = simplexml_load_string($xml);
        if ($doc) {
            $result = @$doc->xpath($xpath);
            if ($result) {
                if (preg_match('/<!ENTITY/i', $xml)) {
                    renderChallengeSuccess($challenge, '实体注入与 XPath 查询组合成功返回了数据');
                }
                $output = "XPath查询结果:\n";
                foreach ($result as $node) {
                    $output .= (string)$node . "\n";
                }
            } else {
                $output = "查询无结果";
            }
        } else {
            $output = "XML解析失败";
        }
    }
}
?>

<p>XML查询（XPath XXE）</p>
<p>支持自定义XML和XPath查询，可利用XXE读取文件。</p>

<form method="POST">
    <label>XML数据</label>
    <textarea name="xml" rows="6" placeholder="输入XML">&lt;users&gt;
  &lt;user&gt;&lt;name&gt;admin&lt;/name&gt;&lt;/user&gt;
  &lt;user&gt;&lt;name&gt;guest&lt;/name&gt;&lt;/user&gt;
&lt;/users&gt;</textarea>
    <label>XPath表达式</label>
    <input type="text" name="xpath" placeholder="例如：//user/name" value="<?= h($_POST['xpath'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">查 询</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    利用XXE修改XML注入外部实体：
    <br>在XML中添加 &lt;!ENTITY xxe SYSTEM "file:///etc/passwd"&gt;
    <br>然后XPath查询中使用 &amp;xxe;
</p>
