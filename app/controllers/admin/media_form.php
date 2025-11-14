<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        error('不正なアクセスです。');
    }

    // 入力データを検証
    if (!preg_match('/^[\w\-\/]+$/', $_POST['directory']) || preg_match('/\/\//', $_POST['directory'])) {
        error('不正なアクセスです。');
    } elseif (!preg_match('/\/$/', $_POST['directory'])) {
        $_POST['directory'] .= '/';
    }

    // 登録データ
    $_SESSION['post']['media'] = [
        'directory' => $_POST['directory'],
    ];

    // フォワード
    forward('/admin/media_post');
}

// ディレクトリを取得
if (!isset($_GET['directory'])) {
    $_GET['directory'] = '';
}

// タイトル
$_view['title'] = 'メディア登録';
