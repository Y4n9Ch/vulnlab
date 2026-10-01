<?php
// 支付逻辑漏洞
$output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product = $_POST['product'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 1);
    $price = floatval($_POST['price'] ?? 0);
    $coupon = $_POST['coupon'] ?? '';

    $products = [
        'phone' => ['name' => '手机', 'price' => 5999],
        'laptop' => ['name' => '电脑', 'price' => 8999],
        'tablet' => ['name' => '平板', 'price' => 3999],
    ];

    if (isset($products[$product])) {
        $total = $price * $quantity;

        // 漏洞：价格由客户端提交
        if ($coupon === 'DISCOUNT50') {
            $total *= 0.5;
        }
        if ($price > 0 && $price < $products[$product]['price']) {
            renderChallengeSuccess($challenge, '服务端按客户端提交的低价完成订单');
        }

        $output = "订单详情:\n";
        $output .= "商品: {$products[$product]['name']}\n";
        $output .= "单价: {$price} 元\n";
        $output .= "数量: {$quantity}\n";
        $output .= "优惠券: {$coupon}\n";
        $output .= "总计: {$total} 元";
    }
}
?>

<p>商品购买（支付逻辑漏洞）</p>
<p>价格由客户端提交，可篡改价格、数量、使用多次优惠券。</p>

<div style="margin-bottom:1rem;">
    <h4>商品列表</h4>
    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div style="background:var(--bg-secondary);padding:1rem;border-radius:var(--radius-sm);">
            <strong>手机</strong> - 5999元
        </div>
        <div style="background:var(--bg-secondary);padding:1rem;border-radius:var(--radius-sm);">
            <strong>电脑</strong> - 8999元
        </div>
        <div style="background:var(--bg-secondary);padding:1rem;border-radius:var(--radius-sm);">
            <strong>平板</strong> - 3999元
        </div>
    </div>
</div>

<form method="POST">
    <label>商品</label>
    <select name="product">
        <option value="phone">手机</option>
        <option value="laptop">电脑</option>
        <option value="tablet">平板</option>
    </select>
    <label>单价（可修改）</label>
    <input type="number" name="price" step="0.01" value="5999">
    <label>数量</label>
    <input type="number" name="quantity" value="1" min="1">
    <label>优惠券</label>
    <input type="text" name="coupon" placeholder="输入优惠码" value="">
    <button type="submit" class="btn btn-primary">购 买</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">支付漏洞：</strong>
    <br>1. 修改price为1或负数
    <br>2. 修改quantity为负数（退款）
    <br>3. 重复使用优惠券
    <br>4. 并发请求导致重复下单
</div>
