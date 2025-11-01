<?php

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('訪問者によるユーザー新規登録は許可されていません。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/auth/register');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $warnings = [];

    // ワンタイムトークン
    if (!token('check')) {
        $warnings[] = '不正な操作が検出されました。送信内容を確認して再度実行してください。';
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        $warnings[] = '不正なアクセスです。';
    }

    if (empty($warnings)) {
        // フォワード
        forward('/auth/register_post');
    } else {
        $_view['warnings'] = $warnings;
    }
}

$_view['user'] = $_SESSION['post']['user'];

// タイトル
$_view['title'] = 'ユーザー登録確認';
