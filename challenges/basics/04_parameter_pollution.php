<?php
$rawQuery = postString('query', 'scope=user');
$gatewayScope = null;
$applicationScope = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    preg_match('/(?:^|&)scope=([^&]*)/', $rawQuery, $firstMatch);
    $gatewayScope = urldecode($firstMatch[1] ?? '');
    parse_str($rawQuery, $parsed);
    $applicationScope = $parsed['scope'] ?? '';
    $success = $gatewayScope === 'user' && $applicationScope === 'admin';
}
?>

<p>输入一段查询参数。网关检查第一个 <code>scope</code>，应用按 PHP 规则解析完整参数。</p>
<form method="POST">
    <label>查询参数</label>
    <input type="text" name="query" value="<?= h($rawQuery) ?>">
    <button type="submit" class="btn btn-primary">发送到网关</button>
</form>
<?php if ($gatewayScope !== null): ?><div class="result-box">网关看到：<?= h($gatewayScope) ?>
应用使用：<?= h($applicationScope) ?></div><?php endif; ?>
<?php if ($success) renderChallengeSuccess($challenge, '解析差异使应用获得了更高权限'); ?>
