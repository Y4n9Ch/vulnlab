<?php

return function (PDO $db) {
    $challenges = [
        ['basics', '客户端价格可信', '01_client_price.php', '结算页面把商品价格放在客户端字段中，并直接使用提交值计算订单。观察正常请求，再判断哪些字段不应由客户端决定。', 'easy', 'flag{basics_client_price_01}', '比较页面展示的价格与请求中的价格字段，思考服务端应从哪里获取单价。', 1],
        ['basics', '角色参数越权', '02_role_parameter.php', '个人资料更新接口允许客户端提交角色字段。找出普通资料与权限字段共用更新逻辑产生的风险。', 'easy', 'flag{basics_role_parameter_02}', '查看请求中所有字段，哪些字段不应该出现在普通用户可控范围内？', 2],
        ['basics', '方法覆盖绕过', '03_method_override.php', '接口只阻止了表面的请求方法，却支持由参数覆盖真实操作。理解路由层与业务层对请求方法认知不一致的风险。', 'easy', 'flag{basics_method_override_03}', '服务端判断的操作方法，是否一定等于浏览器发出的 HTTP 方法？', 3],
        ['basics', '重复参数污染', '04_parameter_pollution.php', '网关检查参数列表中的第一个值，应用却使用最后一个值。利用不同解析层对重复参数的理解差异完成权限检查。', 'medium', 'flag{basics_parameter_pollution_04}', '同名参数出现多次时，网关与 PHP 分别会取哪一个？', 4],
        ['basics', 'JSON 类型混淆', '05_json_type.php', 'JSON 接口使用松散比较判断管理权限。分析布尔、数字和字符串在 PHP 比较规则中的差异。', 'medium', 'flag{basics_json_type_05}', '不要只测试字符串。JSON 能表达哪些原生类型？', 5],
        ['basics', '编码顺序错误', '06_decode_order.php', '跳转接口先检查原始文本，再进行 URL 解码和目标解析。检查校验与规范化顺序错误如何产生绕过。', 'medium', 'flag{basics_decode_order_06}', '安全校验应该发生在解码前还是得到最终规范形式后？', 6],
        ['basics', '批量属性绑定', '07_mass_assignment.php', '资料接口把 JSON 对象批量合并到用户模型。识别哪些内部属性可被越权写入，并满足后台导出条件。', 'hard', 'flag{basics_mass_assignment_07}', '对比默认用户模型与提交后的模型，寻找不属于个人资料的属性。', 7],
        ['basics', '签名字段缺失', '08_signature_scope.php', '操作请求带有合法签名，但签名只覆盖了部分业务字段。判断哪些未签名字段仍会影响最终授权目标。', 'hard', 'flag{basics_signature_scope_08}', '列出服务端执行操作时读取的字段，再与签名计算覆盖的字段逐一对照。', 8],
        ['basics', '未签名状态令牌', '09_state_token.php', '多步骤审批流程把状态保存在可解码但未签名的客户端令牌中。恢复令牌结构并审计服务端信任的关键状态。', 'hard', 'flag{basics_state_token_09}', '编码不等于完整性保护。令牌解码后包含哪些决定流程状态的字段？', 9],
    ];

    $stmt = $db->prepare("INSERT INTO challenges (category, title, file, description, difficulty, flag, hint, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $exists = $db->prepare("SELECT 1 FROM challenges WHERE category = ? AND file = ?");
    foreach ($challenges as $challenge) {
        $exists->execute([$challenge[0], $challenge[2]]);
        if (!$exists->fetchColumn()) $stmt->execute($challenge);
    }
};
