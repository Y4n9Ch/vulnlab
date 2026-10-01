<?php
// XXE DoS - Billion Laughs
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xml'])) {
    $xml = $_POST['xml'];
    libxml_disable_entity_loader(false);
    $doc = new DOMDocument();
    try {
        @$doc->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD);
        $result = $doc->saveXML();
        if ($result !== false && substr_count(strtolower($xml), '<!entity') >= 3) {
            renderChallengeSuccess($challenge, '多层嵌套实体被解析器逐层展开');
        }
    } catch (Exception $e) {
        $result = 'error:' . $e->getMessage();
    }
}
?>

<p>XML解析（XXE DoS攻击）</p>
<p>利用XML实体扩展（Billion Laughs Attack）造成内存耗尽。</p>

<form method="POST">
    <label>XML数据</label>
    <textarea name="xml" placeholder="输入XML数据"><?= h($_POST['xml'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">解 析</button>
</form>

<?php if ($result): ?>
    <div class="result-box"><?= strpos($result, 'error:') === 0 ? '<span style="color:var(--danger);">' . h(substr($result, 6)) . '</span>' : h($result) ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    Billion Laughs Payload：
    <br>&lt;?xml version="1.0"?&gt;
    <br>&lt;!DOCTYPE lolz [
    <br>&lt;!ENTITY lol "lol"&gt;
    <br>&lt;!ENTITY lol2 "&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;&amp;lol;"&gt;
    <br>&lt;!ENTITY lol3 "&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;&amp;lol2;"&gt;
    <br>... 以此类推，每层扩展10倍
    <br>]&gt;
    <br>&lt;lolz&gt;&amp;lol9;&lt;/lolz&gt;
</p>
