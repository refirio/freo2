<?php

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
}

// タイトル
$_view['title'] = 'コメント表示';
