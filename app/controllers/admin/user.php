<?php

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;
}

// ユーザを取得
$_view['users'] = model('select_users', [
    'order_by' => 'users.id',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['config']['limits']['user'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['config']['limits']['user'],
        ],
    ],
], [
    'associate' => true,
]);

$user_count = model('select_users', [
    'select' => 'COUNT(*) AS count',
], [
    'associate' => true,
]);
$_view['user_count'] = $user_count[0]['count'];
$_view['user_page']  = ceil($user_count[0]['count'] / $GLOBALS['config']['limits']['user']);

// タイトル
$_view['title'] = 'ユーザ管理';
