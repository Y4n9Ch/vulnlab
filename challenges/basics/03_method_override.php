<?php
$reportedMethod = $_SERVER['REQUEST_METHOD'];
$effectiveMethod = strtoupper(postString('_method', $reportedMethod));
$success = $reportedMethod === 'POST' && $effectiveMethod === 'DELETE';
?>

<p>网关禁止直接发送 DELETE，但应用框架支持表单方法覆盖。观察业务层最终采用的方法。</p>
<form method="POST">
    <input type="hidden" name="_method" value="POST">
    <button type="submit" class="btn btn-primary">提交普通请求</button>
</form>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="result-box">网关方法：<?= h($reportedMethod) ?>
业务方法：<?= h($effectiveMethod) ?></div>
<?php endif; ?>
<?php if ($success) renderChallengeSuccess($challenge, '方法覆盖绕过了网关限制'); ?>
