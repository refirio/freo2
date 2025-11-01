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
        'field' => model('normalize_fields', [
            'id'         => isset($_POST['id'])         ? $_POST['id']         : '',
            'type_id'    => isset($_POST['type_id'])    ? $_POST['type_id']    : '',
            'name'       => isset($_POST['name'])       ? $_POST['name']       : '',
            'kind'       => isset($_POST['kind'])       ? $_POST['kind']       : '',
            'validation' => isset($_POST['validation']) ? $_POST['validation'] : '',
            'text'       => isset($_POST['text'])       ? $_POST['text']       : '',
            'memo'       => isset($_POST['memo'])       ? $_POST['memo']       : '',
        ]),
    ];

    // 入力データを検証＆登録
    $warnings = model('validate_fields', $post['field']);
    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        if (empty($warnings)) {
            ok();
        } else {
            warning($warnings);
        }
    } else {
        if (empty($warnings)) {
            $_SESSION['post']['field'] = $post['field'];

            // フォワード
            forward('/admin/field_post');
        } else {
            $_view['field'] = $post['field'];

            $_view['warnings'] = $warnings;
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $_view['field'] = model('default_fields');
    } else {
        $fields = model('select_fields', [
            'where' => [
                'fields.id = :id AND types.code = ' . db_escape('entry'),
                [
                    'id' => $_GET['id'],
                ],
            ],
        ], [
            'associate' => true,
        ]);
        if (empty($fields)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['field'] = $fields[0];
        }
    }

    // 投稿セッションを初期化
    unset($_SESSION['post']);

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['field'] = localdate('Y-m-d H:i:s');
    }
}

// 型を取得
$_view['types'] = model('select_types', [
    'order_by' => 'sort, id',
]);

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = 'フィールド登録';
} else {
    $_view['title'] = 'フィールド編集';
}
