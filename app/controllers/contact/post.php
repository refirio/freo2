<?php

import('app/services/contact.php');
import('app/services/mail.php');

//ワンタイムトークン
if (!token('check')) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/');
}

// トランザクションを開始
db_transaction();

// お問い合わせを登録
$resource = service_contact_insert([
    'values' => [
        'name'    => $_SESSION['post']['contact']['name'],
        'email'   => $_SESSION['post']['contact']['email'],
        'message' => $_SESSION['post']['contact']['message'],
    ],
]);
if (!$resource) {
    error('データを登録できません。');
}

// トランザクションを終了
db_commit();

// メールを送信（管理者用）
$to      = $GLOBALS['setting']['mail_to'];
$subject = $GLOBALS['setting']['mail_contact_subject_admin'];
$message = view('mail/contact/send_admin.php', true);
$headers = [
    'From' => $GLOBALS['setting']['mail_from'],
];

if (service_mail_send($to, $subject, $message, $headers) === false) {
    error('メールを送信できません。');
}

// メールを送信（自動返信）
$to      = $_SESSION['post']['contact']['email'];
$subject = $GLOBALS['setting']['mail_contact_subject'];
$message = view('mail/contact/send_user.php', true);
$headers = [
    'From' => $GLOBALS['setting']['mail_from'],
];

if (service_mail_send($to, $subject, $message, $headers) === false) {
    error('メールを送信できません。');
}

// 投稿セッションを初期化
unset($_SESSION['post']);

// リダイレクト
redirect('/contact/complete');
