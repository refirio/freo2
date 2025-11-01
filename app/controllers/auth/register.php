<?php

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('訪問者によるユーザ新規登録は許可されていません。');
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

    // 入力データを整理
    $post = [
        'user' => model('normalize_users', [
            'id'               => null,
            'username'         => isset($_POST['username'])         ? $_POST['username']         : '',
            'password'         => isset($_POST['password'])         ? $_POST['password']         : '',
            'password_confirm' => isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '',
            'name'             => isset($_POST['name'])             ? $_POST['name']             : '',
            'email'            => isset($_POST['email'])            ? $_POST['email']            : '',
            'url'              => isset($_POST['url'])              ? $_POST['url']              : '',
            'text'             => isset($_POST['text'])             ? $_POST['text']             : '',
        ]),
    ];

    // 入力データを検証＆登録
    $warnings = model('validate_users', $post['user']);
    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        if (empty($warnings)) {
            ok();
        } else {
            warning($warnings);
        }
    } else {
        if (empty($warnings)) {
            $_SESSION['post']['user'] = $post['user'];

            // リダイレクト
            redirect('/auth/register_preview');
        } else {
            $_view['user'] = $post['user'];

            $_view['warnings'] = $warnings;
        }
    }
} elseif (isset($_GET['referer']) && $_GET['referer'] === 'preview') {
    // 入力データを復元
    $_view['user'] = $_SESSION['post']['user'];
} else {
    // 初期データを取得
    $_view['user'] = model('default_users');

    // 投稿セッションを初期化
    unset($_SESSION['post']);
}

// タイトル
$_view['title'] = 'ユーザ登録';
