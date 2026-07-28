<?php
$defaultState = ['request_id' => 1042, 'step' => 'profile', 'verified' => false, 'plan' => 'free'];
$defaultToken = rtrim(strtr(base64_encode(json_encode($defaultState)), '+/', '-_'), '=');
$token = postString('token', $defaultToken);
$output = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $normalized = strtr($token, '-_', '+/');
    $padding = strlen($normalized) % 4;
    if ($padding) $normalized .= str_repeat('=', 4 - $padding);
    $state = json_decode(base64_decode($normalized, true) ?: '', true);
    if (!is_array($state)) {
        $output = '状态令牌无效';
    } else {
        $output = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $success = ($state['step'] ?? '') === 'approved'
            && ($state['verified'] ?? false) === true
            && ($state['plan'] ?? '') === 'enterprise';
    }
}
?>

<p>审批状态被编码进客户端令牌。服务端解码后直接恢复流程，没有验证令牌完整性。</p>
<form method="POST">
    <label>状态令牌</label>
    <textarea name="token"><?= h($token) ?></textarea>
    <button type="submit" class="btn btn-primary">恢复审批流程</button>
</form>
<?php if ($output): ?><div class="result-box"><?= h($output) ?></div><?php endif; ?>
<?php if ($success) renderChallengeSuccess($challenge, '伪造状态令牌直接进入了企业审批完成态'); ?>
