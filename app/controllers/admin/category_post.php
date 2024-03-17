<?php

import('app/services/category.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/admin/category_form');
}

// トランザクションを開始
db_transaction();

if (empty($_SESSION['post']['category']['id'])) {
    // カテゴリを登録
    $resource = service_category_insert([
        'values' => [
            'type_id' => $_SESSION['post']['category']['type_id'],
            'code'    => $_SESSION['post']['category']['code'],
            'name'    => $_SESSION['post']['category']['name'],
            'sort'    => $_SESSION['post']['category']['sort'],
        ],
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} else {
    // カテゴリを編集
    $resource = service_category_update([
        'set'   => [
            'type_id' => $_SESSION['post']['category']['type_id'],
            'code'    => $_SESSION['post']['category']['code'],
            'name'    => $_SESSION['post']['category']['name'],
        ],
        'where' => [
            'id = :id',
            [
                'id' => $_SESSION['post']['category']['id'],
            ],
        ],
    ], [
        'id'     => intval($_SESSION['post']['category']['id']),
        'update' => $_SESSION['update']['category'],
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
redirect('/admin/category?ok=post');
