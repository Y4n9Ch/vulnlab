<?php
$payloadText = postString('payload', '{"display_name":"learner","bio":"security student"}');
$user = ['display_name' => 'learner', 'bio' => '', 'role' => 'user', 'permissions' => ['profile']];
$output = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode($payloadText, true);
    if (is_array($input)) {
        $user = array_merge($user, $input);
        $success = ($user['role'] ?? '') === 'admin'
            && in_array('export', (array) ($user['permissions'] ?? []), true);
        $output = json_encode($user, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        $output = 'JSON 格式无效';
    }
}
?>

<p>资料接口把请求对象直接合并到用户模型。后台数据导出要求管理员角色和导出权限同时满足。</p>
<form method="POST">
    <label>资料 JSON</label>
    <textarea name="payload"><?= h($payloadText) ?></textarea>
    <button type="submit" class="btn btn-primary">更新模型</button>
</form>
<?php if ($output): ?><div class="result-box"><?= h($output) ?></div><?php endif; ?>
<?php if ($success) renderChallengeSuccess($challenge, '批量绑定写入了内部授权属性'); ?>
