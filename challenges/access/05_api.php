<?php
// API未授权访问
session_start();
$output = null;

// 模拟API
$users = [
    1 => ['id' => 1, 'username' => 'admin', 'email' => 'admin@test.com', 'role' => 'admin', 'salary' => 50000],
    2 => ['id' => 2, 'username' => 'user1', 'email' => 'user1@test.com', 'role' => 'user', 'salary' => 8000],
    3 => ['id' => 3, 'username' => 'user2', 'email' => 'user2@test.com', 'role' => 'user', 'salary' => 7500],
];

// API路由
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $api = $_GET['api'];

    // 漏洞：API无需认证即可访问
    if ($api === 'users') {
        echo json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } elseif ($api === 'user' && isset($_GET['cid'])) {
        $id = intval($_GET['cid']);
        if (isset($users[$id])) {
            echo json_encode($users[$id], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['error' => '用户不存在']);
        }
    } elseif ($api === 'admin') {
        // 敏感API，但仍无认证
        renderChallengeSuccess($challenge, '未认证直接调用了管理 API');
        echo json_encode(['message' => '管理后台数据', 'flag' => 'admin_api_exposed']);
    }
    exit;
}
?>

<p>用户系统（API未授权访问）</p>
<p>API接口未做认证，可直接访问敏感数据。</p>

<div style="margin-bottom:1rem;">
    <h4>API端点</h4>
    <ul>
        <li><a href="?id=<?= h($_GET['cid'] ?? '') ?>&api=users">GET /api/users</a> - 获取所有用户</li>
        <li><a href="?id=<?= h($_GET['cid'] ?? '') ?>&api=user&id=1">GET /api/user/1</a> - 获取单个用户</li>
        <li><a href="?id=<?= h($_GET['cid'] ?? '') ?>&api=admin">GET /api/admin</a> - 管理接口</li>
    </ul>
</div>

<?php if ($output): ?>
    <div class="result-box"><?= $output ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    API安全问题：
    <br>1. 无认证要求
    <br>2. 返回过多字段（包含薪资等敏感信息）
    <br>3. 管理接口对所有用户开放
</p>
