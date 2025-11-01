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

    // 入力データを整理
    $post = [
        'menu' => model('normalize_menus', [
            'id'      => isset($_POST['id'])      ? $_POST['id']      : '',
            'enabled' => isset($_POST['enabled']) ? $_POST['enabled'] : '',
            'title'   => isset($_POST['title'])   ? $_POST['title']   : '',
            'url'     => isset($_POST['url'])     ? $_POST['url']     : '',
            'memo'    => isset($_POST['memo'])    ? $_POST['memo']    : '',
        ]),
    ];

    // 入力データを検証＆登録
    $warnings = model('validate_menus', $post['menu']);
    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        if (empty($warnings)) {
            ok();
        } else {
            warning($warnings);
        }
    } else {
        if (empty($warnings)) {
            $_SESSION['post']['menu'] = $post['menu'];

            // フォワード
            forward('/admin/menu_post');
        } else {
            $_view['menu'] = $post['menu'];

            $_view['warnings'] = $warnings;
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $_view['menu'] = model('default_menus');
    } else {
        $menus = model('select_menus', [
            'where' => [
                'id = :id',
                [
                    'id' => $_GET['id'],
                ],
            ],
        ]);
        if (empty($menus)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['menu'] = $menus[0];
        }
    }

    // 投稿セッションを初期化
    unset($_SESSION['post']);

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['menu'] = localdate('Y-m-d H:i:s');
    }
}

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = 'メニュー登録';
} else {
    $_view['title'] = 'メニュー編集';
}
