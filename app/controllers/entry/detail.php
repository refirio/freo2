<?php

import('app/services/entry.php');

// 表示対象を取得
if (isset($_params[2])) {
    $_GET['id'] = $_params[2];
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    error('不正なアクセスです。');
}

// エントリーを取得
$entries = service_entry_select_published('entry', [
    'where' => [
        'entries.id = :id',
        [
            'id' => $_GET['id'],
        ],
    ],
], [
    'associate' => true,
]);
if (empty($entries)) {
    warning('記事が見つかりません。');
} else {
    $_view['entry'] = $entries[0];
}

// タイトル
$_view['title'] = '記事';
