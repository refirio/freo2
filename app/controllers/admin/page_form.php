<?php

import('libs/plugins/file.php');

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
        'page' => model('normalize_pages', [
            'id'            => isset($_POST['id'])            ? $_POST['id']            : '',
            'public'        => isset($_POST['public'])        ? $_POST['public']        : '',
            'public_begin'  => isset($_POST['public_begin'])  ? $_POST['public_begin']  : '',
            'public_end'    => isset($_POST['public_end'])    ? $_POST['public_end']    : '',
            'datetime'      => isset($_POST['datetime'])      ? $_POST['datetime']      : '',
            'title'         => isset($_POST['title'])         ? $_POST['title']         : '',
            'code'          => isset($_POST['code'])          ? $_POST['code']          : '',
            'text'          => isset($_POST['text'])          ? $_POST['text']          : '',
            'field_sets'    => isset($_POST['field_sets'])    ? $_POST['field_sets']    : [],
            'category_sets' => isset($_POST['category_sets']) ? $_POST['category_sets'] : [],
        ]),
    ];

    if (isset($_POST['view']) && $_POST['view'] === 'preview') {
        // プレビュー
        $_view['page'] = $post['page'];
    } else {
        // 入力データを検証＆登録
        $warnings = model('validate_pages', $post['page']);
        if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
            if (empty($warnings)) {
                ok();
            } else {
                warning($warnings);
            }
        } else {
            if (empty($warnings)) {
                $_SESSION['post']['page'] = $post['page'];

                // フォワード
                forward('/admin/page_post');
            } else {
                $_view['page'] = $post['page'];

                $_view['warnings'] = $warnings;
            }
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $_view['page'] = model('default_pages');
    } else {
        $pages = model('select_pages', [
            'where' => [
                'pages.id = :id',
                [
                    'id' => $_GET['id'],
                ],
            ],
        ], [
            'associate' => true,
        ]);
        if (empty($pages)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['page'] = $pages[0];
        }
    }

    if (isset($_GET['_type']) && $_GET['_type'] === 'json') {
        // ページ情報を取得
        header('Content-Type: application/json; charset=' . MAIN_CHARSET);

        echo json_encode([
            'status' => 'OK',
            'data'   => $_view,
            'files'  => [
                'picture'   => $_view['page']['picture']   ? file_mimetype($_view['page']['picture'])   : null,
                'thumbnail' => $_view['page']['thumbnail'] ? file_mimetype($_view['page']['thumbnail']) : null,
            ],
        ]);

        exit;
    } else {
        // 投稿セッションを初期化
        unset($_SESSION['post']);
        unset($_SESSION['file']);
    }

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['page'] = localdate('Y-m-d H:i:s');
    }
}

if ((empty($_POST['view']) || $_POST['view'] !== 'preview')) {
    // ページの表示用データ作成
    $_view['page'] = model('view_pages', $_view['page']);
}

// フィールドを取得
$_view['fields'] = model('select_fields', [
    'where'    => [
        'target = :target',
        [
            'target' => 'page',
        ],
    ],
    'order_by' => 'sort, id',
]);

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = 'ページ登録';
} else {
    $_view['title'] = 'ページ編集';
}
