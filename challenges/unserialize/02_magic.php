<?php
// 魔术方法利用
class FileHandler {
    public $filename = '';
    public $content = '';

    function __toString() {
        // 漏洞：读取文件内容
        return file_get_contents($this->filename) ?: '无法读取文件';
    }
}

class Logger {
    public $logfile = 'app.log';
    public $message = '';

    function __destruct() {
        // 漏洞：写入日志
        file_put_contents($this->logfile, date('Y-m-d H:i:s') . ' ' . $this->message . PHP_EOL, FILE_APPEND);
    }

    function __wakeup() {
        // 漏洞：自动执行某些操作
        $this->message = "User logged in: " . $this->message;
    }
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
    $data = $_POST['data'];
    $obj = @unserialize($data);
    if ($obj) {
        $result = '反序列化成功，对象类型：' . h(get_class($obj));
    } else {
        $result = '<span style="color:var(--danger);">反序列化失败</span>';
    }
}
?>

<p>对象恢复（魔术方法利用）</p>
<p>存在多个带有魔术方法的类，可利用 __toString、__destruct、__wakeup 等方法。</p>

<form method="POST">
    <label>序列化数据</label>
    <textarea name="data" placeholder="输入序列化字符串"><?= h($_POST['data'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">恢 复</button>
</form>

<?php if ($result): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">可用类：</strong>
    <pre style="color:var(--text-muted);">class FileHandler {
    public $filename;
    function __toString() { return file_get_contents($this->filename); }
}

class Logger {
    public $logfile = 'app.log';
    public $message = '';
    function __destruct() { file_put_contents($this->logfile, $this->message); }
    function __wakeup() { $this->message = "logged: " . $this->message; }
}</pre>
</div>
