<?php

import('app/services/menu.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/admin/menu_form');
}

// トランザクションを開始
db_transaction();

if (empty($_SESSION['post']['menu']['id'])) {
    // メニューを登録
    $resource = service_menu_insert([
        'values' => [
            'enabled' => $_SESSION['post']['menu']['enabled'],
            'title'   => $_SESSION['post']['menu']['title'],
            'url'     => $_SESSION['post']['menu']['url'],
            'sort'    => $_SESSION['post']['menu']['sort'],
        ],
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} else {
    // メニューを編集
    $resource = service_menu_update([
        'set'   => [
            'enabled' => $_SESSION['post']['menu']['enabled'],
            'title'   => $_SESSION['post']['menu']['title'],
            'url'     => $_SESSION['post']['menu']['url'],
        ],
        'where' => [
            'id = :id',
            [
                'id' => $_SESSION['post']['menu']['id'],
            ],
        ],
    ], [
        'id'     => intval($_SESSION['post']['menu']['id']),
        'update' => $_SESSION['update']['menu'],
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
redirect('/admin/menu?ok=post');
