<?php

import('app/services/user.php');

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

// ログアウト
if (isset($_SESSION['auth']['user']['id'])) {
    service_user_logout($_COOKIE['auth']['session'], $_SESSION['auth']['user']['id']);
}

unset($_SESSION['auth']['user']);

// リダイレクト
redirect('/auth/leave_complete');
