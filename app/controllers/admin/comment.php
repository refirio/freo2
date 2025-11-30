<?php

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;

    $_SESSION['bulk']['comment'] = [];
}

// コメントを取得
$_view['comments'] = model('select_comments', [
    'order_by' => 'comments.id DESC',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['config']['limit']['admin_comment'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['config']['limit']['admin_comment'],
        ],
    ],
], [
    'associate' => true,
]);

$comment_count = model('select_comments', [
    'select' => 'COUNT(DISTINCT comments.id) AS count',
], [
    'associate' => true,
]);
$_view['comment_count'] = $comment_count[0]['count'];
$_view['comment_page']  = ceil($comment_count[0]['count'] / $GLOBALS['config']['limit']['admin_comment']);

// タイトル
$_view['title'] = 'コメント管理';
