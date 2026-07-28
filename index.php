<?php
$pageTitle = '靶场大厅';
require_once __DIR__ . '/includes/header.php';

$stats = getCategoryStats();
$difficultyStats = getDifficultyStats();
$names = categoryNames();
$icons = categoryIcons();
$descriptions = categoryDescriptions();
$skills = categorySkills();
$userStats = isLoggedIn() ? getUserStats($_SESSION['user_id']) : [];
$totalChallenges = array_sum($stats);
$totalSolved = array_sum($userStats);
$progress = $totalChallenges > 0 ? round($totalSolved / $totalChallenges * 100) : 0;
$nextChallenge = isLoggedIn() ? getNextUnsolvedChallenge($_SESSION['user_id']) : null;
?>

<div class="dashboard">
    <header class="dashboard-heading">
        <div>
            <span class="section-kicker">VULNLAB / TRAINING CONSOLE</span>
            <h1>Web 安全训练台</h1>
            <p>按能力路径推进，也可以直接进入一个漏洞分类。每次练习先建立正常请求基线，再验证一个清晰假设。</p>
        </div>
        <?php if ($nextChallenge): ?>
            <a class="continue-panel" href="/challenge.php?id=<?= (int) $nextChallenge['id'] ?>">
                <span>继续训练</span>
                <strong><?= h($nextChallenge['title']) ?></strong>
                <small><?= h($names[$nextChallenge['category']] ?? $nextChallenge['category']) ?> · <?= difficultyLabel($nextChallenge['difficulty']) ?></small>
            </a>
        <?php elseif (!isLoggedIn()): ?>
            <a class="continue-panel" href="/register.php">
                <span>建立训练档案</span>
                <strong>注册并记录学习进度</strong>
                <small>所有分类都可以自由练习</small>
            </a>
        <?php endif; ?>
    </header>

    <section class="stats-bar" aria-label="训练统计">
        <div class="stat-card"><div class="stat-number"><?= $totalChallenges ?></div><div class="stat-label">总题数</div></div>
        <div class="stat-card stat-solved"><div class="stat-number"><?= $totalSolved ?></div><div class="stat-label">已完成</div></div>
        <div class="stat-card stat-progress"><div class="stat-number"><?= $progress ?>%</div><div class="stat-label">总进度</div></div>
        <div class="stat-card stat-rank"><div class="stat-number"><?= count($stats) ?></div><div class="stat-label">训练领域</div></div>
    </section>

    <div class="progress-bar-wrap" aria-label="总训练进度">
        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?= $progress ?>%"></div></div>
        <span class="progress-text"><?= $totalSolved ?> / <?= $totalChallenges ?></span>
    </div>

    <section class="learning-section" id="learning-path">
        <div class="section-heading">
            <div><span class="section-kicker">RECOMMENDED PATH</span><h2>从简单到困难</h2></div>
            <p>路线按测试能力递进，不要求先刷完某个单一分类。</p>
        </div>
        <div class="learning-path">
            <?php foreach (learningStages() as $stage): ?>
                <?php
                $stageTotal = 0;
                foreach ($stage['categories'] as $category) {
                    $stageTotal += $difficultyStats[$category][$stage['difficulty']] ?? 0;
                }
                ?>
                <article class="path-step">
                    <span class="path-level"><?= h($stage['level']) ?></span>
                    <div class="path-copy">
                        <div class="path-title-row"><h3><?= h($stage['title']) ?></h3><span class="diff-badge <?= difficultyClass($stage['difficulty']) ?>"><?= difficultyLabel($stage['difficulty']) ?></span></div>
                        <p><?= h($stage['description']) ?></p>
                        <div class="path-categories">
                            <?php foreach ($stage['categories'] as $category): ?>
                                <a href="/category.php?cat=<?= h($category) ?>"><?= h($names[$category] ?? $category) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <span class="path-count"><?= $stageTotal ?> 题</span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="catalog-section">
        <div class="section-heading catalog-heading">
            <div><span class="section-kicker">CHALLENGE CATALOG</span><h2>漏洞分类</h2></div>
            <div class="catalog-tools">
                <label class="search-field"><span aria-hidden="true">⌕</span><input id="categorySearch" type="search" aria-label="搜索分类或技能" placeholder="搜索分类或技能" autocomplete="off"></label>
                <div class="segmented-control" aria-label="难度筛选">
                    <button type="button" class="active" aria-pressed="true" data-category-filter="all">全部</button>
                    <button type="button" aria-pressed="false" data-category-filter="easy">入门</button>
                    <button type="button" aria-pressed="false" data-category-filter="medium">中级</button>
                    <button type="button" aria-pressed="false" data-category-filter="hard">进阶</button>
                </div>
            </div>
        </div>

        <div class="category-grid" id="categoryGrid">
            <?php foreach ($names as $key => $name): ?>
                <?php
                $total = $stats[$key] ?? 0;
                if ($total === 0) continue;
                $solved = $userStats[$key] ?? 0;
                $catProgress = $total > 0 ? round($solved / $total * 100) : 0;
                $diffs = array_keys($difficultyStats[$key] ?? []);
                $searchText = $name . ' ' . implode(' ', $skills[$key] ?? []);
                ?>
                <a href="/category.php?cat=<?= h($key) ?>" class="category-card" data-search="<?= h($searchText) ?>" data-difficulties="<?= h(implode(' ', $diffs)) ?>">
                    <div class="category-card-head"><span class="card-icon"><?= $icons[$key] ?? '◇' ?></span><span class="card-count"><?= $total ?> 题</span></div>
                    <div class="card-title"><?= h($name) ?></div>
                    <p class="card-description"><?= h($descriptions[$key] ?? '') ?></p>
                    <div class="card-skills">
                        <?php foreach (($skills[$key] ?? []) as $skill): ?><span><?= h($skill) ?></span><?php endforeach; ?>
                    </div>
                    <div class="card-progress">
                        <div class="card-progress-bg"><div class="card-progress-fill" style="width: <?= $catProgress ?>%"></div></div>
                        <span class="card-progress-text"><?= $solved ?>/<?= $total ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="empty-state" id="categoryEmpty" hidden>没有匹配的训练分类</div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
