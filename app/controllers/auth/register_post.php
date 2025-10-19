<?php

import('app/services/user.php');
import('libs/modules/hash.php');

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('訪問者によるユーザ新規登録は許可されていません。');
}

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/register');
}

// パスワードのソルトを作成
$password_salt = hash_salt();

// 権限を決定
$authorities = model('select_authorities', [
    'where' => [
        'power = :power',
        [
            'power' => 0,
        ],
    ],
]);
$authority_id = $authorities[0]['id'];

// 有効
$enabled = $GLOBALS['setting']['user_use_approve'] ? 0 : 1;

// トランザクションを開始
db_transaction();

// ユーザを登録
$resource = service_user_insert([
    'values' => [
        'username'      => $_SESSION['post']['user']['username'],
        'password'      => hash_crypt($_SESSION['post']['user']['password'], $password_salt . ':' . $GLOBALS['config']['hash_salt']),
        'password_salt' => $password_salt,
        'authority_id'  => $authority_id,
        'enabled'       => $enabled,
        'name'          => $_SESSION['post']['user']['name'],
        'email'         => $_SESSION['post']['user']['email'],
        'token'         => rand_string(),
        'token_expire'  => localdate('Y-m-d H:i:s', time() + 60 * 60 * 24),

    ],
]);
if (!$resource) {
    error('データを登録できません。');
}

// メールアドレス認証用URLを通知
$users = model('select_users', [
    'select' => 'email, token',
    'where'  => [
        'email = :email',
        [
            'email' => $_SESSION['post']['user']['email'],
        ],
    ],
]);

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);

// リダイレクト
redirect('/auth/register_complete');
