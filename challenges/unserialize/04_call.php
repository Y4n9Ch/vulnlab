<?php
// __call反序列化
class Database {
    public function query($sql) {
        return "执行SQL: " . $sql;
    }
}

class Proxy {
    protected $obj;
    protected $method;
    protected $args;

    public function __construct($obj, $method, $args) {
        $this->obj = $obj;
        $this->method = $method;
        $this->args = $args;
    }

    public function __call($name, $arguments) {
        return call_user_func_array([$this->obj, $this->method], $this->args);
    }
}

$output = null;
if (isset($_POST['data'])) {
    $data = $_POST['data'];
    $obj = @unserialize($data);
    if ($obj) {
        if ($obj instanceof Proxy) {
            renderChallengeSuccess($challenge, '__call 把不可调用方法转发到了受控回调');
        }
        $output = "反序列化成功: " . serialize($obj);
    } else {
        $output = "反序列化失败";
    }
}
?>

<p>API系统（__call反序列化）</p>
<p>__call魔术方法在调用不存在的方法时触发，可利用执行任意回调。</p>

<form method="POST">
    <label>序列化数据</label>
    <textarea name="data" rows="4" placeholder="输入序列化字符串"><?= h($_POST['data'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">反序列化</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">__call利用：</strong>
    <br>Proxy类的__call会调用预设的方法
    <br>可构造Proxy调用system()等危险函数
    <br>O:5:"Proxy":3:{s:6:"*obj";...}
</div>
