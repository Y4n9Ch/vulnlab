<?php
$target = postString('target', '/dashboard');
$decoded = null;
$success = false;
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (stripos($target, 'http') !== false || strpos($target, '//') !== false) {
        $output = '原始文本检查：拒绝外部地址';
    } else {
        $decoded = urldecode($target);
        $host = parse_url($decoded, PHP_URL_HOST);
        $success = !empty($host) && $host !== 'vulnlab.local';
        $output = '最终跳转目标：' . $decoded;
    }
}
?>

<p>跳转服务先检查原始输入，再进行一次 URL 解码。目标是证明最终解析结果可以离开本站。</p>
<form method="POST">
    <label>跳转目标</label>
    <input type="text" name="target" value="<?= h($target) ?>">
    <button type="submit" class="btn btn-primary">检查并跳转</button>
</form>
<?php if ($output): ?><div class="result-box"><?= h($output) ?></div><?php endif; ?>
<?php if ($success) renderChallengeSuccess($challenge, '解码后的外部地址绕过了原始文本检查'); ?>
