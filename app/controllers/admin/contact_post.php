<?php

import('app/services/contact.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/admin/contact_form');
}

// トランザクションを開始
db_transaction();

// お問い合わせを編集
$resource = service_contact_update([
    'set'  => [
        'name'    => $_SESSION['post']['contact']['name'],
        'email'   => $_SESSION['post']['contact']['email'],
        'message' => $_SESSION['post']['contact']['message'],
        'status'  => $_SESSION['post']['contact']['status'],
        'memo'    => $_SESSION['post']['contact']['memo'],
    ],
    'where' => [
        'id = :id',
        [
            'id' => $_SESSION['post']['contact']['id'],
        ],
    ],
], [
    'id'     => intval($_SESSION['post']['contact']['id']),
    'update' => $_SESSION['update']['contact'],
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
redirect('/admin/contact?ok=post');
