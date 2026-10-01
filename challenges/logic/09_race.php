<?php
// 条件竞争 - 提现
$msg = null;
$db = getVulnDB();

// 初始化余额（如果表不存在则创建）
try {
    $db->exec("CREATE TABLE IF NOT EXISTS wallet (id INT PRIMARY KEY, balance DECIMAL(10,2))");
    $db->exec("INSERT IGNORE INTO wallet VALUES (1, 1000.00)");
} catch (Exception $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    $amount = floatval($_POST['amount']);

    // 查询余额
    $stmt = $db->query("SELECT balance FROM wallet WHERE id = 1");
    $balance = $stmt->fetchColumn();

    if ($balance >= $amount) {
        // 漏洞：先检查再扣款，存在竞争条件
        usleep(500000); // 500ms延迟，增大竞争窗口
        $db->exec("UPDATE wallet SET balance = balance - $amount WHERE id = 1");
        renderChallengeSuccess($challenge, '未加锁的余额检查与扣款之间可被并发利用');
        $msg = '<span style="color:var(--accent);">提现成功！金额：¥' . number_format($amount, 2) . '</span>';
    } else {
        $msg = '<span style="color:var(--danger);">余额不足！当前余额：¥' . number_format($balance, 2) . '</span>';
    }
}

// 查询当前余额
$currentBalance = $db->query("SELECT balance FROM wallet WHERE id = 1")->fetchColumn();
?>

<p>提现功能（条件竞争漏洞）</p>
<p>提现时先检查余额再扣款，存在时间窗口可利用并发请求多次提现。</p>

<div style="background:var(--bg-secondary); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1rem;">
    当前余额：<span style="color:var(--accent); font-size:1.2rem; font-weight:700;">¥<?= number_format($currentBalance ?? 0, 2) ?></span>
</div>

<form method="POST">
    <label>提现金额</label>
    <input type="number" name="amount" step="0.01" placeholder="100" value="100">
    <button type="submit" class="btn btn-primary">提 现</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    漏洞逻辑：检查余额(1000) → 延迟500ms → 扣款<br>
    攻击方法：用Burp Intruder并发发送10个提现1000的请求，可能全部成功。
</p>
