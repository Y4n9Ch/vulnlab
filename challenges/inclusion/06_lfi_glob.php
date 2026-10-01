<?php
// 通配符绕过文件包含
$output = null;
if (isset($_GET['page'])) {
    $page = $_GET['page'];
    // 严格过滤路径
    if (strpos($page, '..') !== false || strpos($page, '/') !== false) {
        $output = "路径中不允许包含 .. 或 /";
    } else {
        $file = $page;
        $content = @file_get_contents($file);
        if ($content !== false) {
            if (preg_match('/[*?\[\]]|^data,|^(php|zip|phar|glob|expect|file):/i', $page)) {
                renderChallengeSuccess($challenge, '路径过滤被特殊符号或包装器绕过，读到了受限资源');
            }
            $output = $content;
        } else {
            $output = "文件不存在";
        }
    }
}
?>

<p>页面加载（通配符绕过）</p>
<p>过滤了 ../ 和 /，可利用通配符或特殊符号绕过。</p>

<form method="GET">
    <input type="hidden" name="id" value="<?= h($_GET['cid'] ?? '') ?>">
    <label>页面路径</label>
    <input type="text" name="page" placeholder="页面名" value="<?= h($_GET['page'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">加 载</button>
</form>

<?php if ($output): ?>
    <div class="result-box"><pre><?= h($output) ?></pre></div>
<?php endif; ?>

<p style="margin-top:1rem; font-size:0.8rem; color:var(--text-muted);">
    绕过方法：
    <br>?page=php://filter/convert.base64-encode/resource=../config.php
    <br>?page=data://text/plain;base64,PD9waHAgcGhwaW5mbygpOz8+
    <br>伪协议不需要 ../ 即可读取任意文件
</p>
