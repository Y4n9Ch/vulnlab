<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// 获取当前用户
function currentUser() {
    if (!isset($_SESSION['user_id'])) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// 检查是否登录
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 要求登录
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function csrfToken() {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// 生成 Flag
function makeFlag($challengeKey) {
    return FLAG_PREFIX . md5($challengeKey . 'vulnlab_salt') . FLAG_SUFFIX;
}

// 验证 Flag
function checkFlag($challengeId, $userInput) {
    $db = getDB();
    $stmt = $db->prepare("SELECT flag FROM challenges WHERE id = ?");
    $stmt->execute([$challengeId]);
    $challenge = $stmt->fetch();
    if (!$challenge) return false;

    $userInput = trim($userInput);
    // 支持 flag{xxx} 或直接 xxx 两种格式
    if ($userInput === $challenge['flag']) return true;
    $prefix = FLAG_PREFIX;
    $suffix = FLAG_SUFFIX;
    $inner = substr($challenge['flag'], strlen($prefix), -strlen($suffix));
    if ($userInput === $inner) return true;

    return false;
}

// 记录解题
function recordSolve($userId, $challengeId) {
    $db = getDB();
    $stmt = $db->prepare("INSERT IGNORE INTO solves (user_id, challenge_id) VALUES (?, ?)");
    $stmt->execute([$userId, $challengeId]);

    // 更新分数
    $stmt2 = $db->prepare("SELECT COUNT(*) as cnt FROM solves WHERE user_id = ?");
    $stmt2->execute([$userId]);
    $cnt = $stmt2->fetch()['cnt'];
    $stmt3 = $db->prepare("UPDATE users SET score = ? WHERE id = ?");
    $stmt3->execute([$cnt, $userId]);

    return $stmt->rowCount() > 0;
}

// 检查是否已解题
function isSolved($userId, $challengeId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM solves WHERE user_id = ? AND challenge_id = ?");
    $stmt->execute([$userId, $challengeId]);
    return $stmt->fetch() !== false;
}

// 一次读取某个分类的完成记录，避免题目列表逐题查询。
function getSolvedChallengeIds($userId, $category = null) {
    $db = getDB();
    if ($category !== null) {
        $stmt = $db->prepare("
            SELECT s.challenge_id
            FROM solves s
            INNER JOIN challenges c ON c.id = s.challenge_id
            WHERE s.user_id = ? AND c.category = ?
        ");
        $stmt->execute([$userId, $category]);
    } else {
        $stmt = $db->prepare("SELECT challenge_id FROM solves WHERE user_id = ?");
        $stmt->execute([$userId]);
    }
    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// 获取每个分类的难度分布
function getCategoryDifficulties() {
    $db = getDB();
    $stmt = $db->query("SELECT category, difficulty FROM challenges");
    $result = [];
    while ($row = $stmt->fetch()) {
        $cat = $row['category'];
        if (!isset($result[$cat])) $result[$cat] = [];
        $result[$cat][] = $row['difficulty'];
    }
    return $result;
}

// 获取各分类、各难度的题目数量
function getDifficultyStats() {
    $db = getDB();
    $stmt = $db->query("SELECT category, difficulty, COUNT(*) AS total FROM challenges GROUP BY category, difficulty");
    $stats = [];
    while ($row = $stmt->fetch()) {
        $stats[$row['category']][$row['difficulty']] = (int) $row['total'];
    }
    return $stats;
}

// 获取分类统计
function getCategoryStats() {
    $db = getDB();
    $stmt = $db->query("SELECT category, COUNT(*) as total FROM challenges GROUP BY category");
    $stats = [];
    while ($row = $stmt->fetch()) {
        $stats[$row['category']] = $row['total'];
    }
    return $stats;
}

// 获取用户解题统计
function getUserStats($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.category, COUNT(s.id) as solved
        FROM challenges c
        LEFT JOIN solves s ON c.id = s.challenge_id AND s.user_id = ?
        GROUP BY c.category
    ");
    $stmt->execute([$userId]);
    $stats = [];
    while ($row = $stmt->fetch()) {
        $stats[$row['category']] = $row['solved'];
    }
    return $stats;
}

// 获取题目列表
function getChallenges($category = null) {
    $db = getDB();
    if ($category) {
        $stmt = $db->prepare("SELECT * FROM challenges WHERE category = ? ORDER BY CASE difficulty WHEN 'easy' THEN 1 WHEN 'medium' THEN 2 WHEN 'hard' THEN 3 END, sort_order");
        $stmt->execute([$category]);
    } else {
        $stmt = $db->query("SELECT * FROM challenges ORDER BY category, CASE difficulty WHEN 'easy' THEN 1 WHEN 'medium' THEN 2 WHEN 'hard' THEN 3 END, sort_order");
    }
    return $stmt->fetchAll();
}

// 获取单个题目
function getChallenge($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM challenges WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// 题目元数据来自数据库，但加载文件时仍限制在 challenges 目录内，避免污染数据扩大影响。
function resolveChallengeFile($challenge) {
    if (!is_array($challenge)) return null;

    $category = $challenge['category'] ?? '';
    $file = $challenge['file'] ?? '';
    if (!is_string($category) || !isset(categoryNames()[$category])) return null;
    if (!is_string($file) || !preg_match('/^[A-Za-z0-9_-]+\.php$/', $file)) return null;

    $base = realpath(__DIR__ . '/../challenges');
    $path = realpath(__DIR__ . '/../challenges/' . $category . '/' . $file);
    if ($base === false || $path === false) return null;

    $basePrefix = rtrim(str_replace('\\', '/', $base), '/') . '/';
    $normalizedPath = str_replace('\\', '/', $path);
    return str_starts_with($normalizedPath, $basePrefix) ? $path : null;
}

// 分类中文名映射
function categoryNames() {
    return [
        'basics'      => 'Web安全基础',
        'sqli'        => 'SQL注入',
        'xss'         => 'XSS跨站脚本',
        'csrf'        => 'CSRF跨站请求伪造',
        'upload'      => '文件上传',
        'inclusion'   => '文件包含',
        'cmdi'        => '命令注入',
        'traversal'   => '目录遍历',
        'xxe'         => 'XXE外部实体',
        'ssrf'        => 'SSRF服务端请求伪造',
        'unserialize' => '反序列化',
        'auth'        => '认证漏洞',
        'crypto'      => '密码学漏洞',
        'access'      => '访问控制',
        'logic'       => '逻辑漏洞',
        'ssti'        => 'SSTI模板注入',
        'php'         => 'PHP特性',
    ];
}

// 分类图标
function categoryIcons() {
    return [
        'basics'      => '◎',
        'sqli'        => '🗄️',
        'xss'         => '⚡',
        'csrf'        => '🔗',
        'upload'      => '📤',
        'inclusion'   => '📄',
        'cmdi'        => '💻',
        'traversal'   => '🗂️',
        'xxe'         => '📋',
        'ssrf'        => '🌐',
        'unserialize' => '📦',
        'auth'        => '🔐',
        'crypto'      => '🔑',
        'access'      => '🛡️',
        'logic'       => '🧠',
        'ssti'        => '🏷️',
        'php'         => '🐘',
    ];
}

// 分类简介与核心技能，用于大厅快速浏览
function categoryDescriptions() {
    return [
        'basics' => '从请求方法、参数信任与编码边界开始，建立 Web 安全测试的基本观察方法。',
        'sqli' => '识别输入如何进入 SQL 语句，并理解回显、盲注与过滤绕过。',
        'xss' => '分析不可信数据进入 HTML、属性和脚本上下文时的执行风险。',
        'csrf' => '理解浏览器自动携带身份凭据时，状态变更接口为何需要来源校验。',
        'upload' => '检查扩展名、MIME、文件内容、存储路径与执行权限之间的边界。',
        'inclusion' => '分析动态包含路径如何演变为文件读取或代码执行。',
        'cmdi' => '识别用户输入进入系统命令后的分隔符、编码与无回显问题。',
        'traversal' => '理解路径规范化、编码顺序和目录边界检查。',
        'xxe' => '掌握 XML 外部实体带来的文件读取、请求伪造与资源消耗风险。',
        'ssrf' => '分析服务端代发请求时的协议、地址解析和重定向边界。',
        'unserialize' => '追踪对象反序列化与魔术方法形成的调用链。',
        'auth' => '检查口令、会话、令牌、验证码与账号恢复流程。',
        'crypto' => '识别弱哈希、错误加密模式、随机数与签名设计问题。',
        'access' => '验证服务端是否对每个对象和每项操作执行授权判断。',
        'logic' => '从状态机、金额、并发和重放角度审计业务流程。',
        'ssti' => '区分模板数据与模板代码，理解表达式执行和沙箱边界。',
        'php' => '练习 PHP 松散比较、变量覆盖、动态调用与对象特性。',
    ];
}

function categorySkills() {
    return [
        'basics' => ['HTTP', '信任边界', '编码'],
        'sqli' => ['数据库', '输入拼接', '盲注'],
        'xss' => ['浏览器', '输出编码', 'CSP'],
        'csrf' => ['会话', 'Token', '同源策略'],
        'upload' => ['文件校验', '存储隔离', '解析差异'],
        'inclusion' => ['文件系统', 'PHP包装器', '日志'],
        'cmdi' => ['系统命令', '过滤绕过', '盲注'],
        'traversal' => ['路径', '规范化', '编码'],
        'xxe' => ['XML', '实体解析', 'OOB'],
        'ssrf' => ['URL解析', '内网边界', '协议'],
        'unserialize' => ['对象', '魔术方法', 'POP链'],
        'auth' => ['身份认证', 'Session', 'Token'],
        'crypto' => ['哈希', '加密模式', '签名'],
        'access' => ['IDOR', 'RBAC', '对象授权'],
        'logic' => ['状态机', '并发', '重放'],
        'ssti' => ['模板引擎', '沙箱', 'RCE'],
        'php' => ['类型系统', '变量覆盖', '对象'],
    ];
}

// 推荐路线按能力递进，不要求学员先刷完单一漏洞分类
function learningStages() {
    return [
        [
            'level' => '01',
            'title' => '观察请求',
            'description' => '先建立 HTTP、客户端输入和服务端信任边界的直觉。',
            'categories' => ['basics', 'auth'],
            'difficulty' => 'easy',
        ],
        [
            'level' => '02',
            'title' => '控制输入',
            'description' => '练习输入拼接、输出上下文、路径和对象授权。',
            'categories' => ['sqli', 'xss', 'access', 'traversal'],
            'difficulty' => 'easy',
        ],
        [
            'level' => '03',
            'title' => '突破边界',
            'description' => '进入服务端解析、文件、命令和请求代理场景。',
            'categories' => ['upload', 'inclusion', 'cmdi', 'ssrf', 'xxe'],
            'difficulty' => 'medium',
        ],
        [
            'level' => '04',
            'title' => '组合利用',
            'description' => '审计复杂状态、对象调用链、模板和语言特性。',
            'categories' => ['logic', 'unserialize', 'ssti', 'php', 'crypto'],
            'difficulty' => 'hard',
        ],
    ];
}

function getNextUnsolvedChallenge($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.*
        FROM challenges c
        LEFT JOIN solves s ON s.challenge_id = c.id AND s.user_id = ?
        WHERE s.id IS NULL
        ORDER BY
            CASE c.difficulty WHEN 'easy' THEN 1 WHEN 'medium' THEN 2 WHEN 'hard' THEN 3 END,
            CASE c.category
                WHEN 'basics' THEN 1
                WHEN 'auth' THEN 2
                WHEN 'sqli' THEN 3
                WHEN 'xss' THEN 4
                WHEN 'access' THEN 5
                WHEN 'traversal' THEN 6
                WHEN 'upload' THEN 7
                WHEN 'inclusion' THEN 8
                WHEN 'cmdi' THEN 9
                WHEN 'ssrf' THEN 10
                WHEN 'xxe' THEN 11
                WHEN 'logic' THEN 12
                WHEN 'unserialize' THEN 13
                WHEN 'ssti' THEN 14
                WHEN 'php' THEN 15
                WHEN 'crypto' THEN 16
                ELSE 99
            END,
            c.sort_order,
            c.id
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function challengeGuidance($category, $difficulty) {
    $categoryPrompts = [
        'basics' => ['请求中哪些值由浏览器生成，哪些值可以由测试者重新构造？', '服务端把哪个客户端值当成了可信事实？'],
        'sqli' => ['输入最终落在 SQL 的哪一种语法上下文？', '页面响应的内容、状态或时间能否作为判断信号？'],
        'xss' => ['输入进入了 HTML、属性、URL 还是脚本上下文？', '当前过滤是否匹配真正的输出上下文？'],
        'access' => ['接口验证了身份，还是也验证了目标资源归属？', '替换对象标识后，服务端的授权结果是否变化？'],
        'logic' => ['正常业务流程包含哪些不可跳过的状态？', '服务端是否重新计算金额并验证前置步骤？'],
    ];
    $fallback = ['先确定所有可控输入以及它们到达的危险操作。', '只改变一个变量，对比响应差异并记录证据。'];
    $prompts = $categoryPrompts[$category] ?? $fallback;
    if ($difficulty === 'hard') {
        $prompts[] = '单个弱点可能不足以完成目标，哪些信任假设可以串联？';
    } elseif ($difficulty === 'medium') {
        $prompts[] = '现有校验发生在解析之前还是之后，顺序能否造成差异？';
    } else {
        $prompts[] = '先用正常输入建立基线，再测试最小变化。';
    }
    return $prompts;
}

function renderChallengeSuccess($challenge, $message = '目标达成') {
    if (empty($challenge['flag'])) return;
    echo '<div class="lab-success">';
    echo '<strong>' . h($message) . '</strong>';
    echo '<span>验证令牌</span>';
    echo '<code>' . h($challenge['flag']) . '</code>';
    echo '</div>';
}

// 表单字段被构造成数组时使用默认值，避免 PHP 8.1 字符串函数抛出 TypeError。
function postString($key, $default = '') {
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? $value : $default;
}

function textExcerpt($text, $length) {
    $text = (string) $text;
    $length = max(0, (int) $length);
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $length, 'UTF-8');
    }
    $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    if ($characters === false) return substr($text, 0, $length);
    return implode('', array_slice($characters, 0, $length));
}

function textLength($text) {
    $text = (string) $text;
    if (function_exists('mb_strlen')) {
        return mb_strlen($text, 'UTF-8');
    }
    $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    return $characters === false ? strlen($text) : count($characters);
}

// 难度中文
function difficultyLabel($diff) {
    $map = ['easy' => '入门', 'medium' => '中级', 'hard' => '进阶'];
    return $map[$diff] ?? $diff;
}

// 难度颜色类
function difficultyClass($diff) {
    $map = ['easy' => 'diff-easy', 'medium' => 'diff-medium', 'hard' => 'diff-hard'];
    return $map[$diff] ?? '';
}

// XSS 安全输出
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// 处理 Flag 提交的 AJAX
function handleFlagSubmit($expectedChallengeId = null) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['challenge_id'], $_POST['flag'])) {
        header('Content-Type: application/json; charset=UTF-8');
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => '页面凭证已失效，请刷新后重试']);
            exit;
        }

        $challengeId = intval($_POST['challenge_id']);
        if ($expectedChallengeId !== null && $challengeId !== (int) $expectedChallengeId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '题目标识不匹配']);
            exit;
        }

        $flag = postString('flag');
        if ($flag === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '请输入 Flag']);
            exit;
        }

        if (checkFlag($challengeId, $flag)) {
            $isNew = recordSolve($_SESSION['user_id'], $challengeId);
            echo json_encode(['success' => true, 'message' => $isNew ? '恭喜！Flag正确！' : 'Flag正确（已解过此题）']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Flag错误，再试试！']);
        }
        exit;
    }
}
