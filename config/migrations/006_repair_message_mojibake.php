<?php

// 修复早期数据库初始化时写入 messages 表的历史双重编码文本。
function repairMessageMojibake($value) {
    $chars = preg_split('//u', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) return null;

    $map = [
        0x20AC => 0x80, 0x201A => 0x82, 0x192 => 0x83, 0x201E => 0x84,
        0x2026 => 0x85, 0x2020 => 0x86, 0x2021 => 0x87, 0x2C6 => 0x88,
        0x2030 => 0x89, 0x160 => 0x8A, 0x2039 => 0x8B, 0x152 => 0x8C,
        0x17D => 0x8E, 0x2018 => 0x91, 0x2019 => 0x92, 0x201C => 0x93,
        0x201D => 0x94, 0x2022 => 0x95, 0x2013 => 0x96, 0x2014 => 0x97,
        0x2DC => 0x98, 0x2122 => 0x99, 0x161 => 0x9A, 0x203A => 0x9B,
        0x153 => 0x9C, 0x17E => 0x9E, 0x178 => 0x9F,
    ];
    $bytes = '';
    foreach ($chars as $char) {
        $codepoint = function_exists('mb_ord') ? mb_ord($char, 'UTF-8') : ord($char[0]);
        if ($codepoint <= 0xFF) {
            $bytes .= chr($codepoint);
        } elseif (isset($map[$codepoint])) {
            $bytes .= chr($map[$codepoint]);
        } else {
            return null;
        }
    }
    $fixed = @iconv('UTF-8', 'UTF-8//IGNORE', $bytes);
    return $fixed === false ? null : $fixed;
}

return function (PDO $db) {
    $select = $db->query('SELECT id, username, content FROM messages ORDER BY id');
    $update = $db->prepare('UPDATE messages SET username = ?, content = ? WHERE id = ?');
    while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
        $username = (string) ($row['username'] ?? '');
        $content = (string) ($row['content'] ?? '');
        $fixedUsername = repairMessageMojibake($username);
        $fixedContent = repairMessageMojibake($content);
        $hasMojibake = preg_match('/[\x{00C0}-\x{00FF}€™šœžŸ]/u', $username . $content);
        if ($hasMojibake && $fixedUsername !== null && $fixedContent !== null) {
            $update->execute([$fixedUsername, $fixedContent, $row['id']]);
        }
    }
};
