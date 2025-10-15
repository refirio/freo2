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
    redirect('/admin/user_form');
}

// パスワードのソルトを作成
$password_salt = hash_salt();

// トランザクションを開始
db_transaction();

if (empty($_SESSION['post']['user']['id'])) {
    // ユーザを登録
    $resource = service_user_insert([
        'values' => [
            'username'       => $_SESSION['post']['user']['username'],
            'password'       => hash_crypt($_SESSION['post']['user']['password'], $password_salt . ':' . $GLOBALS['config']['hash_salt']),
            'password_salt'  => $password_salt,
            'authority_id'   => $_SESSION['post']['user']['authority_id'],
            'enabled'        => $_SESSION['post']['user']['enabled'],
            'name'           => $_SESSION['post']['user']['name'],
            'email'          => $_SESSION['post']['user']['email'],
            'email_verified' => 1,
        ],
    ], [
        'attribute_sets' => $_SESSION['post']['user']['attribute_sets'],
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} else {
    // ユーザを編集
    $sets = [
        'username'     => $_SESSION['post']['user']['username'],
        'authority_id' => $_SESSION['post']['user']['authority_id'],
        'enabled'      => $_SESSION['post']['user']['enabled'],
        'name'         => $_SESSION['post']['user']['name'],
        'email'        => $_SESSION['post']['user']['email'],
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
                'id' => $_SESSION['post']['user']['id'],
            ],
        ],
    ], [
        'id'             => intval($_SESSION['post']['user']['id']),
        'update'         => $_SESSION['update']['user'],
        'attribute_sets' => $_SESSION['post']['user']['attribute_sets'],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }
}

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);
unset($_SESSION['update']);

// リダイレクト
redirect('/admin/user?ok=post');
