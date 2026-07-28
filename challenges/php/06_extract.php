<?php
// extract变量覆盖
$output = null;

// 默认配置
$config = [
    'debug' => false,
    'admin' => false,
    'auth' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 漏洞：extract覆盖变量
    extract($_POST);

    if ($admin) {
        $output = "管理员模式已启用！";
    } elseif ($debug) {
        $output = "调试模式: " . phpinfo();
    } elseif ($auth) {
        $output = "认证绕过成功！";
    } else {
        $output = "普通用户模式。当前admin=" . var_export($admin, true);
    }
}
?>

<p>配置系统（extract变量覆盖）</p>
<p>使用extract导入用户输入，可覆盖内部变量。</p>

<form method="POST">
    <label>配置参数</label>
    <input type="text" name="config" placeholder="输入配置">
    <button type="submit" class="btn btn-primary">提 交</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">变量覆盖：</strong>
    <br>POST admin=1 — 覆盖$admin变量
    <br>POST debug=1 — 覆盖$debug变量
    <br>POST auth=1 — 覆盖$auth变量
    <br>extract($_POST) 会将所有POST变量注册为PHP变量
</div>
