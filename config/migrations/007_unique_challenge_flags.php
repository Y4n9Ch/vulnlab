<?php

return function (PDO $db) {
    // 每题唯一验证令牌：flag{<分类>_<短名>_<两位序号>}。
    // 旧数据中大量题目共用 md5 占位令牌，解出任意一题即可通关同组其他题；统一改为按题目唯一。
    $stmt = $db->query("SELECT id, category, file FROM challenges");
    $update = $db->prepare("UPDATE challenges SET flag = ? WHERE id = ?");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!preg_match('/^(\d{2})_([A-Za-z0-9_]+)\.php$/', (string) $row['file'], $m)) continue;
        $flag = 'flag{' . $row['category'] . '_' . strtolower($m[2]) . '_' . $m[1] . '}';
        $update->execute([$flag, $row['id']]);
    }
};
