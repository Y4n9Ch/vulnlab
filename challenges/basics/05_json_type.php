<?php
$payloadText = postString('payload', '{"username":"learner","is_admin":false}');
$output = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode($payloadText, true);
    if (!is_array($data)) {
        $output = 'JSON 格式无效';
    } else {
        $success = isset($data['is_admin']) && $data['is_admin'] == true && $data['is_admin'] !== true;
        $output = $data['is_admin'] == true ? '权限检查：通过' : '权限检查：拒绝';
    }
}
?>

<p>接口使用 PHP 松散比较判断 <code>is_admin</code>。请比较 JSON 原生类型进入 PHP 后的行为。</p>
<form method="POST">
    <label>JSON 请求体</label>
    <textarea name="payload"><?= h($payloadText) ?></textarea>
    <button type="submit" class="btn btn-primary">解析请求</button>
</form>
<?php if ($output): ?><div class="result-box"><?= h($output) ?></div><?php endif; ?>
<?php if ($success) renderChallengeSuccess($challenge, '非布尔值通过了布尔权限检查'); ?>
