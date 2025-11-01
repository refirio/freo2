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
        'attribute' => model('normalize_attributes', [
            'id'   => isset($_POST['id'])   ? $_POST['id']   : '',
            'name' => isset($_POST['name']) ? $_POST['name'] : '',
            'memo' => isset($_POST['memo']) ? $_POST['memo'] : '',
        ]),
    ];

    // 入力データを検証＆登録
    $warnings = model('validate_attributes', $post['attribute']);
    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        if (empty($warnings)) {
            ok();
        } else {
            warning($warnings);
        }
    } else {
        if (empty($warnings)) {
            $_SESSION['post']['attribute'] = $post['attribute'];

            // フォワード
            forward('/admin/attribute_post');
        } else {
            $_view['attribute'] = $post['attribute'];

            $_view['warnings'] = $warnings;
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $_view['attribute'] = model('default_attributes');
    } else {
        $attributes = model('select_attributes', [
            'where' => [
                'id = :id',
                [
                    'id' => $_GET['id'],
                ],
            ],
        ]);
        if (empty($attributes)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['attribute'] = $attributes[0];
        }
    }

    // 投稿セッションを初期化
    unset($_SESSION['post']);

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['attribute'] = localdate('Y-m-d H:i:s');
    }
}

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = '属性登録';
} else {
    $_view['title'] = '属性編集';
}
