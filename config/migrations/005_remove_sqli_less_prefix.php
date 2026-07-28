<?php

return function (PDO $db) {
    // 仅处理 SQL 注入分类的展示标题，不修改题目文件名、Flag 或排序。
    $db->exec("UPDATE challenges
        SET title = REGEXP_REPLACE(title, '^Less-[0-9]+[[:space:]]+', '')
        WHERE category = 'sqli' AND title REGEXP '^Less-[0-9]+[[:space:]]+'");
};
