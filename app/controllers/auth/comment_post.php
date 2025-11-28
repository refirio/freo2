<?php

import('app/services/comment.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/auth/comment_form');
}

// トランザクションを開始
db_transaction();

if (empty($_SESSION['post']['comment']['id'])) {
    // お問い合わせを確認
    $contacts = model('select_contacts', [
        'where' => [
            'id = :contact_id AND user_id = :user_id',
            [
                'contact_id' => $_SESSION['post']['comment']['contact_id'],
                'user_id'    => $_SESSION['auth']['user']['id'],
            ],
        ],
    ]);
    if (empty($contacts)) {
        error('登録データが見つかりません。');
    }

    // コメントを登録
    $resource = service_comment_insert([
        'values' => [
            'contact_id' => $_SESSION['post']['comment']['contact_id'],
            'name'       => $_SESSION['post']['comment']['name'],
            'url'        => $_SESSION['post']['comment']['url'],
            'message'    => $_SESSION['post']['comment']['message'],
            'memo'       => $_SESSION['post']['comment']['memo'],
        ],
    ]);
    if (!$resource) {
        error('データを登録できません。');
    }
} else {
    // コメントを編集
    $resource = service_comment_update([
        'set'  => [
            'name'    => $_SESSION['post']['comment']['name'],
            'url'     => $_SESSION['post']['comment']['url'],
            'message' => $_SESSION['post']['comment']['message'],
            'memo'    => $_SESSION['post']['comment']['memo'],
        ],
        'where' => [
            'id = :id AND user_id = :user_id',
            [
                'id'      => $_SESSION['post']['comment']['id'],
                'user_id' => $_SESSION['auth']['user']['id'],
            ],
        ],
    ], [
        'id'     => intval($_SESSION['post']['comment']['id']),
        'update' => $_SESSION['update']['comment'],
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
redirect('/auth/comment?ok=post');
