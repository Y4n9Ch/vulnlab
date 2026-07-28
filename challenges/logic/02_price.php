<?php
// 价格篡改
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product = $_POST['product'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $total = $price * $quantity;

    // 漏洞：价格由前端传递，后端未校验
    $msg = '<span style="color:var(--accent);">订单创建成功！</span>';
    $msg .= '<br>商品：' . h($product);
    $msg .= '<br>单价：¥' . number_format($price, 2);
    $msg .= '<br>数量：' . $quantity;
    $msg .= '<br>总价：¥' . number_format($total, 2);
}
?>

<p>商品下单（价格篡改漏洞）</p>
<p>订单提交时价格由前端传递，后端未重新计算。</p>

<div style="background:var(--bg-secondary); padding:1rem; border-radius:var(--radius-sm); margin-bottom:1rem;">
    <strong>商品：VIP会员</strong><br>
    原价：<span style="color:var(--danger); text-decoration:line-through;">¥99.00</span>
    <span style="color:var(--accent); margin-left:0.5rem;">¥99.00</span>
</div>

<form method="POST">
    <input type="hidden" name="product" value="VIP会员">
    <input type="hidden" name="price" value="99.00" id="priceField">
    <label>数量</label>
    <input type="number" name="quantity" value="1" min="1">
    <button type="submit" class="btn btn-primary">提交订单</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    攻击方法：用Burp Suite拦截请求，修改 price 参数为 0.01 或其他低价。
    <br>后端应该从数据库获取价格，而不是信任前端传递的价格。
</p>
