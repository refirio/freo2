<?php

import('libs/modules/environment.php');

// 操作ログを取得
$logs = model('select_logs', [
    'where' => [
        'logs.id = :id',
        [
            'id' => $_GET['id'],
        ],
    ],
], [
    'associate' => true,
]);
if (empty($logs)) {
    warning('表示データが見つかりません。');
} else {
    $_view['log'] = $logs[0];
}

// タイトル
$_view['title'] = 'ログ表示';
