<?php

import('app/services/user.php');

// ワンタイムトークン
if (!token('check')) {
    error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
}

// アクセス元
if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
    error('不正なアクセスです。');
}

if (!empty($_POST['id'])) {
    // トランザクションを開始
    db_transaction();

    // ユーザーを削除
    $resource = service_user_delete([
        'where' => [
            'id = :id',
            [
                'id' => $_POST['id'],
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

    // リダイレクト
    redirect('/admin/user?ok=delete');
} else {
    // リダイレクト
    redirect('/admin/user?warning=delete');
}
