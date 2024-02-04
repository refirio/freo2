<?php

import('app/services/widget.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/admin/widget_form');
}

// トランザクションを開始
db_transaction();

// ウィジェットを編集
$resource = service_widget_update([
    'set'   => [
        'title' => $_SESSION['post']['widget']['title'],
        'code'  => $_SESSION['post']['widget']['code'],
        'text'  => $_SESSION['post']['widget']['text'],
    ],
    'where' => [
        'id = :id',
        [
            'id' => $_SESSION['post']['widget']['id'],
        ],
    ],
], [
    'id'     => intval($_SESSION['post']['widget']['id']),
    'update' => $_SESSION['update']['widget'],
]);
if (!$resource) {
    error('データを編集できません。');
}

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);
unset($_SESSION['update']);

// リダイレクト
redirect('/admin/widget?ok=post');
