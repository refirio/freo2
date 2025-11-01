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
        'widget' => model('normalize_widgets', [
            'id'    => isset($_POST['id'])    ? $_POST['id']    : '',
            'code'  => isset($_POST['code'])  ? $_POST['code']  : '',
            'title' => isset($_POST['title']) ? $_POST['title'] : '',
            'text'  => isset($_POST['text'])  ? $_POST['text']  : '',
            'memo'  => isset($_POST['memo'])  ? $_POST['memo']  : '',
        ]),
    ];

    // 入力データを検証＆登録
    $warnings = model('validate_widgets', $post['widget']);
    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        if (empty($warnings)) {
            ok();
        } else {
            warning($warnings);
        }
    } else {
        if (empty($warnings)) {
            $_SESSION['post']['widget'] = $post['widget'];

            // フォワード
            forward('/admin/widget_post');
        } else {
            $_view['widget'] = $post['widget'];

            $_view['warnings'] = $warnings;
        }
    }
} else {
    // 初期データを取得
    $widgets = model('select_widgets', [
        'where' => [
            'id = :id',
            [
                'id' => $_GET['id'],
            ],
        ],
    ]);
    if (empty($widgets)) {
        warning('編集データが見つかりません。');
    } else {
        $_view['widget'] = $widgets[0];
    }

    // 投稿セッションを初期化
    unset($_SESSION['post']);

    // 編集開始日時を記録
    $_SESSION['update']['widget'] = localdate('Y-m-d H:i:s');
}

// タイトル
$_view['title'] = 'ウィジェット編集';
