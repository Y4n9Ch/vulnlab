<?php
// 基础反序列化
class UserProfile {
    public $username = 'guest';
    public $role = 'user';
    public $log = '';

    function __destruct() {
        // 漏洞：析构时写入日志文件
        if ($this->log) {
            file_put_contents(__DIR__ . '/user.log', $this->username . ': ' . $this->log . PHP_EOL, FILE_APPEND);
        }
    }
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
    $data = $_POST['data'];
    // 漏洞：直接反序列化用户输入
    $obj = @unserialize($data);
    if ($obj) {
        $result = '<span style="color:var(--accent);">反序列化成功！用户名：' . h($obj->username ?? 'N/A') . '，角色：' . h($obj->role ?? 'N/A') . '</span>';
    } else {
        $result = '<span style="color:var(--danger);">反序列化失败</span>';
    }
}
?>

<p>用户配置恢复（存在反序列化漏洞）</p>
<p>后端对用户输入进行 unserialize()，存在 __destruct 方法可利用。</p>

<form method="POST">
    <label>序列化数据</label>
    <textarea name="data" placeholder="输入序列化字符串"><?= h($_POST['data'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">恢 复</button>
</form>

<?php if ($result): ?>
    <div class="result-box"><?= $result ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">类定义：</strong>
    <pre style="color:var(--text-muted);">class UserProfile {
    public $username = 'guest';
    public $role = 'user';
    public $log = '';

    function __destruct() {
        if ($this->log) {
            file_put_contents('user.log', $this->username . ': ' . $this->log);
        }
    }
}</pre>
    <span style="color:var(--text-muted);">利用方法：构造序列化字符串，将 log 设为PHP代码，写入日志文件后包含执行。</span>
</div>
