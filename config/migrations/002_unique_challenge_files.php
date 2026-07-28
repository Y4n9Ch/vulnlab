<?php

return function (PDO $db) {
    $index = $db->query("SHOW INDEX FROM challenges WHERE Key_name = 'unique_challenge_file'")->fetch();
    if (!$index) {
        $db->exec("ALTER TABLE challenges ADD UNIQUE KEY unique_challenge_file (category, file)");
    }
};
