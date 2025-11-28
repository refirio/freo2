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

// 承認
$approve = $GLOBALS['setting']['comment_use_approve'] ? 0 : 1;

// トランザクションを開始
db_transaction();

// コメントを登録
$resource = service_comment_insert([
    'values' => [
        'entry_id' => $_SESSION['post']['comment']['entry_id'],
        'approved' => $approve,
        'name'     => $_SESSION['post']['comment']['name'],
        'url'      => $_SESSION['post']['comment']['url'],
        'message'  => $_SESSION['post']['comment']['message'],
    ],
]);
if (!$resource) {
    error('データを登録できません。');
}

$entry_id = $_SESSION['post']['comment']['entry_id'];

// トランザクションを終了
db_commit();

// 投稿セッションを初期化
unset($_SESSION['post']);

// リダイレクト
redirect('/comment/complete?entry_id=' . $entry_id);
