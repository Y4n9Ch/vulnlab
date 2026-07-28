<?php
// 弱哈希算法
$msg = null;

// 用户密码哈希（MD5）
$hashes = [
    'admin' => 'e10adc3949ba59abbe56e057f20f883e',   // 123456
    'user1' => '482c811da5d5b4bc6d497ffa98491e38',   // 111111
    'user2' => '827ccb0eea8a706c4c34a16891f84e7b',   // 12345
    'test'  => '098f6bcd4621d373cade4e832627b4f6',   // test
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    if (isset($hashes[$user]) && md5($pass) === $hashes[$user]) {
        $msg = '<span style="color:var(--accent);">登录成功！</span>';
    } else {
        $msg = '<span style="color:var(--danger);">用户名或密码错误</span>';
    }
}
?>

<p>用户登录（弱哈希算法）</p>
<p>密码使用MD5存储，且密码足够简单，可通过彩虹表破解。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <label>密码</label>
    <input type="text" name="password" placeholder="密码" value="<?= h($_POST['password'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">登 录</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">密码哈希（MD5）：</strong>
    <br><?php foreach ($hashes as $u => $h): ?>
        <code><?= h($u) ?> : <?= h($h) ?></code><br>
    <?php endforeach; ?>
    <br><span style="color:var(--text-muted);">拿到 cmd5.com 或使用 hashcat 破解MD5哈希。</span>
</div>
