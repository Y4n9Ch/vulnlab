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

// 判定一次文件读取是否越出了基准目录（供目录遍历类题目判定成功条件）
function escapedBaseDir($path, $baseDir) {
    $base = realpath($baseDir);
    $real = realpath($path);
    if ($base === false || $real === false) return false;
    $base = rtrim(str_replace('\\', '/', $base), '/') . '/';
    $real = str_replace('\\', '/', $real);
    return !str_starts_with($real, $base);
}

// 登录类题目的模拟登录状态条：调用时处理退出（unset 会话标记）并渲染状态。
// $logoutParam 为触发退出的 GET 参数名；退出链接带交互区锚点，无 JS 时也能回到原位置附近。
// POST 登录请求忽略残留的退出参数，避免退出后留在带 logout=1 的 URL 上时登录被立即清除。
function renderLoginStatus($sessionKey, $logoutParam = 'logout') {
    if (isset($_GET[$logoutParam]) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        unset($_SESSION[$sessionKey]);
    }
    $user = $_SESSION[$sessionKey] ?? '';
    if (!is_string($user) || $user === '') return;
    echo '<div class="login-status" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;background:var(--bg-secondary);border:1px solid var(--success);border-radius:var(--radius-sm);padding:0.7rem 1rem;margin-bottom:0.8rem;">';
    echo '<span style="color:var(--success);font-weight:600;">已登录：' . h($user) . '</span>';
    echo '<a class="btn logout-link" data-logout="1" style="background:var(--bg-secondary);color:var(--text-secondary);" href="?cid=' . h($_GET['cid'] ?? '') . '&' . $logoutParam . '=1#challenge-area">退出登录</a>';
    echo '</div>';
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

// 漏洞分类前置学习知识与先修指引
function categoryPrerequisites() {
    return [
        'basics' => [
            'summary' => '掌握 HTTP 协议请求报文、URL/Base64 编码与客户端/服务端信任边界。',
            'topics'  => ['HTTP 报文结构', '请求方法 GET/POST', '常见数据编码', '开发者工具抓包'],
        ],
        'sqli' => [
            'summary' => '掌握 SQL 基础增删改查语法、引号闭合规则与注释符号。',
            'topics'  => ['SQL 基础语法', '引号闭合与注释', 'UNION 联合查询', '字符型与数字型判断'],
        ],
        'xss' => [
            'summary' => '掌握 HTML 标签结构、JavaScript DOM 操作基础与浏览器同源策略。',
            'topics'  => ['HTML/JS 语法基础', 'DOM 与事件属性', '上下文输出与实体编码', '同源策略与 Cookie'],
        ],
        'csrf' => [
            'summary' => '理解 HTTP 请求头 Referer/Origin、Cookie 自动携带机制与接口防重放原理。',
            'topics'  => ['Cookie 携带机制', '同源策略与 CORS', 'CSRF Token 原理', 'SameSite 属性'],
        ],
        'upload' => [
            'summary' => '了解 multipart/form-data 文件上传报文格式、文件后缀与 Web 服务器解析机制。',
            'topics'  => ['文件上传报文', 'MIME 类型识别', '服务器脚本解析', '路径与文件名截断'],
        ],
        'inclusion' => [
            'summary' => '掌握 PHP include/require 包含机制、相对路径与绝对路径、以及 PHP 封装协议。',
            'topics'  => ['PHP 包含函数', '路径穿越语法', 'PHP 伪协议', '日志与临时文件利用'],
        ],
        'cmdi' => [
            'summary' => '掌握 Linux/Windows 基础命令、管道符与多命令连接符拼接语法。',
            'topics'  => ['终端常用命令', '管道符 | 与拼接符 ; &&', '命令替换与引号', '输入过滤与转义'],
        ],
        'traversal' => [
            'summary' => '理解操作系统目录层级结构（../）、URL 路径编码与服务器根目录安全隔离。',
            'topics'  => ['路径层级 ../', 'URL 编码与双重编码', '系统敏感文件路径', '路径规范化函数'],
        ],
        'xxe' => [
            'summary' => '了解 XML 语法规范、外部实体声明（SYSTEM 实体）与 DTD 文档类型定义。',
            'topics'  => ['XML 文档结构', 'DTD 与外部实体', 'SYSTEM 实体声明', '带外数据通道 OOB'],
        ],
        'ssrf' => [
            'summary' => '掌握 URL 协议规范（HTTP/Dict/Gopher 等）、内网私有地址范围与 DNS 解析机制。',
            'topics'  => ['URL 协议方案', '内网私有 IP 范围', 'DNS 解析与重定向', 'cURL 服务端请求'],
        ],
        'unserialize' => [
            'summary' => '掌握面向对象编程基础、PHP 序列化字符串结构与常用魔术方法调用时机。',
            'topics'  => ['类与对象机制', 'serialize 格式', '魔术方法 __wakeup/__destruct', 'POP 链基础'],
        ],
        'auth' => [
            'summary' => '掌握身份认证生命周期、密码加盐哈希存储机制与会话状态管理原理。',
            'topics'  => ['Session 与 Cookie 机制', '密码哈希与加盐', 'Token 令牌鉴权', '暴力破解防御'],
        ],
        'crypto' => [
            'summary' => '掌握单向散列哈希（MD5/SHA）、对称加密基础概念与密码学签名验证原理。',
            'topics'  => ['常见哈希算法', '对称/非对称加密概念', '强伪随机数发生器', '签名与防篡改'],
        ],
        'access' => [
            'summary' => '理解水平越权（IDOR）与垂直越权的区别、RBAC 权限模型及服务端对象归属校验。',
            'topics'  => ['水平与垂直越权概念', 'RBAC 角色权限模型', '参数篡改验证', '对象所有权判断'],
        ],
        'logic' => [
            'summary' => '掌握业务状态机设计流程、前后端数据校验分工与并发竞争条件基础。',
            'topics'  => ['业务状态流转', '并发竞争条件', '前后端数据校验边界', '重放攻击原理'],
        ],
        'ssti' => [
            'summary' => '了解 MVC 架构模式中模板引擎的作用、模板变量表达式语法与沙箱机制。',
            'topics'  => ['模板引擎原理', '模板语法与变量渲染', '沙箱绕过思路', '表达式代码执行'],
        ],
        'php' => [
            'summary' => '掌握 PHP 弱类型松散比较原理（== 与 ===）、变量覆盖机制及常用语言特异性。',
            'topics'  => ['PHP 弱类型比较', '变量覆盖 $$', '常用内置函数特性', '动态函数调用'],
        ],
    ];
}

function categoryPrerequisite($category) {
    $all = categoryPrerequisites();
    return $all[$category] ?? [
        'summary' => '掌握 HTTP 基础请求与常见输入输出边界。',
        'topics'  => ['HTTP 基础', '输入校验', '编码转换'],
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

// 按分类提供新手教学剧本：训练目标、思考路径、通用工具箱与通关掌握要点。
// 内容只描述思路与通用方法，不包含任何具体题目的答案或可用 Payload。
function categoryPlaybook($category) {
    $playbooks = [
        'basics' => [
            'goal' => '认清服务端信任了哪些本不该信的客户端数据。',
            'prompts' => [
                '请求中哪些值由浏览器生成，哪些值可以由测试者重新构造？',
                '服务端把哪个客户端值当成了可信事实？',
            ],
            'toolbox' => [
                '先用抓包工具观察一次正常请求的完整内容，再动手修改。',
                '一次只改一个字段，对比服务端反应，逐步缩小可疑范围。',
            ],
            'takeaway' => '客户端提交的一切都不可信，服务端必须自行校验与计算。',
        ],
        'auth' => [
            'goal' => '理解"你是谁"与"你证明了自己是谁"之间的每个环节。',
            'prompts' => [
                '身份断言存放在哪里（表单、Cookie 还是令牌），由谁验证？',
                '验证逻辑有没有可以跳过、复用或预测的分支？',
            ],
            'toolbox' => [
                '准备两个不同权限的账号，横向对比各自的请求与可见资源。',
                '令牌先尝试解码查看结构（只看不改），确认签名与过期校验的位置。',
            ],
            'takeaway' => '认证强度取决于最弱的一环：凭据、令牌与恢复流程都要防猜测防篡改。',
        ],
        'sqli' => [
            'goal' => '掌握从输入定位 SQL 语法边界到取出数据的完整链路。',
            'prompts' => [
                '输入最终落在 SQL 的哪一种语法上下文？',
                '页面响应的内容、状态或时间能否作为判断信号？',
            ],
            'toolbox' => [
                '先用单引号观察报错反馈，判断引号与括号如何闭合。',
                '用 ORDER BY 逐级递增确认列数，再寻找回显位置。',
            ],
            'takeaway' => '输入的语法上下文决定注入方式；区分回显、报错与盲注三类信号源。',
        ],
        'xss' => [
            'goal' => '学会识别输出上下文，让脚本出现在它能执行的位置。',
            'prompts' => [
                '输入进入了 HTML、属性、URL 还是脚本上下文？',
                '当前过滤是否匹配真正的输出上下文？',
            ],
            'toolbox' => [
                '先注入无副作用的标记组合（如引号、尖括号）观察转义行为。',
                '判断输出点在标签体、属性还是 JS 字符串里，再选择闭合方式。',
            ],
            'takeaway' => '输出上下文决定转义方式；统一的输出编码要按上下文逐处检查。',
        ],
        'access' => [
            'goal' => '区分"验证了身份"与"验证了资源归属"。',
            'prompts' => [
                '接口验证了身份，还是也验证了目标资源归属？',
                '替换对象标识后，服务端的授权结果是否变化？',
            ],
            'toolbox' => [
                '用普通账号与管理员账号分别发同样的请求，对比差异。',
                '把请求中的编号、序号类参数改成相邻值观察响应。',
            ],
            'takeaway' => '授权必须验证"资源属于谁"，不能只看"谁来请求"。',
        ],
        'traversal' => [
            'goal' => '理解路径拼接、规范化与校验顺序的关系。',
            'prompts' => [
                '目录跳转符号被过滤后，还有哪些等价的路径表达？',
                '绝对路径与相对路径在服务端拼接时分别落到哪里？',
            ],
            'toolbox' => [
                '从双写、编码等价形式入手，观察过滤被消耗后的效果。',
                '区分目标返回的是文件内容、报错还是空白，判断校验层级。',
            ],
            'takeaway' => '路径校验要在规范化之后进行，白名单永远优于黑名单替换。',
        ],
        'upload' => [
            'goal' => '理清文件从上传到执行之间每一层校验的位置。',
            'prompts' => [
                '服务端校验的是文件名、类型还是内容？',
                '哪些校验发生在保存之前，哪些可以事后绕过？',
            ],
            'toolbox' => [
                '先上传一张合法图片建立基线，再每次只改变一个属性。',
                '关注保存路径与最终访问路径是否一致，扩展名如何决定执行。',
            ],
            'takeaway' => '文件校验要覆盖扩展名、内容与存储位置，三层缺一不可。',
        ],
        'inclusion' => [
            'goal' => '掌握包含点、伪协议与被包含内容的执行条件。',
            'prompts' => [
                '包含路径参数是否允许协议前缀或目录跳转？',
                '被包含的文件会在什么条件下被当作代码执行？',
            ],
            'toolbox' => [
                '先尝试读取一个系统已知文件，确认包含能力存在。',
                '把"读到内容"与"执行代码"当成两个阶段分别验证。',
            ],
            'takeaway' => '包含点要用白名单固定路径，协议前缀与目录跳转都必须限制。',
        ],
        'cmdi' => [
            'goal' => '识别命令拼接点，并突破分隔符与字符过滤。',
            'prompts' => [
                '输入是否被拼进系统命令？拼接发生在哪一段？',
                '哪些分隔符或编码方式可以拆分、改写原命令？',
            ],
            'toolbox' => [
                '先用无破坏的命令验证拼接点存在，再做后续利用。',
                '对比输出内容与响应时间两类信号，确认命令真的执行。',
            ],
            'takeaway' => '命令拼接要交给参数化 API；必须拼接时严格白名单校验输入。',
        ],
        'csrf' => [
            'goal' => '理解跨站请求的构造条件与服务端防御。',
            'prompts' => [
                '关键请求是否依赖 Cookie 的自动携带？',
                '服务端能否区分"用户主动操作"与"第三方伪造请求"？',
            ],
            'toolbox' => [
                '构造一个最小表单放在本地页面提交，观察服务端反应。',
                '逐个检查请求带了哪些校验：令牌、Referer 还是自定义头。',
            ],
            'takeaway' => '关键操作要校验一次性令牌与来源，不能只依赖 Cookie 自动携带。',
        ],
        'ssrf' => [
            'goal' => '掌握服务端发起请求的能力边界与限制绕过。',
            'prompts' => [
                '哪些参数控制服务端访问的目标地址？',
                '地址的校验与实际解析分别发生在什么阶段？',
            ],
            'toolbox' => [
                '先让服务端访问一个确认可达的地址，证明请求能力存在。',
                '对比不同协议与地址写法的响应差异，推断解析行为。',
            ],
            'takeaway' => '服务端发起的请求同样需要目标白名单，且校验要放在解析之后。',
        ],
        'xxe' => [
            'goal' => '理解 XML 解析器对外部实体的处理行为。',
            'prompts' => [
                '解析器是否处理 DTD 与外部实体定义？',
                '结果是直接回显，还是需要通过其他渠道带出？',
            ],
            'toolbox' => [
                '先用本地实体确认解析器行为，再引入 file 协议目标。',
                '注意请求体的 Content-Type，服务端可能接受多种格式。',
            ],
            'takeaway' => '处理 XML 的第一道防线是禁用外部实体与 DTD 加载。',
        ],
        'unserialize' => [
            'goal' => '理解对象注入的成因与魔术方法利用链。',
            'prompts' => [
                '反序列化的数据是否来自用户可控输入？',
                '哪些魔术方法会在序列化周期内被自动触发？',
            ],
            'toolbox' => [
                '先构造一个最小序列化串，观察对象属性的变化。',
                '从被触发的魔术方法入手，倒推可用的属性与调用链。',
            ],
            'takeaway' => '反序列化数据等同代码输入，用户可控时必须严格限制可用的类。',
        ],
        'ssti' => [
            'goal' => '识别模板注入点，区分不同模板引擎的能力。',
            'prompts' => [
                '输入是直接进入模板语法，还是作为变量值传入？',
                '报错信息暴露了哪个模板引擎与哪些对象？',
            ],
            'toolbox' => [
                '用无副作用的算术表达式确认模板是否求值。',
                '根据报错或特征表达式判断引擎，再查其对象继承链。',
            ],
            'takeaway' => '模板与数据要分离：用户输入只能作为数据，不能拼接进模板。',
        ],
        'php' => [
            'goal' => '熟悉 PHP 弱类型与动态特性引入的安全风险。',
            'prompts' => [
                '哪些 PHP 语法特性改变了变量的作用域或类型？',
                '松散比较能否让两个不同的值判定相等？',
            ],
            'toolbox' => [
                '对比 == 与 === 在不同类型组合下的判定结果。',
                '留意会改写变量表或作用域的函数，梳理变量来源。',
            ],
            'takeaway' => '弱比较与动态特性方便开发也方便攻击，关键逻辑必须严格比较。',
        ],
        'crypto' => [
            'goal' => '识别密码学组件被误用的方式与后果。',
            'prompts' => [
                '加密或哈希在这里被用来保证什么性质？',
                '分组模式、填充与密钥是否可以被预测或复用？',
            ],
            'toolbox' => [
                '对比相同明文在不同请求下的密文差异，判断模式。',
                '从密文长度与结构反推可能的算法与分组方式。',
            ],
            'takeaway' => '用错模式比不用加密更危险，优先使用成熟的认证加密方案。',
        ],
        'logic' => [
            'goal' => '发现业务流程中服务端未复核的隐含假设。',
            'prompts' => [
                '正常业务流程包含哪些不可跳过的状态？',
                '服务端是否重新计算金额并验证前置步骤？',
            ],
            'toolbox' => [
                '画出正常流程的状态图，寻找可以跳过或重放的步骤。',
                '金额、数量、状态类字段逐一尝试篡改并观察最终结果。',
            ],
            'takeaway' => '业务规则要服务端端到端复核，前置校验不等于最终校验。',
        ],
    ];
    $fallback = [
        'goal' => '找到可控输入到危险操作之间缺失的安全校验。',
        'prompts' => [
            '先确定所有可控输入以及它们到达的危险操作。',
            '只改变一个变量，对比响应差异并记录证据。',
        ],
        'toolbox' => [
            '先用正常请求建立基线，再一次只改一个地方。',
            '把每次请求与响应的差异记录下来，作为下一步判断的依据。',
        ],
        'takeaway' => '输入不可信：所有安全决策都必须在服务端完成。',
    ];
    return $playbooks[$category] ?? $fallback;
}

function challengeGuidance($category, $difficulty) {
    $prompts = categoryPlaybook($category)['prompts'];
    if ($difficulty === 'hard') {
        $prompts[] = '单个弱点可能不足以完成目标，哪些信任假设可以串联？';
    } elseif ($difficulty === 'medium') {
        $prompts[] = '现有校验发生在解析之前还是之后，顺序能否造成差异？';
    } else {
        $prompts[] = '先用正常输入建立基线，再测试最小变化。';
    }
    return $prompts;
}

// 找同分类中下一道未完成的题：优先当前题之后更进阶的，回退到跳过的更简单题。
function nextChallengeInCategory($challenge) {
    $userId = $_SESSION['user_id'] ?? 0;
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.* FROM challenges c
        LEFT JOIN solves s ON s.challenge_id = c.id AND s.user_id = ?
        WHERE c.category = ? AND s.id IS NULL AND c.sort_order > ?
        ORDER BY c.sort_order LIMIT 1");
    $stmt->execute([$userId, $challenge['category'], (int) $challenge['sort_order']]);
    $next = $stmt->fetch();
    if ($next) return $next;

    $stmt = $db->prepare("
        SELECT c.* FROM challenges c
        LEFT JOIN solves s ON s.challenge_id = c.id AND s.user_id = ?
        WHERE c.category = ? AND s.id IS NULL AND c.sort_order < ?
        ORDER BY c.sort_order LIMIT 1");
    $stmt->execute([$userId, $challenge['category'], (int) $challenge['sort_order']]);
    $next = $stmt->fetch();
    return $next ?: null;
}

function renderChallengeSuccess($challenge, $message = '目标达成') {
    if (empty($challenge['flag'])) return;
    $playbook = categoryPlaybook($challenge['category']);
    echo '<div class="lab-success">';
    echo '<strong>' . h($message) . '</strong>';
    echo '<button type="button" class="copy-btn" data-copy="' . h($challenge['flag']) . '">复制</button>';
    echo '<span>验证令牌</span>';
    echo '<code>' . h($challenge['flag']) . '</code>';
    if (!empty($playbook['takeaway'])) {
        echo '<div class="lab-takeaway"><span class="lab-takeaway-label">掌握要点</span>' . h($playbook['takeaway']) . '</div>';
    }
    $next = nextChallengeInCategory($challenge);
    if ($next) {
        echo '<a class="lab-next" href="/challenge.php?cid=' . (int) $next['id'] . '">'
            . '<span class="lab-next-label">下一题</span><strong>' . h($next['title']) . '</strong>'
            . '<em>' . h(difficultyLabel($next['difficulty'])) . '</em></a>';
    } else {
        echo '<div class="lab-next lab-next-done">本分类题目已全部完成，可以从首页路线进入下一个分类。</div>';
    }
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
