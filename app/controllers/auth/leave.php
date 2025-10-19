<?php

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('自身のユーザ削除は許可されていません。');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        error('不正なアクセスです。');
    }

    // リダイレクト
    redirect('/auth/leave_confirm');
}

// タイトル
$_view['title'] = 'ユーザ情報削除';
