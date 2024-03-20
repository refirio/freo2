<?php

import('app/services/entry.php');

if ($GLOBALS['setting']['page_home_code']) {
    // ページを取得
    $pages = service_entry_select_published('page', [
        'where' => [
            'entries.code = :code',
            [
                'code' => $GLOBALS['setting']['page_home_code'],
            ],
        ],
    ]);
    if (!empty($pages)) {
        $_view['page'] = $pages[0];
    }
}

// エントリーを取得
$_view['entries'] = service_entry_select_published('entry', [
    'order_by' => 'entries.datetime DESC, entries.id',
    'limit'    => $GLOBALS['config']['limits']['entry'],
]);
