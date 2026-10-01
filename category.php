<?php
require_once __DIR__ . '/includes/functions.php';

$cat = $_GET['cat'] ?? '';
$names = categoryNames();
$icons = categoryIcons();

if (!isset($names[$cat])) {
    header('Location: /index.php');
    exit;
}

$pageTitle = $names[$cat];
requireLogin();

$challenges = getChallenges($cat);
$solvedChallengeIds = array_flip(getSolvedChallengeIds($_SESSION['user_id'], $cat));
$solvedCount = count($solvedChallengeIds);
$descriptions = categoryDescriptions();

require_once __DIR__ . '/includes/header.php';
?>

<div class="category-page">
    <div class="category-header">
        <span class="category-icon"><?= $icons[$cat] ?? '❓' ?></span>
        <div>
            <h1><?= h($names[$cat]) ?></h1>
            <p class="category-subtitle"><?= h($descriptions[$cat] ?? '') ?></p>
            <div class="category-progress-line"><span><?= $solvedCount ?> / <?= count($challenges) ?> 已完成</span><div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= count($challenges) ? round($solvedCount / count($challenges) * 100) : 0 ?>%"></div></div></div>
        </div>
    </div>

    <?php $prereq = categoryPrerequisite($cat); ?>
    <div class="category-prereq">
        <div class="prereq-header">
            <span class="prereq-badge">💡 先修学习建议</span>
            <span class="prereq-summary"><?= h($prereq['summary']) ?></span>
        </div>
        <div class="prereq-topics">
            <span class="prereq-label">建议前置掌握：</span>
            <?php foreach ($prereq['topics'] as $topic): ?>
                <span class="prereq-tag"><?= h($topic) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="challenge-toolbar">
        <label class="search-field"><span aria-hidden="true">⌕</span><input id="challengeSearch" type="search" aria-label="搜索题目" placeholder="搜索题目" autocomplete="off"></label>
        <div class="segmented-control" aria-label="题目难度筛选">
            <button type="button" class="active" aria-pressed="true" data-challenge-filter="all">全部</button>
            <button type="button" aria-pressed="false" data-challenge-filter="easy">入门</button>
            <button type="button" aria-pressed="false" data-challenge-filter="medium">中级</button>
            <button type="button" aria-pressed="false" data-challenge-filter="hard">进阶</button>
        </div>
    </div>

    <div class="challenge-list" id="challengeList">
        <?php foreach ($challenges as $i => $ch): ?>
            <?php $solved = isset($solvedChallengeIds[(int) $ch['id']]); ?>
            <a href="/challenge.php?cid=<?= $ch['id'] ?>" class="challenge-item <?= $solved ? 'solved' : '' ?>" data-title="<?= h($ch['title'] . ' ' . $ch['description']) ?>" data-difficulty="<?= h($ch['difficulty']) ?>">
                <div class="challenge-status">
                    <?= $solved ? '<span class="status-icon solved-icon">✓</span>' : '<span class="status-icon unsolved-icon">' . ($i + 1) . '</span>' ?>
                </div>
                <div class="challenge-info">
                    <div class="challenge-title"><?= h($ch['title']) ?></div>
                    <div class="challenge-desc"><?= h(textExcerpt($ch['description'], 80)) ?><?= textLength($ch['description']) > 80 ? '...' : '' ?></div>
                </div>
                <div class="challenge-meta">
                    <span class="diff-badge <?= difficultyClass($ch['difficulty']) ?>"><?= difficultyLabel($ch['difficulty']) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="empty-state" id="challengeEmpty" hidden>当前筛选下没有题目</div>

    <a href="/index.php" class="back-link">← 返回靶场大厅</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
