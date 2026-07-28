<?php
// HTTP方法覆盖CSRF
session_start();
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // GET请求显示表单
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST处理删除操作
    if (isset($_POST['id'])) {
        $output = '删除操作执行成功，ID: ' . h($_POST['id']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // DELETE方法也需要处理
    parse_str(file_get_contents('php://input'), $data);
    if (isset($data['id'])) {
        $output = 'DELETE操作执行成功，ID: ' . h($data['id']);
    }
}
?>

<p>资源删除（HTTP方法覆盖CSRF）</p>
<p>API支持DELETE方法删除资源，可通过表单的_method参数覆盖HTTP方法。</p>

<div style="display:flex;gap:1rem;flex-wrap:wrap;">
    <form method="POST" style="flex:1;min-width:200px;">
        <h4>POST删除</h4>
        <input type="hidden" name="id" value="123">
        <button type="submit" class="btn btn-danger">POST删除</button>
    </form>

    <form method="POST" style="flex:1;min-width:200px;">
        <h4>方法覆盖删除</h4>
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="id" value="456">
        <button type="submit" class="btn btn-danger">_method=DELETE</button>
    </form>
</div>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    HTTP方法覆盖：
    <br>很多框架支持 _method 参数覆盖请求方法
    <br>&lt;form method="POST"&gt;&lt;input name="_method" value="DELETE"&gt;
    <br>这样可以用普通表单发送DELETE请求
</p>
