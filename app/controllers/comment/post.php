<?php

import('app/services/comment.php');

//ワンタイムトークン
if (!token('check')) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/');
}

// トランザクションを開始
db_transaction();

// コメントを登録
$resource = service_comment_insert([
    'values' => [
        'entry_id' => $_SESSION['post']['comment']['entry_id'],
        'name'     => $_SESSION['post']['comment']['name'],
        'url'      => $_SESSION['post']['comment']['url'],
        'message'  => $_SESSION['post']['comment']['message'],
    ],
]);
if (!$resource) {
    error('データを登録できません。');
}

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);

// リダイレクト
redirect('/comment/complete');
