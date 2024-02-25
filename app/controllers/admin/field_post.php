<?php

import('app/services/field.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/admin/field_form');
}

// トランザクションを開始
db_transaction();

if (empty($_SESSION['post']['field']['id'])) {
    // フィールドを登録
    $resource = service_field_insert([
        'values' => [
            'name'   => $_SESSION['post']['field']['name'],
            'type'   => $_SESSION['post']['field']['type'],
            'text'   => $_SESSION['post']['field']['text'],
            'target' => $_SESSION['post']['field']['target'],
            'sort'   => $_SESSION['post']['field']['sort'],
        ],
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} else {
    // フィールドを編集
    $resource = service_field_update([
        'set'   => [
            'name'   => $_SESSION['post']['field']['name'],
            'type'   => $_SESSION['post']['field']['type'],
            'text'   => $_SESSION['post']['field']['text'],
            'target' => $_SESSION['post']['field']['target'],
        ],
        'where' => [
            'id = :id',
            [
                'id' => $_SESSION['post']['field']['id'],
            ],
        ],
    ], [
        'id'     => intval($_SESSION['post']['field']['id']),
        'update' => $_SESSION['update']['field'],
    ]);
    if (!$resource) {
        error('データを編集できません。');
    }
}

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);
unset($_SESSION['update']);

// リダイレクト
redirect('/admin/field?ok=post');
