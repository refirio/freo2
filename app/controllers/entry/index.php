<?php

import('app/services/entry.php');

if ($_REQUEST['_work'] !== 'feed') {
    // パスから絞り込み条件を取得（/entry/category/<コード>/、/entry/archive/<年月>/、/entry/page/<ページ>/）
    $list = service_entry_list_parse(array_slice($_params, 1));
    if ($list === null) {
        warning('ページが見つかりません。');
    }

    if ($list['category'] === null && $list['archive'] === null && $list['page'] === 1) {
        // 古いURL（クエリ文字列）は、パスで表せれば新しいURLへ転送
        if (isset($_GET['category_sets']) || isset($_GET['archive']) || isset($_GET['page'])) {
            $list_filter = service_entry_list_filter($_GET);
            if ($list_filter !== null) {
                redirect(service_entry_list_url($list_filter['category'], $list_filter['archive'], $list_filter['page']), 301);
            }
        }
    } else {
        if ($list['category'] !== null) {
            $categories = model('select_categories', [
                'where' => [
                    'categories.code = :code AND types.code = :type',
                    [
                        'code' => $list['category'],
                        'type' => 'entry',
                    ],
                ],
            ], [
                'associate' => true,
            ]);
            if (empty($categories)) {
                warning('ページが見つかりません。');
            }
            $_GET['category_sets'] = [$categories[0]['id']];
        }
        if ($list['archive'] !== null) {
            $_GET['archive'] = $list['archive'];
        }
        if ($list['page'] > 1) {
            $_GET['page'] = $list['page'];
        }
    }
}

// 絞り込み条件を作成
$filters = model('filter_entries', $_GET, [
    'associate' => true,
]);

// 絞り込み条件を引き継ぎ
$_view['entry_filter'] = $filters['pager'];

// 絞り込み条件を初期化
if (!isset($_GET['category_sets'])) {
    $_GET['category_sets'] = [];
}
if (!isset($_GET['archive'])) {
    $_GET['archive'] = null;
}

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = max(1, intval($_GET['page']));
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

// ページ送りのURLを作成（パスで表せない絞り込みは、クエリ文字列で引き継ぐ）
$list_filter = service_entry_list_filter(array_diff_key($_GET, ['page' => true]));

$_view['entry_pages'] = [];
for ($i = 1; $i <= $_view['entry_page']; $i++) {
    if ($list_filter === null) {
        $_view['entry_pages'][$i] = '/entry/?' . ($filters['pager'] ? str_replace('&amp;', '&', $filters['pager']) . '&' : '') . 'page=' . $i;
    } else {
        $_view['entry_pages'][$i] = service_entry_list_url($list_filter['category'], $list_filter['archive'], $i);
    }
}

// カテゴリーを取得
$_view['categories'] = model('select_categories', [
    'where'    => 'types.code = ' .  db_escape('entry'),
    'order_by' => 'categories.sort, categories.id',
], [
    'associate' => true,
]);
foreach ($_view['categories'] as $index => $category) {
    $_view['categories'][$index]['url'] = service_entry_list_url($category['code']);
}

// 月ごとのエントリー数を取得
$_view['entry_archives'] = service_entry_select_published('entry', [
    'select'   => 'DATE_FORMAT(entries.datetime, ' . db_escape('%Y-%m') . ') AS month, COUNT(DISTINCT entries.id) AS count',
    'group_by' => 'month',
    'order_by' => 'month DESC',
]);
foreach ($_view['entry_archives'] as $index => $entry_archive) {
    $_view['entry_archives'][$index]['url'] = service_entry_list_url(null, $entry_archive['month']);
}

// タイトル
$_view['title'] = 'エントリー';
