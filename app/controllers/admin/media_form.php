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
    if (!empty($_POST['directory']) && (!preg_match('/^[\w\-\/]+$/', $_POST['directory']))) {
        error('ディレクトリの指定が不正です。');
    }

    // 登録データ
    $_SESSION['post']['media'] = [
        'directory' => rtrim($_POST['directory'], '/'),
    ];

    // フォワード
    forward('/admin/media_post');
}

// ディレクトリを取得
if (!isset($_GET['directory'])) {
    $_GET['directory'] = '';
}

// タイトル
if (empty($_GET['type'])) {
    $_view['title'] = 'メディア登録';
} else {
    $_view['title'] = 'メディア編集';
}
