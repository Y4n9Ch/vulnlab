<?php
// 时间盲注 - 用户验证
$db = getVulnDB();
$result = null;
$startTime = microtime(true);

if (isset($_POST['username'])) {
    $user = $_POST['username'];
    // 漏洞：时间盲注
    $sql = "SELECT * FROM users_info WHERE username = '$user'";
    try {
        $stmt = $db->query($sql);
    } catch (PDOException $e) {}
    // 无论结果如何，页面输出相同
    $result = 'done';
}
$endTime = microtime(true);
$elapsed = round($endTime - ($startTime ?? $endTime), 2);
?>

<p>用户验证接口（存在时间盲注漏洞）</p>
<p>页面返回内容完全相同，无法通过布尔判断。需要利用时间延迟来判断。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="输入用户名" value="<?= h($_POST['username'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">验 证</button>
</form>

<?php if ($result): ?>
    <div class="result-box">
        查询完成，耗时 <?= $elapsed ?> 秒
        <br>返回结果：用户验证接口正常
    </div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    提示：观察响应时间。如果条件为真，用 SLEEP(3) 制造延迟。
    <br>IF(SUBSTR(database(),1,1)='v', SLEEP(3), 0)
</p>
