<?php

import('app/services/entry.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/admin/page_form');
}

// アップロードファイル
$files = [
    'picture'   => isset($_SESSION['file']['entry']['picture'])   ? $_SESSION['file']['entry']['picture']   : [],
    'thumbnail' => isset($_SESSION['file']['entry']['thumbnail']) ? $_SESSION['file']['entry']['thumbnail'] : [],
];
$fields = model('select_fields', [
    'select' => 'id',
    'where'  => [
        'type_id = :type_id AND (kind = ' . db_escape('image') . ' OR kind = ' . db_escape('file') . ')',
        [
            'type_id' => $_SESSION['post']['entry']['type_id'],
        ],
    ],
]);
if (!empty($fields)) {
    foreach ($fields as $field) {
        if (empty($_SESSION['post']['entry']['id'])) {
            $key = 'field__' . $field['id'];
        } else {
            $key = 'field_' . $_SESSION['post']['entry']['id'] . '_' . $field['id'];
        }
        $files[$key] = isset($_SESSION['file']['field'][$key]) ? $_SESSION['file']['field'][$key] : [];
    }
}

// トランザクションを開始
db_transaction();

if (empty($_SESSION['post']['entry']['id'])) {
    // エントリーを登録
    $resource = service_entry_insert([
        'values' => [
            'type_id'      => $_SESSION['post']['entry']['type_id'],
            'public'       => $_SESSION['post']['entry']['public'],
            'public_begin' => $_SESSION['post']['entry']['public_begin'],
            'public_end'   => $_SESSION['post']['entry']['public_end'],
            'datetime'     => $_SESSION['post']['entry']['datetime'],
            'code'         => $_SESSION['post']['entry']['code'],
            'title'        => $_SESSION['post']['entry']['title'],
            'text'         => $_SESSION['post']['entry']['text'],
        ],
    ], [
        'field_sets' => $_SESSION['post']['entry']['field_sets'],
        'files'      => $files,
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} else {
    // エントリーを編集
    $resource = service_entry_update([
        'set'  => [
            'type_id'      => $_SESSION['post']['entry']['type_id'],
            'public'       => $_SESSION['post']['entry']['public'],
            'public_begin' => $_SESSION['post']['entry']['public_begin'],
            'public_end'   => $_SESSION['post']['entry']['public_end'],
            'datetime'     => $_SESSION['post']['entry']['datetime'],
            'code'         => $_SESSION['post']['entry']['code'],
            'title'        => $_SESSION['post']['entry']['title'],
            'text'         => $_SESSION['post']['entry']['text'],
        ],
        'where' => [
            'id = :id',
            [
                'id' => $_SESSION['post']['entry']['id'],
            ],
        ],
    ], [
        'id'         => intval($_SESSION['post']['entry']['id']),
        'update'     => $_SESSION['update']['entry'],
        'field_sets' => $_SESSION['post']['entry']['field_sets'],
        'files'      => $files,
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }
}

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);
unset($_SESSION['file']);
unset($_SESSION['update']);

// リダイレクト
redirect('/admin/page?ok=post');
