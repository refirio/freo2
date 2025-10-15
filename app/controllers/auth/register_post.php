<?php

import('app/services/user.php');
import('app/services/mail.php');
import('libs/modules/hash.php');

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

// トランザクションを開始
db_transaction();

// ユーザを登録
$resource = service_user_insert([
    'values' => [
        'username'      => $_SESSION['post']['user']['username'],
        'password'      => hash_crypt($_SESSION['post']['user']['password'], $password_salt . ':' . $GLOBALS['config']['hash_salt']),
        'password_salt' => $password_salt,
        'authority_id'  => $authority_id,
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

// 認証用URLを作成
$_view['url'] = $GLOBALS['config']['http_url'] . '/auth/register_verify?key=' . rawurlencode($users[0]['email']) . '&token=' . $users[0]['token'];

$to      = $users[0]['email'];
$subject = $GLOBALS['config']['mail_subjects']['register/send'];
$message = view('mail/register/send.php', true);
$headers = $GLOBALS['config']['mail_headers'];

// メールを送信
if (service_mail_send($to, $subject, $message, $headers) === false) {
    error('メールを送信できません。');
}

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);

// リダイレクト
redirect('/auth/register_complete');
