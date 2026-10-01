<?php
// 批量操作越权
session_start();
$output = null;

$orders = [
    ['id' => 1, 'user' => 'user1', 'product' => '手机', 'price' => 5999],
    ['id' => 2, 'user' => 'user2', 'product' => '电脑', 'price' => 8999],
    ['id' => 3, 'user' => 'admin', 'product' => '服务器', 'price' => 50000],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ids = $_POST['ids'] ?? '';
    $exploited = false;

    if ($action === 'delete' && $ids) {
        $idList = explode(',', $ids);
        $deleted = [];
        foreach ($idList as $id) {
            $id = intval($id);
            // 漏洞：未验证订单归属权
            if ($id > 0 && $id !== 1) $exploited = true;
            $deleted[] = $id;
        }
        $output = "已删除订单: " . implode(', ', $deleted);
    } elseif ($action === 'export' && $ids) {
        $idList = explode(',', $ids);
        // 漏洞：可导出任意用户的订单
        $output = "导出订单数据:\n";
        foreach ($orders as $order) {
            if (in_array($order['id'], $idList)) {
                if ($order['user'] !== 'user1') $exploited = true;
                $output .= "ID: {$order['id']}, 用户: {$order['user']}, 产品: {$order['product']}, 价格: {$order['price']}\n";
            }
        }
    }
    if ($exploited) renderChallengeSuccess($challenge, '批量操作未验证归属权，越权处理了其他用户的订单');
}
?>

<p>订单管理（批量操作越权）</p>
<p>批量操作接口未验证对象归属权，可操作其他用户的数据。</p>

<div style="margin-bottom:1rem;">
    <h4>当前用户的订单</h4>
    <table style="width:100%;border-collapse:collapse;">
        <tr><th style="border:1px solid var(--border-color);padding:0.5rem;">ID</th><th style="border:1px solid var(--border-color);padding:0.5rem;">用户</th><th style="border:1px solid var(--border-color);padding:0.5rem;">产品</th></tr>
        <tr><td style="border:1px solid var(--border-color);padding:0.5rem;">1</td><td style="border:1px solid var(--border-color);padding:0.5rem;">user1</td><td style="border:1px solid var(--border-color);padding:0.5rem;">手机</td></tr>
        <tr><td style="border:1px solid var(--border-color);padding:0.5rem;">2</td><td style="border:1px solid var(--border-color);padding:0.5rem;">user2</td><td style="border:1px solid var(--border-color);padding:0.5rem;">电脑</td></tr>
        <tr><td style="border:1px solid var(--border-color);padding:0.5rem;">3</td><td style="border:1px solid var(--border-color);padding:0.5rem;">admin</td><td style="border:1px solid var(--border-color);padding:0.5rem;">服务器</td></tr>
    </table>
</div>

<form method="POST">
    <label>订单ID（逗号分隔）</label>
    <input type="text" name="ids" placeholder="1,2,3" value="<?= h($_POST['ids'] ?? '') ?>">
    <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
        <button type="submit" name="action" value="delete" class="btn btn-danger">批量删除</button>
        <button type="submit" name="action" value="export" class="btn btn-primary">批量导出</button>
    </div>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    批量操作越权：
    <br>修改订单ID为其他用户的订单ID
    <br>可删除或导出任意用户的订单数据
</p>
