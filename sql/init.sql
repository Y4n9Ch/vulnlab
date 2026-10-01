-- VulnLab 安全靶场数据库初始化
CREATE DATABASE IF NOT EXISTS vulnlab DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vulnlab;
SET NAMES utf8mb4;

-- 用户表
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 题目表
CREATE TABLE IF NOT EXISTS challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    file VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty ENUM('easy','medium','hard') NOT NULL,
    flag VARCHAR(255) NOT NULL,
    hint TEXT,
    sort_order INT DEFAULT 0,
    UNIQUE KEY unique_challenge_file (category, file)
) ENGINE=InnoDB;

-- 解题记录表
CREATE TABLE IF NOT EXISTS solves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    challenge_id INT NOT NULL,
    solved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_solve (user_id, challenge_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (challenge_id) REFERENCES challenges(id)
) ENGINE=InnoDB;

-- SQL注入用表
CREATE TABLE IF NOT EXISTS users_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255),
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user'
) ENGINE=InnoDB;

INSERT INTO users_info (username, password, email, role) VALUES
('admin', 'e10adc3949ba59abbe56e057f20f883e', 'admin@vulnlab.com', 'admin'),
('user1', '482c811da5d5b4bc6d497ffa98491e38', 'user1@vulnlab.com', 'user'),
('user2', '827ccb0eea8a706c4c34a16891f84e7b', 'user2@vulnlab.com', 'user'),
('test', '098f6bcd4621d373cade4e832627b4f6', 'test@vulnlab.com', 'user'),
('guest', 'e10adc3949ba59abbe56e057f20f883e', 'guest@vulnlab.com', 'guest');

-- XSS留言表
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO messages (username, content) VALUES
('admin', '欢迎来到VulnLab！'),
('user1', '今天天气真不错'),
('user2', '有人一起学习安全吗？');

-- 文件上传记录表
CREATE TABLE IF NOT EXISTS uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    filepath VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 订单表（逻辑漏洞用）
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product VARCHAR(100),
    price DECIMAL(10,2),
    quantity INT,
    coupon VARCHAR(50),
    total DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO orders (user_id, product, price, quantity, coupon, total, status) VALUES
(1, 'VIP会员', 99.00, 1, NULL, 99.00, 'completed'),
(2, '安全课程', 199.00, 1, 'SAVE10', 179.10, 'completed');

-- 日志表
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(50),
    user_agent TEXT,
    url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== 题目数据 =====
