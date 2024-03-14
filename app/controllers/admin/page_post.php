<?php

import('app/services/page.php');

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
    'picture'   => isset($_SESSION['file']['page']['picture'])   ? $_SESSION['file']['page']['picture']   : [],
    'thumbnail' => isset($_SESSION['file']['page']['thumbnail']) ? $_SESSION['file']['page']['thumbnail'] : [],
];
$fields = model('select_fields', [
    'select' => 'id',
    'where'  => '(type = \'image\' OR type = \'file\') AND target = \'page\'',
]);
if (!empty($fields)) {
    foreach ($fields as $field) {
        if (empty($_SESSION['post']['page']['id'])) {
            $key = 'field__' . $field['id'];
        } else {
            $key = 'field_' . $_SESSION['post']['page']['id'] . '_' . $field['id'];
        }
        $files[$key] = isset($_SESSION['file']['field'][$key]) ? $_SESSION['file']['field'][$key] : [];
    }
}

// トランザクションを開始
db_transaction();

if (empty($_SESSION['post']['page']['id'])) {
    // ページを登録
    $resource = service_page_insert([
        'values' => [
            'public'       => $_SESSION['post']['page']['public'],
            'public_begin' => $_SESSION['post']['page']['public_begin'],
            'public_end'   => $_SESSION['post']['page']['public_end'],
            'datetime'     => $_SESSION['post']['page']['datetime'],
            'code'         => $_SESSION['post']['page']['code'],
            'title'        => $_SESSION['post']['page']['title'],
            'text'         => $_SESSION['post']['page']['text'],
        ],
    ], [
        'field_sets'    => $_SESSION['post']['page']['field_sets'],
        'category_sets' => $_SESSION['post']['page']['category_sets'],
        'files'         => $files,
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} else {
    // ページを編集
    $resource = service_page_update([
        'set'  => [
            'public'       => $_SESSION['post']['page']['public'],
            'public_begin' => $_SESSION['post']['page']['public_begin'],
            'public_end'   => $_SESSION['post']['page']['public_end'],
            'datetime'     => $_SESSION['post']['page']['datetime'],
            'code'         => $_SESSION['post']['page']['code'],
            'title'        => $_SESSION['post']['page']['title'],
            'text'         => $_SESSION['post']['page']['text'],
        ],
        'where' => [
            'id = :id',
            [
                'id' => $_SESSION['post']['page']['id'],
            ],
        ],
    ], [
        'id'            => intval($_SESSION['post']['page']['id']),
        'update'        => $_SESSION['update']['page'],
        'field_sets'    => $_SESSION['post']['page']['field_sets'],
        'category_sets' => $_SESSION['post']['page']['category_sets'],
        'files'         => $files,
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
