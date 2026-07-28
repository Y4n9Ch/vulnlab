<?php
// __destruct文件删除
class TempFile {
    public $filename = '/tmp/temp.txt';
    public $content = '';

    public function __destruct() {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }
}

$output = null;
if (isset($_POST['data'])) {
    $data = $_POST['data'];
    $obj = @unserialize($data);
    if ($obj) {
        $output = "反序列化成功，对象将在脚本结束时销毁";
    } else {
        $output = "反序列化失败";
    }
}
?>

<p>临时文件管理（__destruct文件删除）</p>
<p>反序列化可控制删除的文件路径，造成任意文件删除。</p>

<form method="POST">
    <label>序列化数据</label>
    <textarea name="data" rows="4" placeholder="输入序列化字符串"><?= h($_POST['data'] ?? 'O:8:"TempFile":1:{s:8:"filename";s:16:"/tmp/test.txt";}') ?></textarea>
    <button type="submit" class="btn btn-primary">反序列化</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">任意文件删除：</strong>
    <br>构造TempFile对象，filename设为重要文件路径
    <br>O:8:"TempFile":1:{s:8:"filename";s:23:"/var/www/html/config.php";}
    <br>对象销毁时会删除指定文件
</div>
