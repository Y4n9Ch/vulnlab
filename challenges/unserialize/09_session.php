<?php
// Session反序列化
session_start();
$output = null;

$handler = $_GET['handler'] ?? 'php';
$_SESSION['handler'] = $handler;

if (isset($_POST['data'])) {
    // 漏洞：使用不同的处理器反序列化Session
    if ($handler === 'php') {
        session_decode($_POST['data']);
        $output = "Session已更新 (PHP格式): " . serialize($_SESSION);
    } elseif ($handler === 'php_serialize') {
        // 不同的序列化格式
        $data = unserialize($_POST['data']);
        if (is_array($data)) {
            $_SESSION = array_merge($_SESSION, $data);
        }
        $output = "Session已更新 (php_serialize格式): " . serialize($_SESSION);
    }
}
?>

<p>Session管理（Session反序列化）</p>
<p>PHP有多种Session序列化处理器，格式差异可导致反序列化攻击。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>处理器类型</label>
    <select name="handler">
        <option value="php" <?= ($handler ?? '') === 'php' ? 'selected' : '' ?>>php</option>
        <option value="php_serialize" <?= ($handler ?? '') === 'php_serialize' ? 'selected' : '' ?>>php_serialize</option>
    </select>
    <button type="submit" class="btn btn-primary">切 换</button>
</form>

<form method="POST" style="margin-top:0.5rem;">
    <label>Session数据</label>
    <textarea name="data" rows="4" placeholder="输入Session数据"><?= h($_POST['data'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-primary">更 新</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">Session处理器差异：</strong>
    <br>php: 键名|序列化值
    <br>php_serialize: 整个session的序列化
    <br>php_binary: 二进制格式
    <br>切换处理器可导致注入的Session被错误解析
</div>
