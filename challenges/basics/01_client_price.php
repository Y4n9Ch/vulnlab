<?php
$output = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product = postString('product', 'Web安全入门课');
    $price = (float) postString('price', '199');
    $quantity = max(1, (int) postString('quantity', '1'));
    $total = $price * $quantity;
    $output = "订单已创建：{$product}，应付 ¥" . number_format($total, 2);
    $success = $price > 0 && $price < 10;
}
?>

<p>课程结算台显示标准单价为 ¥199.00。请检查提交订单时，服务端是否重新读取了可信价格。</p>
<form method="POST">
    <input type="hidden" name="product" value="Web安全入门课">
    <input type="hidden" name="price" value="199">
    <label>购买数量</label>
    <input type="number" name="quantity" value="1" min="1">
    <button type="submit" class="btn btn-primary">提交订单</button>
</form>
<?php if ($output): ?><div class="result-box"><?= h($output) ?></div><?php endif; ?>
<?php if ($success) renderChallengeSuccess($challenge, '服务端接受了客户端篡改的价格'); ?>
