<?php
// 子域名信任CSRF
session_start();
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $host = $_SERVER['HTTP_HOST'];

    // 信任所有子域名
    $baseDomain = preg_replace('/^.*?([^.]+\.[^.]+)$/', '$1', $host);

    if (preg_match('/\.' . preg_quote($baseDomain, '/') . '$/', $origin) || $origin === $host) {
        if (isset($_POST['password'])) {
            renderChallengeSuccess($challenge, '宽松的子域信任让跨站请求通过了 Origin 校验');
            $output = '密码已修改为: ' . h($_POST['password']);
        }
    } else {
        $output = 'Origin不被信任: ' . h($origin);
    }
}
?>

<p>修改密码（子域名信任CSRF）</p>
<p>服务器信任所有子域名的请求，如果任一子域名存在XSS漏洞，可发起CSRF攻击。</p>

<form method="POST">
    <label>新密码</label>
    <input type="password" name="password" placeholder="新密码" required>
    <button type="submit" class="btn btn-primary">修改密码</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    子域名信任漏洞：
    <br>如果 *.example.com 都被信任
    <br>攻击者只需在任意子域名（如 test.example.com）找到XSS
    <br>即可对主站发起CSRF攻击
</p>
