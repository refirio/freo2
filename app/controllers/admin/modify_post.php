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
    redirect('/admin/modify');
}

// パスワードのソルトを作成
$password_salt = hash_salt();

// トランザクションを開始
db_transaction();

// ユーザを編集
$sets = [
    'username' => $_SESSION['post']['user']['username'],
    'name'     => $_SESSION['post']['user']['name'],
    'email'    => $_SESSION['post']['user']['email'],
];
if (!empty($_SESSION['post']['user']['password'])) {
    $sets['password']      = hash_crypt($_SESSION['post']['user']['password'], $password_salt . ':' . $GLOBALS['config']['hash_salt']);
    $sets['password_salt'] = $password_salt;
}
$resource = service_user_update([
    'set'   => $sets,
    'where' => [
        'id = :id',
        [
            'id' => $_SESSION['auth']['user']['id'],
        ],
    ],
], [
    'id'     => intval($_SESSION['auth']['user']['id']),
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
redirect('/admin/modify_complete');
