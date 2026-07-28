<?php
// parse_str变量覆盖
$output = null;
$auth = false;
$role = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = $_GET['query'] ?? '';

    if ($query) {
        // 漏洞：parse_str覆盖变量
        parse_str($query);

        if ($auth) {
            $output = "认证成功！角色: {$role}";
        } else {
            $output = "未认证。auth=" . var_export($auth, true) . ", role={$role}";
        }
    }
}
?>

<p>查询解析（parse_str变量覆盖）</p>
<p>parse_str函数可覆盖已有变量，实现认证绕过。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['id'] ?? '') ?>">
    <label>查询字符串</label>
    <input type="text" name="query" placeholder="例如：auth=1&role=admin" value="<?= h($_GET['query'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">解 析</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    parse_str覆盖：
    <br>?query=auth=1&role=admin
    <br>会覆盖 $auth 和 $role 变量
    <br>PHP 7.2+ 已弃用不带第二个参数的parse_str
</p>
