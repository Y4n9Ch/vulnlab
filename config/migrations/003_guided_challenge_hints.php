<?php

return function (PDO $db) {
    $hints = [
        ['access', '01_idor.php', '比较当前对象标识与相邻标识的响应，服务端是否验证了对象归属？'],
        ['ssti', '01_basic.php', '先用无副作用的表达式确认模板是否求值，再识别模板引擎及其可访问对象。'],
        ['php', '01_eval.php', '先确认输入是否进入代码执行上下文，再用无副作用表达式观察返回值。'],
        ['inclusion', '08_lfi_log.php', '确认哪一个请求头被写入日志、日志保存在哪里，以及包含时是否进入解释器。'],
        ['ssti', '04_jinja.php', '先以无副作用表达式确认求值，再根据错误信息识别模板引擎和对象边界。'],
        ['ssti', '05_smarty.php', '确认当前 Smarty 版本支持哪些标签，以及服务器是否启用了受限的执行能力。'],
        ['ssti', '06_twig.php', '比较变量表达式和控制标签的反馈，识别 Twig 版本与可调用对象。'],
        ['php', '06_extract.php', '列出 extract 前后的变量名，哪些用户字段可能覆盖内部授权状态？'],
        ['php', '07_parse.php', '追踪 parse_str 生成的变量，哪些名称与已有授权变量发生冲突？'],
        ['sqli', '16_single_int.php', '输入位于单引号字符串中。怎样用最小变化确认引号边界和查询列数？'],
        ['sqli', '17_single_str.php', '比较真假算术条件的响应，判断参数是否直接进入数字表达式。'],
        ['sqli', '18_double_quote.php', '错误信息透露了几层引号和括号？逐个匹配语法边界。'],
        ['sqli', '19_dquote.php', '先判断字符串由哪种引号包裹，再验证剩余语句如何闭合。'],
        ['sqli', '20_dquote_paren.php', '观察报错位置，分别处理字符串边界与外层括号。'],
        ['sqli', '21_multi_query.php', '确认数据库接口是否支持多语句，以及第二条语句的结果如何被观察。'],
        ['sqli', '22_update_inject.php', '先还原 UPDATE 的字段与 WHERE 结构，哪些边界可影响更新目标？'],
        ['sqli', '23_insert_inject.php', '推断 INSERT 的列数和值列表结构，输入能否改变元组边界？'],
        ['sqli', '24_delete_inject.php', '比较单个对象条件与扩大条件后的影响范围，确认 WHERE 是否可控。'],
        ['sqli', '26_limit_inject.php', '参数处于 LIMIT 上下文，先确认数据库允许的语法边界与可见结果。'],
        ['sqli', '29_subquery.php', '先定位参数所在的子查询括号层级，再寻找外层页面可观察的信号。'],
        ['sqli', '34_sleep.php', '内容相同时，建立多次请求的时间基线并比较条件真假造成的延迟。'],
        ['sqli', '36_regexp_blind.php', '先用两个正则条件建立真假响应，再逐步缩小未知字符串范围。'],
        ['sqli', '37_between_blind.php', 'BETWEEN 能表达范围判断，怎样利用响应差异做二分推断？'],
        ['sqli', '38_in_blind.php', '还原 IN 列表或子查询的括号结构，并寻找布尔结果的页面信号。'],
    ];

    $stmt = $db->prepare('UPDATE challenges SET hint = ? WHERE category = ? AND file = ?');
    foreach ($hints as [$category, $file, $hint]) {
        $stmt->execute([$hint, $category, $file]);
    }
};
