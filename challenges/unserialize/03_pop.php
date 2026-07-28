<?php
// POP链构造
class Database {
    private $sql = '';

    function __construct($sql = '') {
        $this->sql = $sql;
    }

    function __destruct() {
        // 漏洞：执行SQL
        if ($this->sql) {
            try {
                getVulnDB()->exec($this->sql);
            } catch (Exception $e) {}
        }
    }
}

class Cache {
    public $key = '';
    public $value = '';

    function __toString() {
        // 漏洞：命令执行
        return shell_exec($this->value) ?: '';
    }
}

class Router {
    public $controller;
    public $action;

    function __call($name, $args) {
        // 漏洞：调用不存在的方法时触发
        return $this->controller->$name($this->action);
    }
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
    $data = $_POST['data'];
    $obj = @unserialize($data);
    if ($obj) {
        $result = '<span style="color:var(--accent);">反序列化成功</span>';
    } else {
        $result = '<span style="color:var(--danger);">反序列化失败</span>';
    }
}
?>

<p>POP链构造（反序列化高级利用）</p>
<p>需要构造多个类的POP链，从入口点到达危险函数。</p>

<form method="POST">
    <label>序列化数据</label>
    <textarea name="data" rows="6" placeholder="输入构造的POP链序列化字符串"><?= h($_POST['data'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">执 行</button>
</form>

<?php if ($result): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">POP链分析：</strong>
    <pre style="color:var(--text-muted);">入口：Database::__destruct() → exec SQL
        ↓ 或
入口：Cache::__toString() → shell_exec()
        ↓
需要找到触发 __toString 的地方</pre>
    <span style="color:var(--text-muted);">提示：从 __destruct 开始，追踪调用链直到 eval/system/shell_exec。</span>
</div>
