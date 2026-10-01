<?php

/*
 * 按难度递增重排每类题目编号：文件名序号与展示顺序一致（easy -> medium -> hard）。
 * 仅变更 challenges.file 与 sort_order，题目 id、Flag、标题不变，解题进度不受影响。
 * 阶段式执行保证幂等且可抗中断：清理重复行 -> 临时名归位 -> 全部腾名 -> 全部落位。
 */
return function (PDO $db) {
    $renames = [
        // [category, 旧文件名, 新文件名, 新排序]
        ['access', '04_func.php', '02_func.php', 2],
        ['access', '02_vertical.php', '03_vertical.php', 3],
        ['access', '03_horizontal.php', '04_horizontal.php', 4],
        ['auth', '04_brute.php', '02_brute.php', 2],
        ['auth', '06_cookie.php', '03_cookie.php', 3],
        ['auth', '02_jwt.php', '04_jwt.php', 4],
        ['auth', '03_session.php', '05_session.php', 5],
        ['auth', '05_reset.php', '06_reset.php', 6],
        ['auth', '08_otp.php', '07_otp.php', 7],
        ['auth', '09_oauth.php', '08_oauth.php', 8],
        ['auth', '07_timing.php', '09_timing.php', 9],
        ['cmdi', '05_split.php', '04_split.php', 4],
        ['cmdi', '06_hex.php', '05_hex.php', 5],
        ['cmdi', '07_env.php', '06_env.php', 6],
        ['cmdi', '08_base64.php', '07_base64.php', 7],
        ['cmdi', '09_backtick.php', '08_backtick.php', 8],
        ['cmdi', '04_char.php', '09_char.php', 9],
        ['crypto', '05_rc4.php', '03_rc4.php', 3],
        ['crypto', '06_sign.php', '04_sign.php', 4],
        ['crypto', '07_rsa.php', '05_rsa.php', 5],
        ['crypto', '03_ecb.php', '06_ecb.php', 6],
        ['crypto', '04_padding.php', '07_padding.php', 7],
        ['inclusion', '06_lfi_wrapper.php', '04_lfi_wrapper.php', 4],
        ['inclusion', '09_lfi_double.php', '05_lfi_double.php', 5],
        ['inclusion', '10_lfi_glob.php', '06_lfi_glob.php', 6],
        ['inclusion', '04_lfi_log.php', '07_lfi_log.php', 7],
        ['inclusion', '05_lfi_trunc.php', '08_lfi_trunc.php', 8],
        ['inclusion', '07_lfi_session.php', '09_lfi_session.php', 9],
        ['inclusion', '08_lfi_log.php', '10_lfi_log.php', 10],
        ['logic', '02_price.php', '01_price.php', 1],
        ['logic', '03_coupon.php', '02_coupon.php', 2],
        ['logic', '04_verify.php', '03_verify.php', 3],
        ['logic', '05_password.php', '04_password.php', 4],
        ['logic', '06_register.php', '05_register.php', 5],
        ['logic', '07_payment.php', '06_payment.php', 6],
        ['logic', '08_sms.php', '07_sms.php', 7],
        ['logic', '09_workflow.php', '08_workflow.php', 8],
        ['logic', '01_race.php', '09_race.php', 9],
        ['php', '04_dynamic.php', '02_dynamic.php', 2],
        ['php', '02_preg.php', '03_preg.php', 3],
        ['php', '03_callback.php', '04_callback.php', 4],
        ['sqli', '16_single_int.php', '03_single_int.php', 3],
        ['sqli', '17_single_str.php', '04_single_str.php', 4],
        ['sqli', '18_double_quote.php', '05_double_quote.php', 5],
        ['sqli', '19_dquote.php', '06_dquote.php', 6],
        ['sqli', '20_dquote_paren.php', '07_dquote_paren.php', 7],
        ['sqli', '03_blind.php', '08_blind.php', 8],
        ['sqli', '04_time.php', '09_time.php', 9],
        ['sqli', '05_stacked.php', '10_stacked.php', 10],
        ['sqli', '07_header.php', '11_header.php', 11],
        ['sqli', '09_xor.php', '12_xor.php', 12],
        ['sqli', '10_json.php', '13_json.php', 13],
        ['sqli', '12_regexp.php', '14_regexp.php', 14],
        ['sqli', '13_doublewrite.php', '15_doublewrite.php', 15],
        ['sqli', '21_multi_query.php', '16_multi_query.php', 16],
        ['sqli', '22_update_inject.php', '17_update_inject.php', 17],
        ['sqli', '23_insert_inject.php', '18_insert_inject.php', 18],
        ['sqli', '24_delete_inject.php', '19_delete_inject.php', 19],
        ['sqli', '25_order_by.php', '20_order_by.php', 20],
        ['sqli', '26_limit_inject.php', '21_limit_inject.php', 21],
        ['sqli', '28_fetch_array.php', '22_fetch_array.php', 22],
        ['sqli', '29_subquery.php', '23_subquery.php', 23],
        ['sqli', '30_column_guess.php', '24_column_guess.php', 24],
        ['sqli', '35_like_blind.php', '25_like_blind.php', 25],
        ['sqli', '36_regexp_blind.php', '26_regexp_blind.php', 26],
        ['sqli', '37_between_blind.php', '27_between_blind.php', 27],
        ['sqli', '38_in_blind.php', '28_in_blind.php', 28],
        ['sqli', '39_exists_blind.php', '29_exists_blind.php', 29],
        ['sqli', '40_case_bypass.php', '30_case_bypass.php', 30],
        ['sqli', '41_keyword_bypass.php', '31_keyword_bypass.php', 31],
        ['sqli', '44_or_and_bypass.php', '32_or_and_bypass.php', 32],
        ['sqli', '45_equal_bypass.php', '33_equal_bypass.php', 33],
        ['sqli', '51_xpath_extract.php', '34_xpath_extract.php', 34],
        ['sqli', '52_update_xml.php', '35_update_xml.php', 35],
        ['sqli', '53_floor.php', '36_floor.php', 36],
        ['sqli', '06_second.php', '37_second.php', 37],
        ['sqli', '08_waf.php', '38_waf.php', 38],
        ['sqli', '11_writefile.php', '39_writefile.php', 39],
        ['sqli', '14_inline.php', '40_inline.php', 40],
        ['sqli', '15_constraint.php', '41_constraint.php', 41],
        ['sqli', '27_having_inject.php', '42_having_inject.php', 42],
        ['sqli', '31_into_outfile.php', '43_into_outfile.php', 43],
        ['sqli', '32_into_dumpfile.php', '44_into_dumpfile.php', 44],
        ['sqli', '33_benchmark.php', '45_benchmark.php', 45],
        ['sqli', '34_sleep.php', '46_sleep.php', 46],
        ['sqli', '42_space_bypass.php', '47_space_bypass.php', 47],
        ['sqli', '43_comment_bypass.php', '48_comment_bypass.php', 48],
        ['sqli', '46_quote_bypass.php', '49_quote_bypass.php', 49],
        ['sqli', '47_hex_bypass.php', '50_hex_bypass.php', 50],
        ['sqli', '48_wide_byte.php', '51_wide_byte.php', 51],
        ['sqli', '49_multibyte.php', '52_multibyte.php', 52],
        ['sqli', '50_dns_exfil.php', '53_dns_exfil.php', 53],
        ['ssrf', '06_ipv6.php', '02_ipv6.php', 2],
        ['ssrf', '07_urlparse.php', '03_urlparse.php', 3],
        ['ssrf', '02_protocol.php', '04_protocol.php', 4],
        ['ssrf', '03_bypass.php', '05_bypass.php', 5],
        ['ssrf', '04_redirect.php', '06_redirect.php', 6],
        ['ssrf', '05_dns.php', '07_dns.php', 7],
        ['ssti', '04_jinja.php', '02_jinja.php', 2],
        ['ssti', '05_smarty.php', '03_smarty.php', 3],
        ['ssti', '06_twig.php', '04_twig.php', 4],
        ['ssti', '07_error.php', '05_error.php', 5],
        ['ssti', '02_filter.php', '06_filter.php', 6],
        ['ssti', '03_sandbox.php', '07_sandbox.php', 7],
        ['traversal', '06_absolute.php', '02_absolute.php', 2],
        ['traversal', '07_glob.php', '03_glob.php', 3],
        ['traversal', '02_filter.php', '04_filter.php', 4],
        ['traversal', '04_double.php', '05_double.php', 5],
        ['traversal', '05_encode.php', '06_encode.php', 6],
        ['traversal', '03_null.php', '07_null.php', 7],
        ['unserialize', '06_tostring.php', '03_tostring.php', 3],
        ['unserialize', '07_call.php', '04_call.php', 4],
        ['unserialize', '08_destruct.php', '05_destruct.php', 5],
        ['unserialize', '03_pop.php', '06_pop.php', 6],
        ['unserialize', '04_phar.php', '07_phar.php', 7],
        ['unserialize', '05_wakeup.php', '08_wakeup.php', 8],
        ['upload', '09_size.php', '06_size.php', 6],
        ['upload', '10_chinese.php', '07_chinese.php', 7],
        ['upload', '11_imagephp.php', '08_imagephp.php', 8],
        ['upload', '06_race.php', '09_race.php', 9],
        ['upload', '07_htaccess.php', '10_htaccess.php', 10],
        ['upload', '08_move.php', '11_move.php', 11],
        ['xss', '06_event.php', '05_event.php', 5],
        ['xss', '07_svg.php', '06_svg.php', 6],
        ['xss', '08_markdown.php', '07_markdown.php', 7],
        ['xss', '09_base64.php', '08_base64.php', 8],
        ['xss', '10_jsonp.php', '09_jsonp.php', 9],
        ['xss', '12_encode.php', '10_encode.php', 10],
        ['xss', '05_csp.php', '11_csp.php', 11],
        ['xss', '11_mutation.php', '12_mutation.php', 12],
        ['xxe', '03_dos.php', '02_dos.php', 2],
        ['xxe', '04_svg.php', '03_svg.php', 3],
        ['xxe', '05_cdata.php', '04_cdata.php', 4],
        ['xxe', '06_xpath.php', '05_xpath.php', 5],
        ['xxe', '07_json.php', '06_json.php', 6],
        ['xxe', '08_soap.php', '07_soap.php', 7],
        ['xxe', '02_blind.php', '08_blind.php', 8],
    ];

    $tmpPrefix = '__ren008__';

    $restore = $db->prepare('UPDATE challenges SET file = ? WHERE category = ? AND file = ?');
    $stage = $db->prepare('UPDATE challenges SET file = ? WHERE category = ? AND file = ?');
    $commit = $db->prepare('UPDATE challenges SET file = ?, sort_order = ? WHERE category = ? AND file = ?');
    $flagOf = $db->prepare('SELECT flag FROM challenges WHERE category = ? AND file = ?');
    $dupRemove = $db->prepare('DELETE FROM challenges WHERE category = ? AND file = ? AND flag = ?');

    // 阶段 0：全新数据库中 init.sql 已含新文件名行，迁移 001 又按旧文件名插入过
    // 一遍 basics 题目；删除与保留行 Flag 相同的旧名重复行，避免后续撞唯一键。
    foreach ($renames as [$category, $oldFile, $newFile, $sortOrder]) {
        $flagOf->execute([$category, $newFile]);
        $keepFlag = $flagOf->fetchColumn();
        if ($keepFlag !== false) {
            $dupRemove->execute([$category, $oldFile, $keepFlag]);
        }
    }

    // 阶段 1：上次执行若在两段改名之间中断，先把残留临时名归位回旧名。
    // 此时旧名必然空置（占用者正是临时名行本身），归位不会触发唯一键冲突。
    foreach ($renames as [$category, $oldFile, $newFile, $sortOrder]) {
        $restore->execute([$oldFile, $category, $tmpPrefix . $oldFile]);
    }

    // 阶段 2：全部旧名先腾到临时名，保证阶段 3 落位时目标名一定空置。
    foreach ($renames as [$category, $oldFile, $newFile, $sortOrder]) {
        $stage->execute([$tmpPrefix . $oldFile, $category, $oldFile]);
    }

    // 阶段 3：落位到新文件名并写入与文件序号一致的 sort_order。
    foreach ($renames as [$category, $oldFile, $newFile, $sortOrder]) {
        $commit->execute([$newFile, $sortOrder, $category, $tmpPrefix . $oldFile]);
    }
};
