<?php
$defaultAction = 'download';
$defaultSignature = hash_hmac('sha256', $defaultAction, 'training-signing-key');
$action = postString('action', $defaultAction);
$resource = postString('resource', 'public-report');
$owner = postString('owner', 'learner');
$signature = postString('signature', $defaultSignature);
$output = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valid = hash_equals(hash_hmac('sha256', $action, 'training-signing-key'), $signature);
    if (!$valid) {
        $output = '签名无效';
    } else {
        $output = "执行 {$action}：{$owner}/{$resource}";
        $success = $owner === 'admin' && $resource === 'audit-export';
    }
}
?>

<p>客户端拿到了一个合法下载签名。服务端验证签名后，会根据其他字段选择资源与所有者。</p>
<form method="POST">
    <label>操作</label><input type="text" name="action" value="<?= h($action) ?>">
    <label>资源</label><input type="text" name="resource" value="<?= h($resource) ?>">
    <label>所有者</label><input type="text" name="owner" value="<?= h($owner) ?>">
    <label>签名</label><input type="text" name="signature" value="<?= h($signature) ?>">
    <button type="submit" class="btn btn-primary">验证并执行</button>
</form>
<?php if ($output): ?><div class="result-box"><?= h($output) ?></div><?php endif; ?>
<?php if ($success) renderChallengeSuccess($challenge, '合法签名被复用于未覆盖的敏感资源'); ?>
