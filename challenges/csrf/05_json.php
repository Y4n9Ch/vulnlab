<?php
// JSON接口CSRF
session_start();
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        if ($data && isset($data['action'])) {
            renderChallengeSuccess($challenge, '跨站 JSON 请求未校验内容类型即执行');
            $output = '操作已执行: ' . h($data['action']);
        }
    }
}
?>

<p>JSON接口（JSON CSRF）</p>
<p>API接受JSON格式请求，可通过form的enctype构造JSON数据进行CSRF攻击。</p>

<form method="POST" id="apiForm">
    <label>JSON数据</label>
    <textarea name="json_data" placeholder='{"action":"delete_user","id":1}'>{"action":"update_email","email":"new@test.com"}</textarea>
    <button type="submit" class="btn btn-primary">发 送</button>
</form>

<script>
document.getElementById('apiForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const json = this.querySelector('textarea').value;
    fetch(location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: json
    }).then(r => r.text()).then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const box = document.querySelector('.result-box');
        const fresh = doc.querySelector('.result-box');
        if (fresh) {
            box.innerHTML = fresh.innerHTML;
        } else {
            box.innerHTML = '请求处理失败，请检查 JSON 格式后重试';
        }
    }).catch(() => {
        const box = document.querySelector('.result-box');
        if (box) box.innerHTML = '请求失败，请重试';
    });
});
</script>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    JSON CSRF利用：
    <br>&lt;form action="http://target/api" method="POST" enctype="text/plain"&gt;
    <br>&lt;input name='{"action":"delete","id":1,"ignore":"' value='"}'&gt;
    <br>&lt;/form&gt;
</p>
