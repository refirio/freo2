<?php

import('libs/modules/environment.php');

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;
}

// 操作ログを取得
$_view['logs'] = model('select_logs', [
    'order_by' => 'logs.id DESC',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['config']['limits']['log'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['config']['limits']['log'],
        ],
    ],
], [
    'associate' => true,
]);

$log_count = model('select_logs', [
    'select' => 'COUNT(*) AS count',
], [
    'associate' => true,
]);
$_view['log_count'] = $log_count[0]['count'];
$_view['log_page']  = ceil($log_count[0]['count'] / $GLOBALS['config']['limits']['log']);

// タイトル
$_view['title'] = 'ログ確認';
