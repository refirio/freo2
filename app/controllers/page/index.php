<?php

import('app/services/page.php');

// 表示対象を取得
if (isset($_params[1])) {
    $_GET['code'] = implode('/', array_slice($_params, 1));
}
if (!isset($_GET['code'])) {
    error('不正なアクセスです。');
}

// ページを取得
$pages = service_page_select_published([
    'where' => [
        'pages.code = :code',
        [
            'code' => $_GET['code'],
        ],
    ],
], [
    'associate' => true,
]);
if (empty($pages)) {
    warning('ページが見つかりません。');
} else {
    $_view['page'] = $pages[0];
}

// ページを取得
$_view['pages'] = service_page_select_published([]);

// タイトル
$_view['title'] = 'ページ';
