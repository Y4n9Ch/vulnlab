<?php
// 链式CSRF攻击
session_start();
$output = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = $_POST['step'] ?? '';

    switch ($step) {
        case '1':
            $output[] = '步骤1: 修改邮箱为 ' . h($_POST['email'] ?? '');
            $_SESSION['csrf_email'] = $_POST['email'] ?? '';
            break;
        case '2':
            $output[] = '步骤2: 请求密码重置到新邮箱 ' . h($_SESSION['csrf_email'] ?? '');
            break;
        case '3':
            renderChallengeSuccess($challenge, '链式 CSRF 完成了从改邮箱到接管账户的全程');
            $output[] = '步骤3: 验证邮箱 ' . h($_SESSION['csrf_email'] ?? '');
            $output[] = '攻击完成！攻击者现在可以重置密码了。';
            break;
    }
}
?>

<p>账户接管（链式CSRF攻击）</p>
<p>通过多步CSRF攻击，依次修改邮箱、请求密码重置、完成邮箱验证，最终接管账户。</p>

<div style="display:flex;gap:1rem;flex-wrap:wrap;">
    <form method="POST" style="flex:1;min-width:200px;">
        <h4>步骤1: 修改邮箱</h4>
        <input type="hidden" name="step" value="1">
        <input type="email" name="email" placeholder="attacker@evil.com" value="<?= h($_SESSION['csrf_email'] ?? '') ?>">
        <button type="submit" class="btn btn-primary">执行</button>
    </form>

    <form method="POST" style="flex:1;min-width:200px;">
        <h4>步骤2: 请求重置</h4>
        <input type="hidden" name="step" value="2">
        <button type="submit" class="btn btn-warning">执行</button>
    </form>

    <form method="POST" style="flex:1;min-width:200px;">
        <h4>步骤3: 验证邮箱</h4>
        <input type="hidden" name="step" value="3">
        <button type="submit" class="btn btn-danger">执行</button>
    </form>
</div>

<?php if ($output): ?>
    <div class="result-box">
        <?php foreach ($output as $line): ?>
            <div><?= $line ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    链式CSRF攻击：多个无害操作组合成攻击链
    <br>每一步单独看都是正常操作
    <br>但组合起来可以接管整个账户
</p>
