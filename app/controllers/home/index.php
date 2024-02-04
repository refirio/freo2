<?php

import('app/services/entry.php');

if ($GLOBALS['setting']['page_home_code']) {
    // ページを取得
    $pages = service_page_select_published([
        'where' => [
            'pages.code = :code',
            [
                'code' => $GLOBALS['setting']['page_home_code'],
            ],
        ],
    ], [
        'associate' => true,
    ]);
    if (!empty($pages)) {
        $_view['page'] = $pages[0];
    }
}

// 記事を取得
$_view['entries'] = service_entry_select_published([
    'order_by' => 'entries.datetime DESC, entries.id',
    'limit'    => $GLOBALS['config']['limits']['entry'],
]);
