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
        'category' => model('normalize_categories', [
            'id'      => isset($_POST['id'])      ? $_POST['id']      : '',
            'type_id' => isset($_POST['type_id']) ? $_POST['type_id'] : '',
            'code'    => isset($_POST['code'])    ? $_POST['code']    : '',
            'name'    => isset($_POST['name'])    ? $_POST['name']    : '',
            'memo'    => isset($_POST['memo'])    ? $_POST['memo']    : '',
        ]),
    ];

    // 入力データを検証＆登録
    $warnings = model('validate_categories', $post['category']);
    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        if (empty($warnings)) {
            ok();
        } else {
            warning($warnings);
        }
    } else {
        if (empty($warnings)) {
            $_SESSION['post']['category'] = $post['category'];

            // フォワード
            forward('/admin/category_post');
        } else {
            $_view['category'] = $post['category'];

            $_view['warnings'] = $warnings;
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $_view['category'] = model('default_categories');
    } else {
        $categories = model('select_categories', [
            'where' => [
                'categories.id = :id AND types.code = ' . db_escape('entry'),
                [
                    'id' => $_GET['id'],
                ],
            ],
        ], [
            'associate' => true,
        ]);
        if (empty($categories)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['category'] = $categories[0];
        }
    }

    // 投稿セッションを初期化
    unset($_SESSION['post']);

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['category'] = localdate('Y-m-d H:i:s');
    }
}

// 型を取得
$types = model('select_types', [
    'where' => 'code = ' . db_escape('entry'),
]);
$_view['type'] = $types[0];

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = 'カテゴリー登録';
} else {
    $_view['title'] = 'カテゴリー編集';
}
