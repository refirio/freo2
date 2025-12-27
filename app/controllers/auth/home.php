<?php

// 権限を確認
if ($GLOBALS['authority']['power'] >= 1) {
    // リダイレクト
    redirect('/admin/');
}

// ユーザーを取得
$users = model('select_users', [
    'where' => [
        'id = :id AND enabled = 1',
        [
            'id' => $_SESSION['auth']['user']['id'],
        ],
    ],
]);

// 権限を取得
$authorities = model('select_authorities', [
    'where' => [
        'id = :id',
        [
            'id' => $users[0]['authority_id'],
        ],
    ],
]);
$GLOBALS['authority'] = $authorities[0];

// タイトル
$_view['title'] = 'ホーム';
