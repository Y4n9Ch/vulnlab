<?php
// 优惠券重放
$msg = null;
$discount = 0;

// 初始化订单表
$db = getVulnDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product = $_POST['product'] ?? 'VIP会员';
    $coupon = $_POST['coupon'] ?? '';
    $price = 99.00;

    // 漏洞：优惠券使用后未标记失效
    if ($coupon === 'SAVE50') {
        $discount = 50;
        $finalPrice = $price - $discount;
        // 记录订单（但优惠券未标记已使用）
        $stmt = $db->prepare("INSERT INTO orders (user_id, product, price, coupon, total, status) VALUES (?, ?, ?, ?, ?, 'completed')");
        $stmt->execute([$_SESSION['user_id'] ?? 0, $product, $price, $coupon, $finalPrice]);
        $msg = '<span style="color:var(--accent);">订单成功！</span>';
        $msg .= '<br>商品：' . h($product);
        $msg .= '<br>原价：¥' . number_format($price, 2);
        $msg .= '<br>优惠券：' . h($coupon) . '（-¥' . number_format($discount, 2) . '）';
        $msg .= '<br>实付：<span style="color:var(--accent); font-weight:700;">¥' . number_format($finalPrice, 2) . '</span>';
    } elseif ($coupon) {
        $msg = '<span style="color:var(--danger);">无效的优惠券</span>';
    } else {
        $msg = '<span style="color:var(--warning);">请使用优惠券下单</span>';
    }
}
?>

<p>优惠券下单（优惠券重放漏洞）</p>
<p>优惠券使用后未标记失效，可以重复使用。</p>

<div style="background:var(--bg-secondary); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1rem;">
    <strong>商品：VIP会员</strong><br>
    原价：¥99.00<br>
    <span style="color:var(--accent);">可用优惠券：SAVE50（减50元）</span>
</div>

<form method="POST">
    <input type="hidden" name="product" value="VIP会员">
    <label>优惠券码</label>
    <input type="text" name="coupon" placeholder="输入优惠券码" value="SAVE50">
    <button type="submit" class="btn btn-primary">使用优惠券下单</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    优惠券 SAVE50 可以重复使用！每次下单都会减50元。
    <br>应该在使用后将优惠券标记为已使用。
</p>
