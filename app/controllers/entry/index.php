<?php

import('app/services/entry.php');

// エントリーの絞り込み
$filters = model('filter_entries', $_GET, [
    'associate' => true,
]);

// 検索用文字列を初期化
if (!isset($_GET['category_sets'])) {
    $_GET['category_sets'] = [];
}
if (!isset($_GET['archive'])) {
    $_GET['archive'] = null;
}

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;
}

// エントリーを取得
$_view['entries'] = service_entry_select_published('entry', [
    'where'    => $filters['where'],
    'order_by' => 'entries.datetime DESC, entries.id',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['setting']['number_limit_entry'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['setting']['number_limit_entry'],
        ],
    ],
]);

$entry_count = service_entry_select_published('entry', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => $filters['where'],
]);
$_view['entry_count'] = $entry_count[0]['count'];
$_view['entry_page']  = ceil($entry_count[0]['count'] / $GLOBALS['setting']['number_limit_entry']);

// カテゴリーを取得
$_view['categories'] = model('select_categories', [
    'where'    => 'types.code = ' .  db_escape('entry'),
    'order_by' => 'categories.sort, categories.id',
], [
    'associate' => true,
]);

// 月ごとのエントリー数を取得
$_view['entry_archives'] = service_entry_select_published('entry', [
    'select'   => 'DATE_FORMAT(entries.datetime, ' . db_escape('%Y-%m') . ') AS month, COUNT(DISTINCT entries.id) AS count',
    'group_by' => 'month',
    'order_by' => 'month DESC',
]);

// タイトル
$_view['title'] = 'エントリー';
