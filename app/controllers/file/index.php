<?php

import('app/services/entry.php');
import('libs/modules/recaptcha.php');

// 表示対象を取得
if (!empty($_params[1]) && !empty($_params[2])) {
    $_GET['code'] = implode('/', array_slice($_params, 2));
} else {
    error('不正なアクセスです。');
}
if (!isset($_GET['code'])) {
    error('不正なアクセスです。');
}

// エントリーもしくはページを取得
$entries = service_entry_select_published($_params[1], [
    'where' => [
        'entries.code = :code',
        [
            'code' => $_GET['code'],
        ],
    ],
]);
if (empty($entries)) {
    warning('エントリーが見つかりません。');
} else {
    $_view['entry'] = $entries[0];
}

// タイトル
$_view['title'] = $_view['entry']['title'];
