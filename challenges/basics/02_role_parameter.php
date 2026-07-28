<?php
$profile = ['nickname' => 'learner', 'role' => 'user'];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile['nickname'] = trim(postString('nickname', $profile['nickname']));
    $profile['role'] = postString('role', $profile['role']);
    $success = $profile['role'] === 'admin';
}
?>

<p>这是普通用户的昵称更新接口。服务端直接保存请求中的全部资料字段。</p>
<form method="POST">
    <label>昵称</label>
    <input type="text" name="nickname" value="<?= h($profile['nickname']) ?>">
    <input type="hidden" name="role" value="user">
    <button type="submit" class="btn btn-primary">保存资料</button>
</form>
<div class="result-box">当前角色：<?= h($profile['role']) ?></div>
<?php if ($success) renderChallengeSuccess($challenge, '普通资料接口修改了权限角色'); ?>
