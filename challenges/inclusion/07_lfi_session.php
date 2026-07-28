<?php
// Session文件包含
session_start();
$output = null;

if (isset($_GET['name'])) {
    $_SESSION['username'] = $_GET['name'];
}

if (isset($_GET['page'])) {
    $page = $_GET['page'];
    $page = str_replace('../', '', $page);
    $file = "pages/" . $page;
    $content = @file_get_contents($file);
    if ($content !== false) {
        $output = $content;
    } else {
        $output = "无法读取文件";
    }
}

$sessionId = session_id();
?>

<p>用户设置（Session文件包含）</p>
<p>用户名存入Session文件，可通过包含Session文件执行代码。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>用户名</label>
    <input type="text" name="name" placeholder="输入用户名" value="<?= h($_GET['name'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">设 置</button>
</form>

<form method="GET" style="margin-top:0.5rem;">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>包含页面</label>
    <input type="text" name="page" placeholder="页面路径" value="<?= h($_GET['page'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">加 载</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:0.5rem; font-size:0.85rem;">
    当前Session ID: <strong><?= $sessionId ?></strong>
</p>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">Session包含攻击：</strong>
    <br>1. 设置用户名为PHP代码：&lt;?php system('id');?&gt;
    <br>2. Session保存到 /tmp/sess_{SESSION_ID}
    <br>3. 包含Session文件：?page=../../tmp/sess_<?= $sessionId ?>
</div>