-- 访问控制 (9题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('access', 'IDOR越权', '01_idor.php', '个人信息页面通过URL参数中的ID获取数据，未验证权限。\n修改ID查看其他用户信息。', 'easy', 'flag{access_idor_01}', '比较当前对象标识与相邻标识的响应，服务端是否验证了对象归属？', 1),
('access', '未授权访问', '02_func.php', '后台管理接口没有认证检查，可以直接访问。', 'easy', 'flag{access_func_02}', '直接访问 /admin.php 或其他管理路径', 2),
('access', '垂直越权', '03_vertical.php', '普通用户可以访问管理员接口，因为后端未验证用户角色。\n以普通用户身份访问管理功能。', 'medium', 'flag{access_vertical_03}', '直接访问 /admin 接口，看看是否检查了角色权限', 3),
('access', '水平越权', '04_horizontal.php', '修改个人资料时，可以通过修改请求中的用户ID来修改其他用户的资料。', 'medium', 'flag{access_horizontal_04}', '在修改资料的请求中添加或修改 user_id 参数', 4),
('access', 'API未授权', '05_api.php', 'API接口未做认证，可直接访问敏感数据。', 'medium', 'flag{access_api_05}', '直接访问API端点获取数据', 5),
('access', '路径绕过', '06_path.php', '管理页面的访问控制存在路径绕过。', 'medium', 'flag{access_path_06}', '/admin/dashboard/ 或 URL编码绕过', 6),
('access', '请求头绕过', '07_header.php', '信任客户端请求头判断身份。', 'medium', 'flag{access_header_07}', 'X-Forwarded-For: 127.0.0.1', 7),
('access', 'HTTP方法绕过', '08_method.php', '某些HTTP方法的访问控制不完整。', 'medium', 'flag{access_method_08}', '尝试OPTIONS、HEAD等方法', 8),
('access', '批量操作越权', '09_batch.php', '批量操作未验证对象归属权。', 'medium', 'flag{access_batch_09}', '修改订单ID为其他用户的订单', 9);

-- 认证漏洞 (9题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('auth', '弱口令', '01_weak.php', '后台登录使用了常见的弱口令。\n尝试常见的用户名密码组合。', 'easy', 'flag{auth_weak_01}', '试试 admin/admin123、admin/password、root/toor 等组合', 1),
('auth', '暴力破解', '02_brute.php', '登录接口没有验证码和频率限制。\n使用字典暴力破解登录密码。', 'easy', 'flag{auth_brute_02}', '使用Burp Intruder或hydra进行密码爆破', 2),
('auth', 'Cookie伪造', '03_cookie.php', 'Cookie使用Base64编码存储密码。', 'easy', 'flag{auth_cookie_03}', '解码Base64得到username:password', 3),
('auth', 'JWT漏洞', '04_jwt.php', '认证使用JWT，但算法为none或密钥过弱。\n伪造JWT获取管理员权限。', 'medium', 'flag{auth_jwt_04}', '将JWT header中的alg改为none，或尝试爆破弱密钥', 4),
('auth', 'Session固定', '05_session.php', '登录后Session ID不变，存在Session固定攻击。\n诱导用户使用已知的Session ID登录。', 'medium', 'flag{auth_session_05}', '先获取一个Session ID，诱导目标使用该ID登录后劫持', 5),
('auth', '密码重置漏洞', '06_reset.php', '密码重置token可预测且未绑定用户。', 'medium', 'flag{auth_reset_06}', 'Token基于用户名+时间的MD5', 6),
('auth', '验证码爆破', '07_otp.php', '4位验证码无速率限制可暴力枚举。', 'medium', 'flag{auth_otp_07}', '0000-9999只有10000种组合', 7),
('auth', 'OAuth劫持', '08_oauth.php', 'redirect_uri未严格校验可劫持授权码。', 'medium', 'flag{auth_oauth_08}', '修改redirect_uri指向攻击者服务器', 8),
('auth', '时序攻击', '09_timing.php', '逐字符比较导致时间差异泄露信息。', 'hard', 'flag{auth_timing_09}', '每猜对一个字符响应时间增加', 9);

-- Web 安全基础 (9题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('basics', '客户端价格可信', '01_client_price.php', '结算页面把商品价格放在客户端字段中，并直接使用提交值计算订单。观察正常请求，再判断哪些字段不应由客户端决定。', 'easy', 'flag{basics_client_price_01}', '比较页面展示的价格与请求中的价格字段，思考服务端应从哪里获取单价。', 1),
('basics', '角色参数越权', '02_role_parameter.php', '个人资料更新接口允许客户端提交角色字段。找出普通资料与权限字段共用更新逻辑产生的风险。', 'easy', 'flag{basics_role_parameter_02}', '查看请求中所有字段，哪些字段不应该出现在普通用户可控范围内？', 2),
('basics', '方法覆盖绕过', '03_method_override.php', '接口只阻止了表面的请求方法，却支持由参数覆盖真实操作。理解路由层与业务层对请求方法认知不一致的风险。', 'easy', 'flag{basics_method_override_03}', '服务端判断的操作方法，是否一定等于浏览器发出的 HTTP 方法？', 3),
('basics', '重复参数污染', '04_parameter_pollution.php', '网关检查参数列表中的第一个值，应用却使用最后一个值。利用不同解析层对重复参数的理解差异完成权限检查。', 'medium', 'flag{basics_parameter_pollution_04}', '同名参数出现多次时，网关与 PHP 分别会取哪一个？', 4),
('basics', 'JSON 类型混淆', '05_json_type.php', 'JSON 接口使用松散比较判断管理权限。分析布尔、数字和字符串在 PHP 比较规则中的差异。', 'medium', 'flag{basics_json_type_05}', '不要只测试字符串。JSON 能表达哪些原生类型？', 5),
('basics', '编码顺序错误', '06_decode_order.php', '跳转接口先检查原始文本，再进行 URL 解码和目标解析。检查校验与规范化顺序错误如何产生绕过。', 'medium', 'flag{basics_decode_order_06}', '安全校验应该发生在解码前还是得到最终规范形式后？', 6),
('basics', '批量属性绑定', '07_mass_assignment.php', '资料接口把 JSON 对象批量合并到用户模型。识别哪些内部属性可被越权写入，并满足后台导出条件。', 'hard', 'flag{basics_mass_assignment_07}', '对比默认用户模型与提交后的模型，寻找不属于个人资料的属性。', 7),
('basics', '签名字段缺失', '08_signature_scope.php', '操作请求带有合法签名，但签名只覆盖了部分业务字段。判断哪些未签名字段仍会影响最终授权目标。', 'hard', 'flag{basics_signature_scope_08}', '列出服务端执行操作时读取的字段，再与签名计算覆盖的字段逐一对照。', 8),
('basics', '未签名状态令牌', '09_state_token.php', '多步骤审批流程把状态保存在可解码但未签名的客户端令牌中。恢复令牌结构并审计服务端信任的关键状态。', 'hard', 'flag{basics_state_token_09}', '编码不等于完整性保护。令牌解码后包含哪些决定流程状态的字段？', 9);

-- 命令注入 (10题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('cmdi', '基础命令注入', '01_basic.php', '一个Ping工具，直接将用户输入拼接到系统命令中。\n利用命令分隔符执行任意命令。', 'easy', 'flag{cmdi_basic_01}', '试试 127.0.0.1; whoami 或 127.0.0.1 | whoami', 1),
('cmdi', '过滤绕过', '02_filter.php', '后端过滤了 ;、|、& 等命令分隔符。\n找到未被过滤的分隔符或绕过方式。', 'medium', 'flag{cmdi_filter_02}', '试试 %0a（换行符）或反引号 ` ` 执行命令', 2),
('cmdi', '空格绕过', '03_space.php', '后端过滤了空格字符。\n使用替代方式代替空格执行命令。', 'medium', 'flag{cmdi_space_03}', '试试 $IFS、${IFS}、%09（Tab）、< 等方式代替空格', 3),
('cmdi', '分割符注入', '04_split.php', '过滤了|和&，使用其他分割符绕过。', 'medium', 'flag{cmdi_split_04}', '使用分号;或换行符%0a', 4),
('cmdi', '十六进制注入', '05_hex.php', '只允许数字和点，利用IP的其他表示法。', 'medium', 'flag{cmdi_hex_05}', '使用十六进制IP: 0x7f000001', 5),
('cmdi', '环境变量注入', '06_env.php', '过滤了常见命令，使用绝对路径或变量绕过。', 'medium', 'flag{cmdi_env_06}', '/bin/cat 或使用通配符 ca? /etc/passwd', 6),
('cmdi', 'Base64编码注入', '07_base64.php', '只接受Base64输入，解码后直接执行。', 'medium', 'flag{cmdi_base64_07}', 'whoami的Base64: d2hvYW1p', 7),
('cmdi', '反引号注入', '08_backtick.php', '过滤了;|&但未过滤反引号。', 'medium', 'flag{cmdi_backtick_08}', '使用反引号 `whoami` 命令替换', 8),
('cmdi', '无字母数字RCE', '09_char.php', '后端过滤了所有字母和数字字符。\n利用特殊字符和变量构造命令。', 'hard', 'flag{cmdi_char_09}', '利用 ${_}、$()、通配符等方式构造命令', 9),
('cmdi', '时间盲注注入', '10_time.php', '命令结果不回显，通过时间延迟判断。', 'hard', 'flag{cmdi_time_10}', '使用sleep命令或条件判断延迟', 10);

-- 密码学 (8题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('crypto', '弱哈希算法', '01_hash.php', '系统使用MD5存储密码，且密码足够简单。\n通过彩虹表或在线工具破解MD5哈希。', 'medium', 'flag{crypto_hash_01}', '将MD5值拿到 cmd5.com 或 hashcat 破解', 1),
('crypto', 'XOR加密', '02_xor.php', '密钥和明文进行XOR加密，密钥长度较短。\n通过已知明文攻击恢复密钥。', 'medium', 'flag{crypto_xor_02}', '如果知道明文的一部分，可以用 C XOR P = K 恢复密钥', 2),
('crypto', 'RC4弱加密', '03_rc4.php', 'RC4流密码已不再安全。', 'medium', 'flag{crypto_rc4_03}', '相同密钥加密多条消息可被破解', 3),
('crypto', '弱签名算法', '04_sign.php', '使用MD5做签名且可获取任意签名。', 'medium', 'flag{crypto_sign_04}', '请求 ?sign=admin 的签名', 4),
('crypto', 'RSA小指数攻击', '05_rsa.php', '公钥指数e=3，明文小时可直接开立方根。', 'medium', 'flag{crypto_rsa_05}', '当 m^3 < n 时直接开立方根', 5),
('crypto', 'ECB模式攻击', '06_ecb.php', '使用AES-ECB模式加密，相同明文块产生相同密文。\n利用ECB的特性构造恶意密文。', 'hard', 'flag{crypto_ecb_06}', 'ECB模式下相同明文块加密结果相同，可以重排密文块', 6),
('crypto', 'Padding Oracle', '07_padding.php', '解密失败返回不同错误信息，可逐字节解密。', 'hard', 'flag{crypto_padding_07}', '修改密文观察错误信息差异', 7),
('crypto', 'CBC字节翻转', '08_cbc.php', '修改前一个密文块影响当前明文块。', 'hard', 'flag{crypto_cbc_08}', 'C[i] = C[i] XOR P[i] XOR desired_byte', 8);

-- CSRF (10题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('csrf', '基础CSRF', '01_basic.php', '修改密码功能没有任何CSRF防护。\n构造一个恶意页面，当受害者访问时自动修改其密码。', 'easy', 'flag{csrf_basic_01}', '创建一个包含自动提交表单的HTML页面，action指向修改密码接口', 1),
('csrf', 'Token绕过', '02_token.php', '修改密码功能使用了CSRF Token，但Token验证存在缺陷。\n分析Token生成规则，构造有效Token。', 'medium', 'flag{csrf_token_02}', '观察Token是否可预测，比如是否基于时间戳或用户名生成', 2),
('csrf', '点击劫持', '03_clickjack.php', '页面没有设置 X-Frame-Options 头，可以被嵌入iframe。\n利用点击劫持诱导用户点击隐藏的按钮。', 'medium', 'flag{csrf_clickjack_03}', '创建一个透明iframe覆盖在诱导按钮上方', 3),
('csrf', 'Referer校验绕过', '04_referer.php', 'Referer检查不严格，可构造包含域名的Referer绕过。', 'medium', 'flag{csrf_referer_04}', '在攻击者页面域名中包含目标域名', 4),
('csrf', 'JSON CSRF', '05_json.php', 'JSON接口可被表单方式利用进行CSRF。', 'medium', 'flag{csrf_json_05}', '使用enctype=text/plain构造JSON请求', 5),
('csrf', '登录CSRF', '06_login.php', '登录表单无CSRF保护，可让用户以攻击者账号登录。', 'medium', 'flag{csrf_login_06}', '构造自动提交的登录表单', 6),
('csrf', '验证码绕过CSRF', '07_captcha.php', '验证码在页面可见，可自动化获取。', 'medium', 'flag{csrf_captcha_07}', '先请求页面获取验证码再提交', 7),
('csrf', '子域名信任CSRF', '08_subdomain.php', '信任所有子域名的请求，子域名XSS可触发CSRF。', 'medium', 'flag{csrf_subdomain_08}', '在子域名找到XSS后发起CSRF', 8),
('csrf', 'HTTP方法覆盖', '09_method.php', '通过_method参数覆盖HTTP方法发起CSRF。', 'medium', 'flag{csrf_method_09}', '使用 <input name="_method" value="DELETE">', 9),
('csrf', '链式CSRF攻击', '10_chain.php', '多步CSRF攻击组合实现账户接管。', 'hard', 'flag{csrf_chain_10}', '依次修改邮箱、请求重置、验证邮箱', 10);

-- 文件包含 (10题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('inclusion', '本地文件包含', '01_lfi.php', '页面通过GET参数加载本地文件，未对路径做限制。\n利用路径穿越读取系统敏感文件。', 'easy', 'flag{inclusion_lfi_01}', '尝试 ?page=../../../../etc/passwd 或 ?page=../../../../windows/win.ini', 1),
('inclusion', 'LFI过滤绕过', '02_lfi_filter.php', '后端过滤了 ../ 关键字，但过滤不严格。\n绕过过滤实现路径穿越。', 'medium', 'flag{inclusion_lfi_filter_02}', '试试双写 ....// 或 URL编码 %2e%2e%2f', 2),
('inclusion', '远程文件包含', '03_rfi.php', 'php.ini 中 allow_url_include=On。\n页面可以从远程URL加载文件。', 'medium', 'flag{inclusion_rfi_03}', '在你的服务器上放一个PHP文件，然后通过URL包含它', 3),
('inclusion', 'PHP伪协议包含', '04_lfi_wrapper.php', '使用PHP伪协议读取文件或执行代码。', 'medium', 'flag{inclusion_lfi_wrapper_04}', 'php://filter/convert.base64-encode/resource=config.php', 4),
('inclusion', '双重编码包含', '05_lfi_double.php', '过滤后又URL解码，双重编码绕过。', 'medium', 'flag{inclusion_lfi_double_05}', '%252e%252e%252f 解码后变成 ../', 5),
('inclusion', '通配符包含', '06_lfi_glob.php', '过滤路径分隔符，使用伪协议绕过。', 'medium', 'flag{inclusion_lfi_glob_06}', 'data://text/plain;base64,PD9waHAgcGhwaW5mbygpOz8+', 6),
('inclusion', '日志包含', '07_lfi_log.php', '无法直接包含敏感文件，但可以包含日志文件。\n通过User-Agent注入代码到日志，再包含日志文件。', 'hard', 'flag{inclusion_lfi_log_07}', '先在User-Agent中注入PHP代码，再包含access.log', 7),
('inclusion', '路径截断包含', '08_lfi_trunc.php', '自动添加.php后缀，利用路径截断绕过。', 'hard', 'flag{inclusion_lfi_trunc_08}', '使用 %00 截断或大量 ./ 绕过', 8),
('inclusion', 'Session文件包含', '09_lfi_session.php', '用户名存入Session文件，可包含执行代码。', 'hard', 'flag{inclusion_lfi_session_09}', '设置用户名为PHP代码后包含Session文件', 9),
('inclusion', 'Nginx日志包含', '10_lfi_log.php', 'User-Agent注入代码到日志文件。', 'hard', 'flag{inclusion_lfi_log_10}', '确认哪一个请求头被写入日志、日志保存在哪里，以及包含时是否进入解释器。', 10);

-- 逻辑漏洞 (9题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('logic', '价格篡改', '01_price.php', '订单提交时价格由前端传递，后端未重新计算。\n修改请求中的价格参数。', 'medium', 'flag{logic_price_01}', '在提交订单时修改 price 或 total 参数为0.01', 1),
('logic', '优惠券重放', '02_coupon.php', '优惠券使用后未标记失效，可以重复使用。', 'medium', 'flag{logic_coupon_02}', '抓取使用优惠券的请求，多次重放', 2),
('logic', '验证码绕过', '03_verify.php', '验证码在前端生成且可预测，或验证后未失效。\n绕过验证码机制。', 'medium', 'flag{logic_verify_03}', '观察验证码是前端生成还是后端，验证后是否清除session中的验证码', 3),
('logic', '密码修改漏洞', '04_password.php', '修改密码时未验证旧密码。', 'medium', 'flag{logic_password_04}', '旧密码字段为空也能修改成功', 4),
('logic', '注册逻辑漏洞', '05_register.php', '用户名大小写不敏感检查，邮箱未验证。', 'medium', 'flag{logic_register_05}', '使用 Admin 或 ADMIN 绕过检查', 5),
('logic', '支付逻辑漏洞', '06_payment.php', '价格由客户端提交，可篡改。', 'medium', 'flag{logic_payment_06}', '修改price为1或负数', 6),
('logic', '短信验证码绕过', '07_sms.php', '验证码可重复使用，无速率限制。', 'medium', 'flag{logic_sms_07}', '验证码使用后未清除', 7),
('logic', '工作流绕过', '08_workflow.php', '跳过付款等步骤直接完成订单。', 'medium', 'flag{logic_workflow_08}', '直接调用发货或确认接口', 8),
('logic', '条件竞争', '09_race.php', '提现功能在并发请求下可能多次成功。\n利用多线程同时发送提现请求。', 'hard', 'flag{logic_race_09}', '使用Burp Intruder的并发功能或Python多线程发送请求', 9);

-- PHP 特性 (9题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('php', 'eval代码执行', '01_eval.php', '后端将用户输入直接传入eval()函数执行。\n构造Payload执行任意PHP代码。', 'easy', 'flag{php_eval_01}', '先确认输入是否进入代码执行上下文，再用无副作用表达式观察返回值。', 1),
('php', '动态函数调用', '02_dynamic.php', '后端使用变量函数调用，如 $func($param)，两个参数均可控。', 'easy', 'flag{php_dynamic_02}', '将函数名设为 system，参数设为要执行的命令', 2),
('php', 'preg_replace /e', '03_preg.php', '后端使用 preg_replace 的 /e 修饰符，替换内容会被当作PHP代码执行。', 'medium', 'flag{php_preg_03}', '构造正则使匹配成功，替换部分为恶意代码，如 /(.*)/e', 3),
('php', '回调函数', '04_callback.php', '后端使用 call_user_func 或 array_map 等回调函数，参数可控。', 'medium', 'flag{php_callback_04}', '将回调函数设为 system，参数设为要执行的命令', 4),
('php', '动态函数执行', '05_include.php', '白名单可枚举，错误信息泄露。', 'medium', 'flag{php_include_05}', '利用白名单函数的副作用', 5),
('php', 'extract变量覆盖', '06_extract.php', 'extract导入用户输入覆盖内部变量。', 'medium', 'flag{php_extract_06}', '列出 extract 前后的变量名，哪些用户字段可能覆盖内部授权状态？', 6),
('php', 'parse_str覆盖', '07_parse.php', 'parse_str覆盖已有变量。', 'medium', 'flag{php_parse_07}', '追踪 parse_str 生成的变量，哪些名称与已有授权变量发生冲突？', 7),
('php', '类型混淆', '08_type.php', 'PHP松散比较导致类型混淆。', 'medium', 'flag{php_type_08}', '"0e123" == "0e456" 结果为true', 8),
('php', '对象注入', '09_object.php', '反序列化可注入恶意对象利用魔术方法。', 'hard', 'flag{php_object_09}', '构造序列化数据利用__destruct方法', 9);

-- SQL 注入 (57题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('sqli', '联合查询注入', '01_union.php', '这是一个简单的登录页面，后端使用SQL查询验证用户名和密码。\n尝试通过联合查询（UNION SELECT）获取数据库中的敏感信息。', 'easy', 'flag{sqli_union_01}', '试试用户名输入 admin\' UNION SELECT 1,2,3,4 -- ，观察页面显示了哪些字段', 1),
('sqli', '报错注入', '02_error.php', '后端开启了SQL错误回显。利用报错信息来提取数据库数据。', 'easy', 'flag{sqli_error_02}', '尝试使用 extractvalue() 或 updatexml() 函数触发报错并回显数据', 2),
('sqli', '单引号字符型', '03_single_int.php', 'GET参数id直接拼接到SQL语句中，使用单引号包裹。\n经典入门题，练习基本的单引号闭合注入。', 'easy', 'flag{sqli_single_int_03}', '输入位于单引号字符串中。怎样用最小变化确认引号边界和查询列数？', 3),
('sqli', '数字型注入', '04_single_str.php', 'GET参数id直接拼接为数字，无引号包裹。\n最简单的注入类型，无需闭合引号。', 'easy', 'flag{sqli_single_str_04}', '比较真假算术条件的响应，判断参数是否直接进入数字表达式。', 4),
('sqli', '括号闭合注入', '05_double_quote.php', 'SQL使用 (id) 方式包裹参数，需要闭合括号和引号。', 'easy', 'flag{sqli_double_quote_05}', '错误信息透露了几层引号和括号？逐个匹配语法边界。', 5),
('sqli', '双引号字符型', '06_dquote.php', 'SQL使用双引号包裹参数，需要使用双引号闭合。', 'easy', 'flag{sqli_dquote_06}', '先判断字符串由哪种引号包裹，再验证剩余语句如何闭合。', 6),
('sqli', '双引号+括号注入', '07_dquote_paren.php', 'SQL使用 ("id") 方式包裹参数。', 'easy', 'flag{sqli_dquote_paren_07}', '观察报错位置，分别处理字符串边界与外层括号。', 7),
('sqli', '布尔盲注', '08_blind.php', '页面不会显示查询结果，也不会显示错误信息，但会根据查询结果返回不同的页面内容（True/False）。\n通过布尔判断一位一位地提取数据。', 'medium', 'flag{sqli_blind_08}', '用 AND SUBSTR(database(),1,1)=\'v\' 这样的方式逐字符判断', 8),
('sqli', '时间盲注', '09_time.php', '页面返回内容完全相同，无法通过布尔判断。需要利用时间延迟来判断注入结果。', 'medium', 'flag{sqli_time_09}', '使用 IF(condition, SLEEP(3), 0) 来根据条件制造延迟', 9),
('sqli', '堆叠注入', '10_stacked.php', '后端使用了 mysqli_multi_query，允许执行多条SQL语句。\n利用堆叠注入执行任意SQL。', 'medium', 'flag{sqli_stacked_10}', '尝试用 ; 分隔符执行第二条SQL语句，如 INSERT 或 UPDATE', 10),
('sqli', 'HTTP头注入', '11_header.php', '后端将HTTP请求头（如 User-Agent, Referer）直接写入数据库，且未做过滤。\n通过伪造HTTP头进行注入。', 'medium', 'flag{sqli_header_11}', '在 User-Agent 或 Referer 头中注入SQL语句', 11),
('sqli', 'XOR注入', '12_xor.php', '利用XOR运算进行SQL注入，绕过简单的关键字过滤。', 'medium', 'flag{sqli_xor_12}', '使用 XOR 运算符构造注入条件', 12),
('sqli', 'JSON注入', '13_json.php', '后端处理JSON格式数据时存在注入漏洞。', 'medium', 'flag{sqli_json_13}', '在JSON字段中注入SQL语句', 13),
('sqli', '正则注入', '14_regexp.php', '后端使用REGEXP进行正则匹配查询，存在注入。', 'medium', 'flag{sqli_regexp_14}', '利用正则表达式的特性进行盲注', 14),
('sqli', '双重写入注入', '15_doublewrite.php', '过滤器只替换一次关键字，双写可绕过。', 'medium', 'flag{sqli_doublewrite_15}', '试试 selselectect 这样的双写', 15),
('sqli', '多语句执行', '16_multi_query.php', '后端支持多语句执行，可使用分号分隔执行多条SQL。', 'medium', 'flag{sqli_multi_query_16}', '确认数据库接口是否支持多语句，以及第二条语句的结果如何被观察。', 16),
('sqli', 'UPDATE注入', '17_update_inject.php', '用户资料更新功能，email参数存在注入漏洞。', 'medium', 'flag{sqli_update_inject_17}', '先还原 UPDATE 的字段与 WHERE 结构，哪些边界可影响更新目标？', 17),
('sqli', 'INSERT注入', '18_insert_inject.php', '用户注册功能，INSERT语句中存在注入漏洞。', 'medium', 'flag{sqli_insert_inject_18}', '推断 INSERT 的列数和值列表结构，输入能否改变元组边界？', 18),
('sqli', 'DELETE注入', '19_delete_inject.php', '删除功能直接将ID拼接到DELETE语句中。', 'medium', 'flag{sqli_delete_inject_19}', '比较单个对象条件与扩大条件后的影响范围，确认 WHERE 是否可控。', 19),
('sqli', 'ORDER BY注入', '20_order_by.php', '排序字段直接拼接到SQL中，可在ORDER BY子句注入。', 'medium', 'flag{sqli_order_by_20}', 'ORDER BY注入使用报错或盲注', 20),
('sqli', 'LIMIT注入', '21_limit_inject.php', 'LIMIT子句参数可控，可进行注入。', 'medium', 'flag{sqli_limit_inject_21}', '参数处于 LIMIT 上下文，先确认数据库允许的语法边界与可见结果。', 21),
('sqli', 'fetch方式差异', '22_fetch_array.php', '使用FETCH_NUM方式获取结果，返回数字索引数组。', 'medium', 'flag{sqli_fetch_array_22}', '注意列数匹配，使用数字索引', 22),
('sqli', '子查询注入', '23_subquery.php', '参数在子查询中，可利用子查询进行注入。', 'medium', 'flag{sqli_subquery_23}', '先定位参数所在的子查询括号层级，再寻找外层页面可观察的信号。', 23),
('sqli', '猜列名注入', '24_column_guess.php', '不显示列名，需要猜测表结构进行注入。', 'medium', 'flag{sqli_column_guess_24}', '常见列名: id, username, password, email, role', 24),
('sqli', 'LIKE盲注', '25_like_blind.php', '使用LIKE模糊查询，通过布尔结果判断数据。', 'medium', 'flag{sqli_like_blind_25}', 'LIKE注入: % and substr(username,1,1)=a--+', 25),
('sqli', 'REGEXP盲注', '26_regexp_blind.php', '使用REGEXP正则匹配，通过结果判断数据。', 'medium', 'flag{sqli_regexp_blind_26}', '先用两个正则条件建立真假响应，再逐步缩小未知字符串范围。', 26),
('sqli', 'BETWEEN盲注', '27_between_blind.php', '使用BETWEEN范围查询，通过结果判断数据。', 'medium', 'flag{sqli_between_blind_27}', 'BETWEEN 能表达范围判断，怎样利用响应差异做二分推断？', 27),
('sqli', 'IN子查询盲注', '28_in_blind.php', 'IN子句参数可控，可进行子查询注入。', 'medium', 'flag{sqli_in_blind_28}', '还原 IN 列表或子查询的括号结构，并寻找布尔结果的页面信号。', 28),
('sqli', 'EXISTS盲注', '29_exists_blind.php', 'EXISTS子查询中存在注入点。', 'medium', 'flag{sqli_exists_blind_29}', 'EXISTS注入判断数据是否存在', 29),
('sqli', '大小写绕过', '30_case_bypass.php', '过滤了小写关键字，使用大小写混写绕过。', 'medium', 'flag{sqli_case_bypass_30}', '大小写绕过: SeLeCt, UnIoN, FrOm', 30),
('sqli', '关键字双写绕过', '31_keyword_bypass.php', '过滤器只替换一次关键字，双写可绕过。', 'medium', 'flag{sqli_keyword_bypass_31}', '双写绕过: selselectect, ununionion', 31),
('sqli', 'OR/AND绕过', '32_or_and_bypass.php', '过滤了OR和AND关键字，使用替代写法。', 'medium', 'flag{sqli_or_and_bypass_32}', '双写: oorr, aandd 或 ||, &&', 32),
('sqli', '等号绕过', '33_equal_bypass.php', '过滤了等号，使用LIKE、REGEXP等替代。', 'medium', 'flag{sqli_equal_bypass_33}', '使用 LIKE, REGEXP, BETWEEN, IN 代替', 33),
('sqli', 'EXTRACTVALUE报错', '34_xpath_extract.php', '使用EXTRACTVALUE函数触发XML解析错误并回显数据。', 'medium', 'flag{sqli_xpath_extract_34}', 'EXTRACTVALUE(1,CONCAT(0x7e,version()))', 34),
('sqli', 'UPDATEXML报错', '35_update_xml.php', '使用UPDATEXML函数触发XML解析错误并回显数据。', 'medium', 'flag{sqli_update_xml_35}', 'UPDATEXML(1,CONCAT(0x7e,version()),1)', 35),
('sqli', 'FLOOR报错', '36_floor.php', '使用FLOOR+RAND+GROUP BY触发主键重复报错。', 'medium', 'flag{sqli_floor_36}', 'FLOOR(RAND(0)*2) GROUP BY报错', 36),
('sqli', '二次注入', '37_second.php', '注册时对输入做了转义，但登录后的查询直接使用了数据库中的值（未转义）。\n利用注册->查询的流程进行二次注入。', 'hard', 'flag{sqli_second_37}', '先注册一个包含注入Payload的用户名，再通过修改密码功能触发注入', 37),
('sqli', 'WAF绕过', '38_waf.php', '后端有简单的WAF过滤，拦截了 select、union、from 等关键字。\n尝试绕过WAF完成注入。', 'hard', 'flag{sqli_waf_38}', '试试大小写混写、双写绕过、内联注释 /**/ 等方式', 38),
('sqli', '写文件注入', '39_writefile.php', '利用SQL注入写入WebShell文件到服务器。', 'hard', 'flag{sqli_writefile_39}', '使用 SELECT INTO OUTFILE 写文件', 39),
('sqli', '内联注释注入', '40_inline.php', '使用MySQL内联注释绕过WAF过滤。', 'hard', 'flag{sqli_inline_40}', '使用 /*!SELECT*/ 等内联注释语法', 40),
('sqli', '约束注入', '41_constraint.php', '利用MySQL类型转换和约束条件进行注入。', 'hard', 'flag{sqli_constraint_41}', '利用字符串截断和类型转换特性', 41),
('sqli', 'HAVING注入', '42_having_inject.php', 'HAVING子句参数可控，可泄露列名和数据。', 'hard', 'flag{sqli_having_inject_42}', 'HAVING报错泄露列名', 42),
('sqli', 'INTO OUTFILE', '43_into_outfile.php', '利用INTO OUTFILE将查询结果写入服务器文件。', 'hard', 'flag{sqli_into_outfile_43}', 'INTO OUTFILE写WebShell', 43),
('sqli', 'INTO DUMPFILE', '44_into_dumpfile.php', 'INTO DUMPFILE与INTO OUTFILE的区别在于不换行。', 'hard', 'flag{sqli_into_dumpfile_44}', '适合写二进制文件如UDF', 44),
('sqli', 'BENCHMARK盲注', '45_benchmark.php', '使用BENCHMARK函数进行时间盲注。', 'hard', 'flag{sqli_benchmark_45}', 'BENCHMARK(10000000,SHA1(test))', 45),
('sqli', 'SLEEP盲注', '46_sleep.php', '使用SLEEP函数进行时间盲注，页面无回显。', 'hard', 'flag{sqli_sleep_46}', '内容相同时，建立多次请求的时间基线并比较条件真假造成的延迟。', 46),
('sqli', '空格绕过', '47_space_bypass.php', '过滤了空格字符，使用其他方式代替空格。', 'hard', 'flag{sqli_space_bypass_47}', '空格绕过: /**/, %09(tab), %0a(换行)', 47),
('sqli', '注释符绕过', '48_comment_bypass.php', '过滤了常见注释符，使用其他方式闭合。', 'hard', 'flag{sqli_comment_bypass_48}', '使用 %00 或引号闭合', 48),
('sqli', '引号绕过', '49_quote_bypass.php', '过滤了单引号和双引号，使用其他方式绕过。', 'hard', 'flag{sqli_quote_bypass_49}', '0x编码或CHAR函数: 0x61646D696E', 49),
('sqli', 'HEX编码绕过', '50_hex_bypass.php', '过滤了引号和关键字，使用十六进制编码绕过。', 'hard', 'flag{sqli_hex_bypass_50}', 'HEX编码: 0x61646D696E 代替 admin', 50),
('sqli', '宽字节注入', '51_wide_byte.php', '使用addslashes转义，但GBK编码下可使用宽字节绕过。', 'hard', 'flag{sqli_wide_byte_51}', '宽字节: %bf%27', 51),
('sqli', '多字节注入', '52_multibyte.php', '使用mysql_real_escape_string转义，多字节编码下可绕过。', 'hard', 'flag{sqli_multibyte_52}', '多字节编码绕过: %bf%27', 52),
('sqli', 'DNS外带注入', '53_dns_exfil.php', '无回显时通过DNS请求外带数据。', 'hard', 'flag{sqli_dns_exfil_53}', 'LOAD_FILE CONCAT DNS外带', 53),
('sqli', 'GEOMETRY报错', '54_geometry.php', '使用几何函数触发报错注入。', 'hard', 'flag{sqli_geometry_54}', 'ST_LatFromGeoHash, polygon等几何函数', 54),
('sqli', '堆叠+字符集', '55_stack_encoding.php', '支持多语句执行，可修改字符集辅助注入。', 'hard', 'flag{sqli_stack_encoding_55}', 'SET NAMES gbk修改字符集', 55),
('sqli', '无错误回显', '56_no_error.php', '页面不显示任何错误信息，只能通过布尔判断。', 'hard', 'flag{sqli_no_error_56}', '布尔盲注或时间盲注', 56),
('sqli', '多表联合注入', '57_multi_table.php', '多表JOIN查询，需要理解表结构进行注入。', 'hard', 'flag{sqli_multi_table_57}', '多表UNION查询，注意列数匹配', 57);

-- SSRF (8题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('ssrf', '基础SSRF', '01_basic.php', 'URL采集功能会请求用户指定的URL。\n利用SSRF访问内网资源或本地文件。', 'medium', 'flag{ssrf_basic_01}', '试试 http://127.0.0.1 或 file:///etc/passwd', 1),
('ssrf', 'IPv6 SSRF', '02_ipv6.php', '只检查IPv4内网地址，IPv6绕过。', 'medium', 'flag{ssrf_ipv6_02}', 'http://[::1]/ 是IPv6的127.0.0.1', 2),
('ssrf', 'URL解析差异SSRF', '03_urlparse.php', '利用URL解析差异绕过黑名单。', 'medium', 'flag{ssrf_urlparse_03}', 'http://127.0.0.1.nip.io/ 解析到127.0.0.1', 3),
('ssrf', '协议利用', '04_protocol.php', '后端只允许HTTP协议，但可以通过特殊协议绕过。\n利用gopher、dict等协议扩展攻击面。', 'hard', 'flag{ssrf_protocol_04}', '试试 gopher://127.0.0.1:3306/ 或 dict://127.0.0.1:6379/', 4),
('ssrf', '过滤绕过', '05_bypass.php', '后端过滤了 127.0.0.1 和 localhost，但可以使用其他表示方式。\n绕过IP过滤访问内网。', 'hard', 'flag{ssrf_bypass_05}', '试试 0x7f000001、2130706433、0.0.0.0、[::1] 等表示方式', 5),
('ssrf', '重定向SSRF', '06_redirect.php', '跟随重定向可绕过地址检查。', 'hard', 'flag{ssrf_redirect_06}', '在攻击者服务器设置302重定向到内网', 6),
('ssrf', 'DNS重绑定SSRF', '07_dns.php', 'DNS重绑定技术绕过IP检查。', 'hard', 'flag{ssrf_dns_07}', '使用TTL=0的DNS记录，第一次解析返回外网IP', 7),
('ssrf', 'Gopher协议SSRF', '08_gopher.php', '使用gopher://协议攻击内网服务。', 'hard', 'flag{ssrf_gopher_08}', 'gopher://127.0.0.1:6379/_*1%0d%0a$4%0d%0aINFO', 8);

-- SSTI (8题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('ssti', '基础SSTI', '01_basic.php', '用户输入直接作为模板内容渲染，未做任何过滤。\n执行模板表达式获取信息。', 'medium', 'flag{ssti_basic_01}', '先用无副作用的表达式确认模板是否求值，再识别模板引擎及其可访问对象。', 1),
('ssti', 'Jinja2 SSTI', '02_jinja.php', '模板引擎支持表达式执行。', 'medium', 'flag{ssti_jinja_02}', '先以无副作用表达式确认求值，再根据错误信息识别模板引擎和对象边界。', 2),
('ssti', 'Smarty SSTI', '03_smarty.php', 'Smarty模板的{php}标签可执行代码。', 'medium', 'flag{ssti_smarty_03}', '确认当前 Smarty 版本支持哪些标签，以及服务器是否启用了受限的执行能力。', 3),
('ssti', 'Twig SSTI', '04_twig.php', 'Twig模板存在代码注入漏洞。', 'medium', 'flag{ssti_twig_04}', '比较变量表达式和控制标签的反馈，识别 Twig 版本与可调用对象。', 4),
('ssti', '错误信息SSTI', '05_error.php', '错误信息泄露模板引擎细节。', 'medium', 'flag{ssti_error_05}', '故意触发错误获取服务器信息', 5),
('ssti', 'SSTI过滤绕过', '06_filter.php', '后端过滤了 {{、}}、_ 等模板语法字符。\n绕过过滤执行模板注入。', 'hard', 'flag{ssti_filter_06}', '试试 {% %} 标签语法，或字符串拼接绕过', 6),
('ssti', '沙箱逃逸', '07_sandbox.php', '模板引擎开启了沙箱模式，限制了危险函数的调用。\n找到沙箱的弱点执行系统命令。', 'hard', 'flag{ssti_sandbox_07}', '利用Python的MRO链或__import__绕过沙箱限制', 7),
('ssti', 'WAF绕过SSTI', '08_waf.php', 'WAF过滤常见危险函数，需替代方法。', 'hard', 'flag{ssti_waf_08}', '使用反引号或动态函数调用绕过', 8);

-- 目录遍历 (8题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('traversal', '基础路径穿越', '01_basic.php', '文件下载功能通过参数指定文件名，未过滤路径分隔符。\n读取上级目录的敏感文件。', 'easy', 'flag{traversal_basic_01}', '试试 ?file=../../../config/database.php', 1),
('traversal', '绝对路径遍历', '02_absolute.php', '过滤了..但允许绝对路径。', 'easy', 'flag{traversal_absolute_02}', '直接使用 /etc/passwd 等绝对路径', 2),
('traversal', '通配符遍历', '03_glob.php', '使用通配符列出文件。', 'easy', 'flag{traversal_glob_03}', '使用 *.txt 或 * 通配符', 3),
('traversal', '过滤绕过', '04_filter.php', '后端过滤了 ../，但过滤不够严格。\n绕过过滤读取敏感文件。', 'medium', 'flag{traversal_filter_04}', '试试双写 ....// 或使用绝对路径', 4),
('traversal', '双写绕过', '05_double.php', '过滤../替换为空，双写可绕过。', 'medium', 'flag{traversal_double_05}', '使用 ....// 过滤后变成 ../', 5),
('traversal', 'URL编码遍历', '06_encode.php', '过滤../后URL解码，编码绕过。', 'medium', 'flag{traversal_encode_06}', '%2e%2e%2f 解码后变成 ../', 6),
('traversal', '截断绕过', '07_null.php', '后端在文件名后添加了固定后缀（如 .html），但存在空字节截断漏洞。', 'hard', 'flag{traversal_null_07}', '试试 %00 截断（适用于PHP < 5.3.4）', 7),
('traversal', 'ZIP路径穿越', '08_zip.php', 'ZIP文件解压时路径穿越。', 'hard', 'flag{traversal_zip_08}', '在ZIP中包含 ../../evil.php 文件名', 8);

-- 反序列化 (9题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('unserialize', '基础反序列化', '01_basic.php', '后端对用户输入进行 unserialize()，且有可利用的类。\n构造恶意序列化字符串触发漏洞。', 'medium', 'flag{unserialize_basic_01}', '查看源码中的类定义，找到 __destruct 或 __toString 方法', 1),
('unserialize', '魔术方法利用', '02_magic.php', '存在多个带有魔术方法的类。\n利用 __wakeup、__destruct、__toString 等方法构造攻击链。', 'medium', 'flag{unserialize_magic_02}', '分析每个魔术方法的逻辑，找到可串联的方法链', 2),
('unserialize', '__toString利用', '03_tostring.php', '对象被当字符串使用时触发__toString。', 'medium', 'flag{unserialize_tostring_03}', '构造Display->data = Config对象', 3),
('unserialize', '__call利用', '04_call.php', '调用不存在的方法时触发__call。', 'medium', 'flag{unserialize_call_04}', 'Proxy类的__call会调用预设的方法', 4),
('unserialize', '文件删除', '05_destruct.php', '__destruct可控制删除的文件路径。', 'medium', 'flag{unserialize_destruct_05}', '构造TempFile对象，filename设为重要文件路径', 5),
('unserialize', 'POP链构造', '06_pop.php', '存在多个类，需要构造完整的POP链从入口到危险函数。\n链式调用多个类的方法。', 'hard', 'flag{unserialize_pop_06}', '从 __destruct 开始，逐个追踪方法调用直到 eval 或 system', 6),
('unserialize', 'Phar反序列化', '07_phar.php', '后端有文件操作功能（如文件存在性检查），可以触发 phar:// 协议的反序列化。', 'hard', 'flag{unserialize_phar_07}', '生成phar文件，在文件操作中使用 phar:// 协议触发反序列化', 7),
('unserialize', '__wakeup绕过', '08_wakeup.php', '通过属性数量绕过__wakeup方法。', 'hard', 'flag{unserialize_wakeup_08}', '序列化字符串中属性数量大于实际数量', 8),
('unserialize', 'Session反序列化', '09_session.php', '不同Session处理器格式差异导致攻击。', 'hard', 'flag{unserialize_session_09}', '切换php和php_serialize处理器', 9);

-- 文件上传 (12题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('upload', '前端校验绕过', '01_frontend.php', '文件上传只在前端JavaScript校验文件类型。\n绕过前端验证上传WebShell。', 'easy', 'flag{upload_frontend_01}', '禁用JavaScript或直接修改请求包中的文件名', 1),
('upload', 'MIME校验绕过', '02_mime.php', '后端仅检查Content-Type头（MIME类型）。\n修改请求中的Content-Type绕过验证。', 'easy', 'flag{upload_mime_02}', '将Content-Type从 application/x-php 改为 image/jpeg', 2),
('upload', '扩展名绕过', '03_ext.php', '后端使用黑名单过滤了 .php 后缀，但黑名单不完整。\n尝试使用其他可执行的扩展名。', 'medium', 'flag{upload_ext_03}', '试试 .php3, .php5, .phtml, .pht 等扩展名', 3),
('upload', '文件内容绕过', '04_content.php', '后端检查文件内容的前几个字节（文件头）来判断类型。\n在WebShell文件头添加图片文件头绕过检查。', 'medium', 'flag{upload_content_04}', '在PHP文件开头添加 GIF89a 或 PNG文件头', 4),
('upload', '双扩展名绕过', '05_double.php', '后端会删除文件名中的 .php 扩展名，但只删除一次。\n利用双写绕过。', 'medium', 'flag{upload_double_05}', '试试 shell.pphphp 这样的双写绕过', 5),
('upload', '大小写绕过', '06_size.php', '黑名单只检查小写扩展名。', 'medium', 'flag{upload_size_06}', '上传 shell.PhP 或 shell.pHp', 6),
('upload', '中文字符绕过', '07_chinese.php', '使用中文特殊字符绕过扩展名检测。', 'medium', 'flag{upload_chinese_07}', '使用中文句号：shell.php。jpg', 7),
('upload', '图片马', '08_imagephp.php', '图片中嵌入PHP代码，配合文件包含使用。', 'medium', 'flag{upload_imagephp_08}', 'copy /b normal.jpg + shell.php shell.jpg', 8),
('upload', '条件竞争', '09_race.php', '后端先保存文件再检查扩展名，如果不合法则删除。\n利用上传和删除之间的时间差执行WebShell。', 'hard', 'flag{upload_race_09}', '用Burp Intruder持续上传，同时持续请求上传后的文件', 9),
('upload', '.htaccess上传', '10_htaccess.php', '上传.htaccess文件改变Apache解析规则。', 'hard', 'flag{upload_htaccess_10}', '上传.htaccess添加 AddType application/x-httpd-php .jpg', 10),
('upload', '移动绕过', '11_move.php', '先保存文件再检查，存在时间窗口。', 'hard', 'flag{upload_move_11}', '利用保存到删除之间的时间窗口', 11),
('upload', '截断上传', '12_trunc.php', '自定义文件名时利用空字节截断。', 'hard', 'flag{upload_trunc_12}', '文件名: shell.php%00.jpg', 12);

-- XSS (12题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('xss', '反射型XSS', '01_reflected.php', '搜索功能直接将用户输入回显到页面，未做过滤。\n通过构造恶意JS脚本获取Cookie。', 'easy', 'flag{xss_reflected_01}', '直接在搜索框输入 <script>alert(1)</script> 试试', 1),
('xss', '存储型XSS', '02_stored.php', '留言板功能，用户提交的留言会存储到数据库并显示给所有用户。\n插入恶意脚本，当其他用户访问时执行。', 'easy', 'flag{xss_stored_02}', '在留言内容中插入 <script> 标签', 2),
('xss', 'DOM型XSS', '03_dom.php', '页面使用JavaScript从URL参数中读取数据并直接写入DOM（innerHTML）。\n不需要服务端参与，纯前端漏洞。', 'medium', 'flag{xss_dom_03}', '观察页面JS代码，找到从URL取值并写入DOM的位置', 3),
('xss', 'XSS过滤绕过', '04_filter.php', '后端对 <script> 标签进行了过滤，但过滤不够严格。\n尝试绕过过滤实现XSS。', 'medium', 'flag{xss_filter_04}', '试试大小写、标签嵌套、事件属性等方式', 4),
('xss', '事件处理绕过', '05_event.php', '过滤了常见的事件属性（onclick, onerror等），但没有覆盖所有事件。\n找到未被过滤的事件处理器。', 'medium', 'flag{xss_event_05}', '试试 onfocus, onmouseover, onanimationstart 等较少见的事件', 5),
('xss', 'SVG XSS', '06_svg.php', '上传SVG文件触发XSS，SVG是XML格式可嵌入JS。', 'medium', 'flag{xss_svg_06}', '在SVG中使用 script 标签或事件属性', 6),
('xss', 'Markdown XSS', '07_markdown.php', 'Markdown渲染器未过滤HTML标签。', 'medium', 'flag{xss_markdown_07}', '在Markdown中直接写 img onerror=alert(1)', 7),
('xss', 'Base64 XSS', '08_base64.php', '后端解码Base64后直接输出到页面。', 'medium', 'flag{xss_base64_08}', '将XSS Payload进行Base64编码后提交', 8),
('xss', 'JSONP XSS', '09_jsonp.php', 'JSONP接口的callback参数未过滤。', 'medium', 'flag{xss_jsonp_09}', '修改callback参数注入JS代码', 9),
('xss', '编码绕过XSS', '10_encode.php', '使用URL编码双重编码绕过过滤。', 'medium', 'flag{xss_encode_10}', '使用双重URL编码绕过过滤器', 10),
('xss', 'CSP绕过', '11_csp.php', '页面设置了Content-Security-Policy头，限制了脚本来源。\n找到CSP策略的弱点，绕过限制执行JS。', 'hard', 'flag{xss_csp_11}', '检查CSP头，看是否允许 unsafe-inline 或有可利用的CDN域名', 11),
('xss', 'Mutation XSS', '12_mutation.php', '浏览器解析HTML时产生变异，绕过过滤器。', 'hard', 'flag{xss_mutation_12}', '利用noscript标签和属性变异绕过过滤', 12);

-- XXE (8题)
INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES
('xxe', '基础XXE', '01_basic.php', 'XML解析功能未禁用外部实体加载。\n通过XXE读取服务器本地文件。', 'medium', 'flag{xxe_basic_01}', '构造DTD定义外部实体指向本地文件，如 <!ENTITY xxe SYSTEM "file:///etc/passwd">', 1),
('xxe', 'XXE DoS', '02_dos.php', '利用XML实体扩展（Billion Laughs Attack）造成拒绝服务。', 'medium', 'flag{xxe_dos_02}', '构造递归实体扩展，如 <!ENTITY lol "lol"> <!ENTITY lol2 "&lol;&lol;">', 2),
('xxe', 'SVG XXE', '03_svg.php', 'SVG是XML格式，上传恶意SVG触发XXE。', 'medium', 'flag{xxe_svg_03}', '在SVG中嵌入DTD和外部实体', 3),
('xxe', 'CDATA XXE', '04_cdata.php', '使用CDATA包裹绕过XML解析限制。', 'medium', 'flag{xxe_cdata_04}', '在CDATA中引用外部实体', 4),
('xxe', 'XPath XXE', '05_xpath.php', '自定义XML和XPath查询，可注入外部实体。', 'medium', 'flag{xxe_xpath_05}', '在XML中添加DTD定义外部实体', 5),
('xxe', 'JSON转XML XXE', '06_json.php', 'JSON数据转换为XML处理时注入实体。', 'medium', 'flag{xxe_json_06}', '在JSON值中包含XML标记和DTD', 6),
('xxe', 'SOAP XXE', '07_soap.php', 'SOAP接口接受XML请求，可注入外部实体。', 'medium', 'flag{xxe_soap_07}', '在SOAP XML中注入DTD和实体', 7),
('xxe', '盲XXE', '08_blind.php', 'XML解析不回显结果，但会处理外部实体。\n通过外带数据（OOB）提取信息。', 'hard', 'flag{xxe_blind_08}', '使用外部DTD + 参数实体将数据外带到你的服务器', 8);
