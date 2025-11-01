<?php

import('app/services/user.php');
import('libs/modules/hash.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/auth/password');
}

// トランザクションを開始
db_transaction();

// パスワードのソルトを作成
$password_salt = hash_salt();

// ユーザーを編集
$resource = service_user_update([
    'set'   => [
        'password'      => hash_crypt($_SESSION['post']['user']['password'], $password_salt . ':' . $GLOBALS['config']['hash_salt']),
        'password_salt' => $password_salt,
        'token'         => null,
        'token_code'    => null,
        'token_expire'  => null,
    ],
    'where' => [
        'email = :email',
        [
            'email' => $_SESSION['post']['user']['key'],
        ],
    ],
], [
    'id'     => intval($_SESSION['post']['user']['id']),
    'update' => $_SESSION['update']['user'],
]);
if (!$resource) {
    error('データを編集できません。');
}

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);
unset($_SESSION['update']);

// リダイレクト
redirect('/auth/password_complete');
