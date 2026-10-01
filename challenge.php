<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

// 路由参数用 cid：题目自身的注入参数可能也叫 id（GET 表单会整串替换查询参数），
// 丢失 cid 时回退到会话中记录的当前题目，保证 GET 表单类题目提交后仍停在原题。
$id = intval($_GET['cid'] ?? $_SESSION['last_challenge_id'] ?? 0);
$challenge = getChallenge($id);
if (!$challenge) {
    header('Location: /index.php');
    exit;
}
$_SESSION['last_challenge_id'] = (int) $challenge['id'];

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
$playbook = categoryPlaybook($challenge['category']);
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
            <div><span>训练目标</span><strong><?= h($playbook['goal']) ?></strong></div>
            <div><span>完成标准</span><strong>触发目标行为并取得本题验证令牌</strong></div>
        </div>
        <div class="thinking-path">
            <span class="thinking-label">思考路径</span>
            <ol><?php foreach ($guidance as $prompt): ?><li><?= h($prompt) ?></li><?php endforeach; ?></ol>
        </div>
        <?php if (!empty($challenge['hint'])): ?>
        <div class="hint-section">
            <button type="button" class="hint-toggle disclosure-toggle" aria-expanded="false" data-alt-label="收起题目提示">
                查看题目提示
            </button>
            <div class="hint-content"><?= nl2br(h($challenge['hint'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($playbook['toolbox'])): ?>
        <div class="hint-section toolbox-section">
            <button type="button" class="toolbox-toggle disclosure-toggle" aria-expanded="false" data-alt-label="收起新手工具箱">
                新手工具箱
            </button>
            <ul class="hint-content toolbox-content">
                <?php foreach ($playbook['toolbox'] as $tip): ?><li><?= h($tip) ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <!-- 交互区 -->
    <div class="ch-main">
        <div class="ch-main-header">
            <span class="ch-main-icon">&#9654;</span>
            <h3>题目交互区</h3>
        </div>
        <div class="challenge-area" id="challenge-area">
            <?php
            $hasChallengeSuccess = false;
            if ($challengeFile) {
                ob_start();
                include $challengeFile;
                $challengeOutput = ob_get_clean();
                $hasChallengeSuccess = str_contains($challengeOutput, 'class="lab-success"');
                echo $challengeOutput;
            } else {
                echo '<p>题目文件加载失败，请检查题目配置。</p>';
            }
            ?>
        </div>
    </div>

    <?php if ($solved && !empty($challenge['flag']) && !$hasChallengeSuccess): ?>
        <?php renderChallengeSuccess($challenge, '本题已完成'); ?>
    <?php endif; ?>

    <!-- Flag 提交 -->
    <div class="ch-flag-bar">
        <form id="flagForm" class="flag-form" onsubmit="submitFlag(event, <?= $challenge['id'] ?>)">
            <span class="ch-flag-icon">&#127937;</span>
            <input type="hidden" id="csrfToken" value="<?= h(csrfToken()) ?>">
            <input type="text" id="flagInput" placeholder="输入 flag{...}" aria-label="Flag" autocomplete="off" spellcheck="false" autocapitalize="off" class="flag-input">
            <button type="submit" class="btn btn-primary">提交 Flag</button>
        </form>
        <div id="flagResult" class="flag-result" aria-live="polite"></div>
    </div>

    <!-- 源码辅助 -->
    <div class="ch-source">
        <button type="button" class="source-toggle disclosure-toggle" aria-expanded="false" data-alt-label="收起源码辅助">
            开启源码辅助
        </button>
        <div class="source-content">
            <pre><code><?= h($sourceCode) ?></code></pre>
            <div class="source-actions">
                <button type="button" class="copy-btn" data-copy-target=".source-content code">复制源码</button>
            </div>
        </div>
    </div>

    <nav class="challenge-navigation" aria-label="相邻题目">
        <?php if ($previousChallenge): ?><a href="/challenge.php?cid=<?= (int) $previousChallenge['id'] ?>"><span>上一题</span><strong><?= h($previousChallenge['title']) ?></strong></a><?php else: ?><span></span><?php endif; ?>
        <?php if ($nextChallenge): ?><a class="next" href="/challenge.php?cid=<?= (int) $nextChallenge['id'] ?>"><span>下一题</span><strong><?= h($nextChallenge['title']) ?></strong></a><?php endif; ?>
    </nav>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
