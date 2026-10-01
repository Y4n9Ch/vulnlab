<?php
// 布尔盲注 - 用户存在性检测
$db = getVulnDB();
$result = null;

if (isset($_POST['username'])) {
    $user = $_POST['username'];
    // 漏洞：布尔盲注
    $sql = "SELECT * FROM users_info WHERE username = '$user'";
    try {
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount() > 0) {
            $result = true;
        } else {
            $result = false;
        }
    } catch (PDOException $e) {
        $result = false;
    }
    if (is_string($user) && preg_match('/\'\s*(and|or)\b|(substr|ascii|ord|left|right|mid)\s*\(/i', $user)) {
        renderChallengeSuccess($challenge, '页面仅回显存在与否，布尔盲注通过条件真假逐字符提取数据');
    }
}
?>

<p>用户存在性检测（存在布尔盲注漏洞）</p>
<p>页面只返回"存在"或"不存在"，通过布尔判断逐字符提取数据。</p>

<form method="POST">
    <label>用户名</label>
    <input type="text" name="username" placeholder="输入用户名检测" value="<?= h($_POST['username'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">检 测</button>
</form>

<?php if ($result === true): ?>
    <div class="result-box" style="color: var(--accent);">&#10003; 用户存在</div>
<?php elseif ($result === false): ?>
    <div class="result-box" style="color: var(--danger);">&#10007; 用户不存在</div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    提示：数据库名第一个字符是什么？用 SUBSTR(database(),1,1)='v' 来判断
</p>
