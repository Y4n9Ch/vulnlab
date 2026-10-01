<?php

return function (PDO $db) {
    // Flag 编号与文件序号对齐：题号重排后，按当前文件名重算
    // flag{<分类>_<短名>_<两位序号>}，推导规则与迁移 007 一致。
    // 文件名不再变化时结果不变，重复执行无副作用。
    $stmt = $db->query("SELECT id, category, file FROM challenges");
    $update = $db->prepare("UPDATE challenges SET flag = ? WHERE id = ?");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!preg_match('/^(\d{2})_([A-Za-z0-9_]+)\.php$/', (string) $row['file'], $m)) continue;
        $flag = 'flag{' . $row['category'] . '_' . strtolower($m[2]) . '_' . $m[1] . '}';
        $update->execute([$flag, $row['id']]);
    }
};
