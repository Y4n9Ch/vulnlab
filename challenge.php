<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
$challenge = getChallenge($id);
if (!$challenge) {
    header('Location: /index.php');
    exit;
}

// 处理 Flag 提交，并确保提交目标与当前题目一致。
handleFlagSubmit($challenge['id']);

$names = categoryNames();
$icons = categoryIcons();
$pageTitle = $challenge['title'];
$solved = isSolved($_SESSION['user_id'], $challenge['id']);

// 读取源码
$challengeFile = resolveChallengeFile($challenge);
$sourceCode = $challengeFile ? file_get_contents($challengeFile) : '';
$guidance = challengeGuidance($challenge['category'], $challenge['difficulty']);
$categoryChallenges = getChallenges($challenge['category']);
$previousChallenge = null;
$nextChallenge = null;
foreach ($categoryChallenges as $index => $item) {
    if ((int) $item['id'] !== (int) $challenge['id']) continue;
    $previousChallenge = $categoryChallenges[$index - 1] ?? null;
    $nextChallenge = $categoryChallenges[$index + 1] ?? null;
    break;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="challenge-page">
    <!-- 题目概览 -->
    <div class="ch-hero">
        <div class="ch-hero-top">
            <a href="/category.php?cat=<?= $challenge['category'] ?>" class="back-link">
                ← <?= h($names[$challenge['category']] ?? '') ?>
            </a>
            <div class="ch-hero-badges">
                <span class="diff-badge <?= difficultyClass($challenge['difficulty']) ?>"><?= difficultyLabel($challenge['difficulty']) ?></span>
                <?php if ($solved): ?><span class="solved-badge">✓ 已完成</span><?php endif; ?>
            </div>
        </div>
        <h1 class="ch-hero-title"><?= h($challenge['title']) ?></h1>
        <p class="ch-hero-desc"><?= nl2br(h($challenge['description'])) ?></p>
        <div class="challenge-brief">
            <div><span>训练目标</span><strong>找到可控输入到危险操作之间缺失的安全校验</strong></div>
            <div><span>完成标准</span><strong>触发目标行为并取得本题验证令牌</strong></div>
        </div>
        <div class="thinking-path">
            <span class="thinking-label">思考路径</span>
            <ol><?php foreach ($guidance as $prompt): ?><li><?= h($prompt) ?></li><?php endforeach; ?></ol>
        </div>
        <?php if (!empty($challenge['hint'])): ?>
        <div class="hint-section">
            <button type="button" class="hint-toggle disclosure-toggle" aria-expanded="false">
                查看题目提示
            </button>
            <div class="hint-content"><?= nl2br(h($challenge['hint'])) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- 交互区 -->
    <div class="ch-main">
        <div class="ch-main-header">
            <span class="ch-main-icon">&#9654;</span>
            <h3>题目交互区</h3>
        </div>
        <div class="challenge-area">
            <?php
            if ($challengeFile) {
                include $challengeFile;
            } else {
                echo '<p>题目文件加载中...</p>';
            }
            ?>
        </div>
    </div>

    <!-- Flag 提交 -->
    <div class="ch-flag-bar">
        <form id="flagForm" class="flag-form" onsubmit="submitFlag(event, <?= $challenge['id'] ?>)">
            <span class="ch-flag-icon">&#127937;</span>
            <input type="hidden" id="csrfToken" value="<?= h(csrfToken()) ?>">
            <input type="text" id="flagInput" placeholder="输入 flag{...}" aria-label="Flag" autocomplete="off" class="flag-input">
            <button type="submit" class="btn btn-primary">提交 Flag</button>
        </form>
        <div id="flagResult" class="flag-result"></div>
    </div>

    <!-- 源码辅助 -->
    <div class="ch-source">
        <button type="button" class="source-toggle disclosure-toggle" aria-expanded="false">
            开启源码辅助
        </button>
        <div class="source-content">
            <pre><code><?= h($sourceCode) ?></code></pre>
        </div>
    </div>

    <nav class="challenge-navigation" aria-label="相邻题目">
        <?php if ($previousChallenge): ?><a href="/challenge.php?id=<?= (int) $previousChallenge['id'] ?>"><span>上一题</span><strong><?= h($previousChallenge['title']) ?></strong></a><?php else: ?><span></span><?php endif; ?>
        <?php if ($nextChallenge): ?><a class="next" href="/challenge.php?id=<?= (int) $nextChallenge['id'] ?>"><span>下一题</span><strong><?= h($nextChallenge['title']) ?></strong></a><?php endif; ?>
    </nav>
</div>

<script>
    window.__challengeData = {
        id: <?= $challenge['id'] ?>,
        title: <?= json_encode($challenge['title']) ?>,
        category: <?= json_encode($challenge['category']) ?>,
        source: <?= json_encode($sourceCode) ?>
    };
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
