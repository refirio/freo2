<?php

// 権限を確認
if (isset($GLOBALS['authority'])) {
    // リダイレクト
    redirect('/auth/');
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
            'key'              => isset($_POST['key'])              ? $_POST['key']              : '',
            'token_code'       => isset($_POST['token_code'])       ? $_POST['token_code']       : '',
            'password'         => isset($_POST['password'])         ? $_POST['password']         : '',
            'password_confirm' => isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '',
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

            // フォワード
            forward('/auth/password_post');
        } else {
            $_view['user'] = $post['user'];
            $_view['key']  = $post['user']['key'];

            $_view['warnings'] = $warnings;
        }
    }
} else {
    // パスワード再発行用URLを検証
    $users = model('select_users', [
        'select' => 'token_expire',
        'where'  => [
            'email = :email AND token = :token',
            [
                'email' => $_GET['key'],
                'token' => $_GET['token'],
            ],
        ],
    ]);
    if (empty($users)) {
        error('不正なアクセスです。');
    }

    if (localdate(null, $users[0]['token_expire']) < localdate()) {
        error('URLの有効期限が終了しています。');
    }

    $_view['user'] = [
        'password'   => '',
        'token_code' => '',
    ];

    $_view['key'] = $_GET['key'];

    // 投稿セッションを初期化
    unset($_SESSION['post']);

    // 編集開始日時を記録
    $_SESSION['update']['user'] = localdate('Y-m-d H:i:s');
}

// タイトル
$_view['title'] = 'パスワード再登録';
