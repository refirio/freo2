<?php

import('app/services/user.php');

// トランザクションを開始
db_transaction();

// ユーザーを編集
$resource = service_user_update([
    'set'   => [
        'email_verified'  => 1,
        'token'           => null,
        'token_expire'    => null,
    ],
    'where' => [
        'email = :email AND token = :token AND token_expire > :token_expire',
        [
            'email'        => $_GET['key'],
            'token'        => $_GET['token'],
            'token_expire' => localdate('Y-m-d H:i:s'),
        ],
    ],
]);
if (!$resource) {
    error('データを編集できません。');
}

// トランザクションを終了
db_commit();

// リダイレクト
redirect('/auth/email_verified');
