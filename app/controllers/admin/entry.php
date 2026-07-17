<?php

// 絞り込み条件を作成
$filters = model('filter_entries', $_GET, [
    'associate' => true,
]);
if ($filters['where'] !== '') {
    $filters['where'] .= ' AND ';
}
$filters['where'] .= 'types.code = ' . db_escape('entry');

// 絞り込み条件を引き継ぎ
$_view['entry_filter'] = $filters['pager'];

// 絞り込み条件を初期化
if (!isset($_GET['keyword'])) {
    $_GET['keyword'] = null;
}
if (!isset($_GET['public'])) {
    $_GET['public'] = null;
}

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;

    $_SESSION['bulk']['entry'] = [];
}

// エントリーを取得
$_view['entries'] = model('select_entries', [
    'where'    => $filters['where'],
    'order_by' => 'entries.datetime DESC, entries.id',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['setting']['number_limit_admin_entry'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['setting']['number_limit_admin_entry'],
        ],
    ],
], [
    'associate' => true,
]);

$entry_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => $filters['where'],
], [
    'associate' => true,
]);
$_view['entry_count'] = $entry_count[0]['count'];
$_view['entry_page']  = ceil($entry_count[0]['count'] / $GLOBALS['setting']['number_limit_admin_entry']);

// カテゴリーを取得
$_view['categories'] = model('select_categories', [
    'where'    => 'types.code = ' .  db_escape('entry'),
    'order_by' => 'categories.sort, categories.id',
], [
    'associate' => true,
]);

// タイトル
$_view['title'] = 'エントリー管理';
