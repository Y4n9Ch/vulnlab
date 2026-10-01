<?php
// JWT漏洞
$msg = null;
$token = null;

// 简单的JWT实现（不安全）
function base64url_encode($data) { return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }
function base64url_decode($data) { return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); }

function createJWT($payload, $secret = 'secret') {
    $header = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64url_encode(json_encode($payload));
    $signature = base64url_encode(hash_hmac('sha256', "$header.$payload", $secret, true));
    return "$header.$payload.$signature";
}

function verifyJWT($token, $secret = 'secret') {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    // 漏洞：支持 none 算法
    $header = json_decode(base64url_decode($parts[0]), true);
    if ($header['alg'] === 'none') {
        return json_decode(base64url_decode($parts[1]), true);
    }
    $sig = base64url_encode(hash_hmac('sha256', "$parts[0].$parts[1]", $secret, true));
    if ($sig === $parts[2]) {
        return json_decode(base64url_decode($parts[1]), true);
    }
    return null;
}

// 生成默认token
$defaultPayload = ['username' => 'guest', 'role' => 'user', 'exp' => time() + 3600];
$token = createJWT($defaultPayload);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $submittedToken = $_POST['token'];
    $payload = verifyJWT($submittedToken);
    if ($payload) {
        if (($payload['role'] ?? '') === 'admin') {
            renderChallengeSuccess($challenge, '伪造的 Token 提权为 admin 角色');
            $msg = '<span style="color:var(--accent);">欢迎管理员！Flag已获取。</span>';
        } else {
            $msg = '<span style="color:var(--warning);">登录成功，但你是 ' . h($payload['role'] ?? 'unknown') . ' 角色。需要admin权限。</span>';
        }
        $msg .= '<br>Token内容：<code>' . h(json_encode($payload)) . '</code>';
    } else {
        $msg = '<span style="color:var(--danger);">Token验证失败</span>';
    }
}
?>

<p>JWT认证（存在算法漏洞）</p>
<p>认证使用JWT，但支持 none 算法或密钥过弱。</p>

<form method="POST">
    <label>JWT Token</label>
    <textarea name="token" rows="3"><?= h($_POST['token'] ?? $token) ?></textarea>
    <button type="submit" class="btn btn-primary">验 证</button>
</form>

<?php if ($msg): ?>
    <div class="result-box"><?= $msg ?></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    当前Token（user角色）：<?= h($token) ?><br><br>
    绕过方法：
    <br>1. 将 header 中的 alg 改为 none，去掉签名部分
    <br>2. 爆破弱密钥（如 secret、123456）
</p>
