<?php

import('app/services/comment.php');

// コメントを取得
$comments = model('select_comments', [
    'where' => [
        'comments.id = :id',
        [
            'id' => $_GET['id'],
        ],
    ],
], [
    'associate' => true,
]);
if (empty($comments)) {
    warning('表示データが見つかりません。');
} else {
    $_view['comment'] = $comments[0];

    // 対象（型の名前は、データの定義から）
    $_view['comment']['target'] = service_comment_target($_view['comment']);
}

// タイトル
$_view['title'] = 'コメント表示';
