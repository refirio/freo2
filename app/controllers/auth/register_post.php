<?php

import('app/services/user.php');
import('app/services/mail.php');
import('libs/modules/hash.php');

// 権限を確認
if (isset($GLOBALS['authority'])) {
    // リダイレクト
    redirect('/auth/');
}

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('訪問者によるユーザー新規登録は許可されていません。');
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

// ユーザーを登録
$resource = service_user_insert([
    'values' => [
        'username'      => $_SESSION['post']['user']['username'],
        'password'      => hash_crypt($_SESSION['post']['user']['password'], $password_salt . ':' . $GLOBALS['config']['hash_salt']),
        'password_salt' => $password_salt,
        'authority_id'  => $authority_id,
        'enabled'       => $enabled,
        'name'          => $_SESSION['post']['user']['name'],
        'email'         => $_SESSION['post']['user']['email'],
        'url'           => $_SESSION['post']['user']['url'],
        'text'          => $_SESSION['post']['user']['text'],
        'token'         => rand_string(),
        'token_expire'  => localdate('Y-m-d H:i:s', time() + 60 * 60 * 24),

    ],
]);
if (!$resource) {
    error('データを登録できません。');
}

// トランザクションを終了
db_commit();

// ユーザー情報を取得
$users = model('select_users', [
    'select' => 'email, token',
    'where'  => [
        'email = :email',
        [
            'email' => $_SESSION['post']['user']['email'],
        ],
    ],
]);

// ユーザー登録完了を通知
$to      = $users[0]['email'];
$subject = $GLOBALS['setting']['mail_register_subject'];
$message = view('mail/register/send.php', true);
$headers = $GLOBALS['config']['mail_headers'];

// メールを送信
if (service_mail_send($to, $subject, $message, $headers) === false) {
    error('メールを送信できません。');
}

// 投稿セッションを初期化
unset($_SESSION['post']);

// リダイレクト
redirect('/auth/register_complete');
