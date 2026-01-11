<?php

import('libs/modules/file.php');

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
        'entry' => model('normalize_entries', [
            'id'             => isset($_POST['id'])             ? $_POST['id']             : '',
            'type_id'        => isset($_POST['type_id'])        ? $_POST['type_id']        : '',
            'public'         => isset($_POST['public'])         ? $_POST['public']         : '',
            'public_begin'   => isset($_POST['public_begin'])   ? $_POST['public_begin']   : '',
            'public_end'     => isset($_POST['public_end'])     ? $_POST['public_end']     : '',
            'password'       => isset($_POST['password'])       ? $_POST['password']       : '',
            'datetime'       => isset($_POST['datetime'])       ? $_POST['datetime']       : '',
            'code'           => isset($_POST['code'])           ? $_POST['code']           : '',
            'title'          => isset($_POST['title'])          ? $_POST['title']          : '',
            'text'           => isset($_POST['text'])           ? $_POST['text']           : '',
            'comment'        => isset($_POST['comment'])        ? $_POST['comment']        : '',
            'field_sets'     => isset($_POST['field_sets'])     ? $_POST['field_sets']     : [],
            'attribute_sets' => isset($_POST['attribute_sets']) ? $_POST['attribute_sets'] : [],
            'picture_files'  => isset($_POST['picture_files'])  ? $_POST['picture_files']  : [],
        ]),
    ];

    if (isset($_POST['view']) && $_POST['view'] === 'preview') {
        // プレビュー
        $_view['entry'] = $post['entry'];
    } else {
        // 入力データを検証＆登録
        $warnings = model('validate_entries', $post['entry']);
        if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
            if (empty($warnings)) {
                ok();
            } else {
                warning($warnings);
            }
        } else {
            if (empty($warnings)) {
                $_SESSION['post']['entry'] = $post['entry'];

                // フォワード
                forward('/admin/page_post');
            } else {
                $_view['entry'] = $post['entry'];

                $_view['warnings'] = $warnings;
            }
        }
    }
} else {
    // 初期データを取得
    if (empty($_GET['id'])) {
        $_view['entry'] = model('default_entries');
        $_view['entry']['code'] = $GLOBALS['setting']['page_default_code'] ? localdate($GLOBALS['setting']['page_default_code']) : '';
    } else {
        $entries = model('select_entries', [
            'where' => [
                'entries.id = :id AND types.code = ' . db_escape('page'),
                [
                    'id' => $_GET['id'],
                ],
            ],
        ], [
            'associate' => true,
        ]);
        if (empty($entries)) {
            warning('編集データが見つかりません。');
        } else {
            $_view['entry'] = $entries[0];
        }
    }

    if (isset($_GET['_type']) && $_GET['_type'] === 'json') {
        // アップロードファイルを確認
        $files = [
            'pictures'  => $_view['entry']['pictures']  ? file_mimetype($_view['entry']['pictures'])  : null,
            'thumbnail' => $_view['entry']['thumbnail'] ? file_mimetype($_view['entry']['thumbnail']) : null,
        ];
        if (!empty($_GET['id'])) {
            $field_sets = model('select_field_sets', [
                'select' => 'field_sets.field_id, field_sets.text',
                'where'  => [
                    'field_sets.entry_id = :entry_id AND (fields.kind = ' . db_escape('image') . ' OR fields.kind = ' . db_escape('file') . ')',
                    [
                        'entry_id' => $_GET['id'],
                    ],
                ],
            ], [
                'associate' => true,
            ]);
            foreach ($field_sets as $field_set) {
                $key = 'field_' . $_GET['id'] . '_' . $field_set['field_id'];

                if ($field_set['text']) {
                    $files[$key] = file_mimetype($field_set['text']);
                }
            }
        }

        // JSON形式で出力
        header('Content-Type: application/json; charset=' . MAIN_CHARSET);

        echo json_encode([
            'status' => 'OK',
            'data'   => $_view,
            'files'  => $files,
        ]);

        exit;
    } else {
        // 投稿セッションを初期化
        unset($_SESSION['post']);
        unset($_SESSION['file']);
    }

    // 編集開始日時を記録
    if (!empty($_GET['id'])) {
        $_SESSION['update']['entry'] = localdate('Y-m-d H:i:s');
    }
}

if ((empty($_POST['view']) || $_POST['view'] !== 'preview')) {
    // エントリーの表示用データ作成
    $_view['entry'] = model('view_entries', $_view['entry']);
}

// 型を取得
$types = model('select_types', [
    'where' => 'code = ' . db_escape('page'),
]);
$_view['type'] = $types[0];

// フィールドを取得
$_view['fields'] = model('select_fields', [
    'where'    => 'type_id = ' . intval($_view['type']['id']),
    'order_by' => 'sort, id',
]);

// 属性を取得
$_view['attributes'] = model('select_attributes', [
    'order_by' => 'sort, id',
]);

// タイトル
if (empty($_GET['id'])) {
    $_view['title'] = 'ページ登録';
} else {
    $_view['title'] = 'ページ編集';
}
