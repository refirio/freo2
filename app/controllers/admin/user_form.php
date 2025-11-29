<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if ((empty($_POST['view']) || $_POST['view'] !== 'preview') && !token('check')) {
        error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        error('不正なアクセスです。');
    }

    // 入力データを整理
    $post = [
        'user' => model('normalize_users', [
            'id'               => isset($_POST['id'])               ? $_POST['id']               : '',
            'username'         => isset($_POST['username'])         ? $_POST['username']         : '',
            'password'         => isset($_POST['password'])         ? $_POST['password']         : '',
            'password_confirm' => isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '',
            'authority_id'     => isset($_POST['authority_id'])     ? $_POST['authority_id']     : '',
            'attribute_begin'  => isset($_POST['attribute_begin'])  ? $_POST['attribute_begin']  : '',
            'attribute_end'    => isset($_POST['attribute_end'])    ? $_POST['attribute_end']    : '',
            'enabled'          => isset($_POST['enabled'])          ? $_POST['enabled']          : '',
            'name'             => isset($_POST['name'])             ? $_POST['name']             : '',
            'email'            => isset($_POST['email'])            ? $_POST['email']            : '',
            'url'              => isset($_POST['url'])              ? $_POST['url']              : '',
            'text'             => isset($_POST['text'])             ? $_POST['text']             : '',
            'memo'             => isset($_POST['memo'])             ? $_POST['memo']             : '',
            'attribute_sets'   => isset($_POST['attribute_sets'])   ? $_POST['attribute_sets']   : [],
        ]),
    ];

    if (isset($_POST['view']) && $_POST['view'] === 'preview') {
        // プレビュー
        $_view['user'] = $post['user'];
    } else {
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
                forward('/admin/user_post');
            } else {
                $_view['user'] = $post['user'];

                $_view['warnings'] = $warnings;
            }
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $_view['user'] = model('default_users');
    } else {
        $users = model('select_users', [
            'where' => [
                'users.id = :id',
                [
                    'id' => $_GET['id'],
                ],
            ],
        ], [
            'associate' => true,
        ]);
        if (empty($users)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['user'] = $users[0];
        }
    }

    // 投稿セッションを初期化
    unset($_SESSION['post']);

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['user'] = localdate('Y-m-d H:i:s');
    }
}

// ユーザーの表示用データ作成
$_view['user'] = model('view_users', $_view['user']);

// 権限を取得
$_view['authorities'] = model('select_authorities', [
    'order_by' => 'power DESC, id',
]);

// 属性を取得
$_view['attributes'] = model('select_attributes', [
    'order_by' => 'sort, id',
]);

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = 'ユーザー登録';
} else {
    $_view['title'] = 'ユーザー編集';
}
