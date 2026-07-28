<?php
// 对象注入
$output = null;

class FileHandler {
    public $filename = '/tmp/default.txt';
    public $content = 'default';

    public function __toString() {
        return file_get_contents($this->filename);
    }

    public function __destruct() {
        file_put_contents($this->filename, $this->content);
    }
}

class Logger {
    public $logFile = '/tmp/app.log';
    public $message = '';

    public function log() {
        file_put_contents($this->logFile, $this->message . "\n", FILE_APPEND);
    }

    public function __destruct() {
        $this->log();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data'] ?? '';

    if ($data) {
        // 漏洞：反序列化用户输入
        $obj = @unserialize($data);
        if ($obj) {
            $output = "反序列化成功: " . get_class($obj);
        } else {
            $output = "反序列化失败";
        }
    }
}
?>

<p>数据处理（对象注入）</p>
<p>反序列化用户输入可注入恶意对象，利用魔术方法执行操作。</p>

<form method="POST">
    <label>序列化数据</label>
    <textarea name="data" rows="4" placeholder="输入序列化字符串"><?= h($_POST['data'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">反序列化</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">对象注入：</strong>
    <br>FileHandler::__destruct() 写文件
    <br>Logger::__destruct() 写日志
    <br>构造序列化数据利用这些方法
</div>
