<?php
// HTTP方法绕过访问控制
$output = null;

// 模拟资源
$resources = ['file1' => '机密文件内容1', 'file2' => '机密文件内容2', 'file3' => '机密文件内容3'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $file = $_GET['file'] ?? '';
    if (isset($resources[$file])) {
        // GET需要认证
        if (!isset($_COOKIE['token'])) {
            $output = "401 Unauthorized - GET请求需要认证";
        } else {
            $output = $resources[$file];
        }
    } else {
        $output = "可用文件: " . implode(', ', array_keys($resources));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST也检查认证
    if (!isset($_COOKIE['token'])) {
        $output = "401 Unauthorized";
    } else {
        $output = "POST操作成功";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // 漏洞：OPTIONS方法不检查认证
    header('Allow: GET, POST, OPTIONS, HEAD, PUT, DELETE');
    $output = "允许的方法: GET, POST, OPTIONS, HEAD, PUT, DELETE";
} elseif ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    // 漏洞：HEAD方法不检查认证
    $file = $_GET['file'] ?? '';
    if (isset($resources[$file])) {
        renderChallengeSuccess($challenge, 'HEAD 请求未认证就读取了受保护资源');
        header('X-Content-Length: ' . strlen($resources[$file]));
        $output = ''; // HEAD不返回body
    }
}
?>

<p>文件服务（HTTP方法绕过）</p>
<p>某些HTTP方法的访问控制不完整，可绕过认证。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>文件名</label>
    <select name="file">
        <option value="file1">file1</option>
        <option value="file2">file2</option>
        <option value="file3">file3</option>
    </select>
    <button type="submit" class="btn btn-primary">GET请求</button>
</form>

<a href="?id=<?= h($_GET['cid'] ?? '') ?>&file=file1" class="btn btn-warning" style="margin-top:0.5rem;">OPTIONS请求（模拟）</a>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    HTTP方法绕过：
    <br>用OPTIONS查看允许的方法
    <br>尝试HEAD、PUT、DELETE等方法
    <br>某些Web服务器对不同方法的处理不一致
</p>
