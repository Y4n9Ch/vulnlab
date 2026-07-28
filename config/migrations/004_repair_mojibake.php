<?php

// 早期初始化时，部分 UTF-8 文本曾被按 Windows-1252/Latin-1 解码后再次写入。
// 将这些字符逐个还原为原始字节，再按 UTF-8 重新解码；正常中文不满足转换条件，不会被改动。
function mojibakeCodepoint($char) {
    if (function_exists('mb_ord')) return mb_ord($char, 'UTF-8');

    $bytes = unpack('C*', $char);
    if (!$bytes) return null;
    $first = $bytes[1];
    if ($first < 0x80) return $first;
    if (($first & 0xE0) === 0xC0 && isset($bytes[2])) {
        return (($first & 0x1F) << 6) | ($bytes[2] & 0x3F);
    }
    if (($first & 0xF0) === 0xE0 && isset($bytes[2], $bytes[3])) {
        return (($first & 0x0F) << 12) | (($bytes[2] & 0x3F) << 6) | ($bytes[3] & 0x3F);
    }
    if (($first & 0xF8) === 0xF0 && isset($bytes[2], $bytes[3], $bytes[4])) {
        return (($first & 0x07) << 18) | (($bytes[2] & 0x3F) << 12) | (($bytes[3] & 0x3F) << 6) | ($bytes[4] & 0x3F);
    }
    return null;
}

function mojibakeWindows1252Byte($codepoint) {
    $map = [
        0x20AC => 0x80, 0x201A => 0x82, 0x192 => 0x83, 0x201E => 0x84,
        0x2026 => 0x85, 0x2020 => 0x86, 0x2021 => 0x87, 0x2C6 => 0x88,
        0x2030 => 0x89, 0x160 => 0x8A, 0x2039 => 0x8B, 0x152 => 0x8C,
        0x17D => 0x8E, 0x2018 => 0x91, 0x2019 => 0x92, 0x201C => 0x93,
        0x201D => 0x94, 0x2022 => 0x95, 0x2013 => 0x96, 0x2014 => 0x97,
        0x2DC => 0x98, 0x2122 => 0x99, 0x161 => 0x9A, 0x203A => 0x9B,
        0x153 => 0x9C, 0x17E => 0x9E, 0x178 => 0x9F,
    ];
    return $map[$codepoint] ?? null;
}

function repairMojibakeText($value) {
    $value = (string) $value;
    $chars = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) return null;

    $bytes = '';
    foreach ($chars as $char) {
        $codepoint = mojibakeCodepoint($char);
        if ($codepoint === null) return null;
        if ($codepoint <= 0xFF) {
            $bytes .= chr($codepoint);
            continue;
        }
        $byte = mojibakeWindows1252Byte($codepoint);
        if ($byte === null) return null;
        $bytes .= chr($byte);
    }

    $fixed = @iconv('UTF-8', 'UTF-8//IGNORE', $bytes);
    return $fixed === false ? null : $fixed;
}

return function (PDO $db) {
    $select = $db->query('SELECT id, title, description, hint FROM challenges ORDER BY id');
    $update = $db->prepare('UPDATE challenges SET title = ?, description = ?, hint = ? WHERE id = ?');

    while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
        $values = [];
        $changed = false;
        foreach (['title', 'description', 'hint'] as $field) {
            $original = (string) ($row[$field] ?? '');
            $fixed = repairMojibakeText($original);
            // 正常中文含有无法映射回单字节的码点，只有疑似乱码文本会通过此条件。
            if ($fixed !== null && $fixed !== $original && preg_match('/[\x{00C0}-\x{00FF}€™šœžŸ]/u', $original)) {
                $values[$field] = $fixed;
                $changed = true;
            } else {
                $values[$field] = $original;
            }
        }
        if ($changed) {
            $update->execute([$values['title'], $values['description'], $values['hint'], $row['id']]);
        }
    }
};
