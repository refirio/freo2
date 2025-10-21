<?php

import('app/services/user.php');
import('app/services/mail.php');

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('自身のユーザ削除は許可されていません。');
}

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// トランザクションを開始
db_transaction();

// ユーザ情報を取得
$users = model('select_users', [
    'select' => 'email, token',
    'where'  => [
        'id = :id',
        [
            'id' => $_SESSION['auth']['user']['id'],
        ],
    ],
]);

// ユーザを削除
$resource = service_user_delete([
    'where' => [
        'id = :id',
        [
            'id' => $_SESSION['auth']['user']['id'],
        ],
    ],
], [
    'associate' => true,
]);
if (!$resource) {
    error('データを削除できません。');
}

// トランザクションを終了
db_commit();

// ユーザ削除完了を通知
$to      = $users[0]['email'];
$subject = $GLOBALS['setting']['mail_leave_subject'];
$message = view('mail/leave/send.php', true);
$headers = [
    'From' => $GLOBALS['setting']['mail_from'],
];

// メールを送信
if (service_mail_send($to, $subject, $message, $headers) === false) {
    error('メールを送信できません。');
}

// ログアウト
if (isset($_SESSION['auth']['user']['id'])) {
    service_user_logout($_COOKIE['auth']['session'], $_SESSION['auth']['user']['id']);
}

unset($_SESSION['auth']['user']);

// リダイレクト
redirect('/auth/leave_complete');
