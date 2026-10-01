<?php
// 工作流绕过
session_start();
$output = null;

// 模拟订单流程：下单 -> 付款 -> 发货 -> 确认
$orders = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $order_id = $_POST['order_id'] ?? '';

    switch ($action) {
        case 'create':
            $id = rand(1000, 9999);
            $orders[$id] = ['id' => $id, 'status' => 'created', 'paid' => false];
            $_SESSION['orders'] = $orders;
            $output = "订单创建成功！ID: {$id}";
            break;

        case 'pay':
            // 正常流程：需要先下单
            $output = "付款成功！订单状态: paid";
            break;

        case 'ship':
            // 漏洞：跳过付款直接发货
            renderChallengeSuccess($challenge, '订单状态机被跳步，未付款即完成发货');
            $output = "发货成功！订单状态: shipped（跳过了付款检查）";
            break;

        case 'confirm':
            // 漏洞：跳过所有步骤直接确认
            $output = "订单确认完成！状态: completed";
            break;
    }
}
?>

<p>订单系统（工作流绕过）</p>
<p>订单流程可被绕过，跳过付款直接完成订单。</p>

<div style="display:flex;gap:0.5rem;margin-bottom:1rem;">
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="create">
        <button type="submit" class="btn btn-primary">1. 创建订单</button>
    </form>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="pay">
        <button type="submit" class="btn btn-primary">2. 付款</button>
    </form>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="ship">
        <button type="submit" class="btn btn-warning">3. 发货</button>
    </form>
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="confirm">
        <button type="submit" class="btn btn-danger">4. 确认</button>
    </form>
</div>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<div style="margin-top:1rem; padding:1rem; background:var(--bg-secondary); border-radius:var(--radius-sm); font-size:0.8rem;">
    <strong style="color:var(--warning);">工作流绕过：</strong>
    <br>正常流程：创建 -> 付款 -> 发货 -> 确认
    <br>攻击：直接跳到第3步或第4步
    <br>后端必须验证前序步骤是否完成
</div>
