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

// 承認
$approve = $GLOBALS['setting']['page_use_approve'] ? 0 : 1;

// アップロードファイル
$files = [
    'pictures'  => isset($_SESSION['file']['entry']['pictures'])  ? $_SESSION['file']['entry']['pictures']  : [],
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
            'approved'     => $approve,
            'public'       => $_SESSION['post']['entry']['public'],
            'public_begin' => $_SESSION['post']['entry']['public_begin'],
            'public_end'   => $_SESSION['post']['entry']['public_end'],
            'password'     => $_SESSION['post']['entry']['password'],
            'datetime'     => $_SESSION['post']['entry']['datetime'],
            'code'         => $_SESSION['post']['entry']['code'],
            'title'        => $_SESSION['post']['entry']['title'],
            'text'         => $_SESSION['post']['entry']['text'],
            'comment'      => $_SESSION['post']['entry']['comment'],
        ],
    ], [
        'field_sets'     => $_SESSION['post']['entry']['field_sets'],
        'category_sets'  => $_SESSION['post']['entry']['category_sets'],
        'attribute_sets' => $_SESSION['post']['entry']['attribute_sets'],
        'files'          => $files,
        'picture_files'  => $_SESSION['post']['entry']['picture_files'],
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
            'password'     => $_SESSION['post']['entry']['password'],
            'datetime'     => $_SESSION['post']['entry']['datetime'],
            'code'         => $_SESSION['post']['entry']['code'],
            'title'        => $_SESSION['post']['entry']['title'],
            'text'         => $_SESSION['post']['entry']['text'],
            'comment'      => $_SESSION['post']['entry']['comment'],
        ],
        'where' => [
            'id = :id',
            [
                'id' => $_SESSION['post']['entry']['id'],
            ],
        ],
    ], [
        'id'             => intval($_SESSION['post']['entry']['id']),
        'update'         => $_SESSION['update']['entry'],
        'field_sets'     => $_SESSION['post']['entry']['field_sets'],
        'category_sets'  => $_SESSION['post']['entry']['category_sets'],
        'attribute_sets' => $_SESSION['post']['entry']['attribute_sets'],
        'files'          => $files,
        'picture_files'  => $_SESSION['post']['entry']['picture_files'],
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
