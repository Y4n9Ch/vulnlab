<?php
// __toString反序列化
class Config {
    public $name = 'default';
    public $value = '';

    public function __toString() {
        return $this->getValue();
    }

    public function getValue() {
        return file_get_contents($this->value);
    }
}

class Display {
    public $data;

    public function show() {
        echo $this->data;
    }
}

$output = null;
if (isset($_POST['data'])) {
    $data = $_POST['data'];
    $obj = @unserialize($data);
    if ($obj) {
        ob_start();
        if ($obj instanceof Display) {
            $obj->show();
        } else {
            if ($obj instanceof Config) {
                renderChallengeSuccess($challenge, '__toString 链被触发，读取路径受对象属性控制');
            }
            echo "对象: " . get_class($obj);
        }
        $output = ob_get_clean();
    } else {
        $output = "反序列化失败";
    }
}
?>

<p>配置显示（__toString反序列化）</p>
<p>当对象被当作字符串使用时，__toString会被调用，可链式利用。</p>

<form method="POST">
    <label>序列化数据</label>
    <textarea name="data" rows="4" placeholder="输入序列化字符串"><?= h($_POST['data'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">反序列化</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">POP链构造：</strong>
    <br>Display->show() 输出 $this->data
    <br>Config的__toString调用getValue()
    <br>getValue() 读取文件
    <br>构造：Display->data = Config->value = "/etc/passwd"
</div>
