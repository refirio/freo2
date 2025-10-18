<?php

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;

    $_SESSION['bulk']['entry'] = [];
}

// エントリーを取得
$_view['entries'] = model('select_entries', [
    'where'    => 'types.code = ' . db_escape('entry'),
    'order_by' => 'entries.datetime DESC, entries.id',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['config']['limit']['entry'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['config']['limit']['entry'],
        ],
    ],
], [
    'associate' => true,
]);

$entry_count = model('select_entries', [
    'select' => 'COUNT(DISTINCT entries.id) AS count',
    'where'  => 'types.code = ' . db_escape('entry'),
], [
    'associate' => true,
]);
$_view['entry_count'] = $entry_count[0]['count'];
$_view['entry_page']  = ceil($entry_count[0]['count'] / $GLOBALS['config']['limit']['entry']);

// タイトル
$_view['title'] = 'エントリー管理';
