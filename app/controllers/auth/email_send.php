<?php

import('app/services/mail.php');

// メールアドレス認証用URLを通知
$users = model('select_users', [
    'select' => 'email, token',
    'where'  => [
        'id = :id',
        [
            'id' => $_SESSION['auth']['user']['id'],
        ],
    ],
]);

// 認証用URLを作成
$_view['url'] = $GLOBALS['config']['http_url'] . '/auth/email_verify?key=' . rawurlencode($users[0]['email']) . '&token=' . $users[0]['token'];

$to      = $users[0]['email'];
$subject = $GLOBALS['setting']['mail_verify_subject'];
$message = view('mail/email/verify.php', true);
$headers = $GLOBALS['config']['mail_headers'];

// メールを送信
if (service_mail_send($to, $subject, $message, $headers) === false) {
    error('メールを送信できません。');
}

// リダイレクト
redirect('/auth/home?verify=send');
