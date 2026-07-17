<?php

// 絞り込み条件を作成
$filters = model('filter_comments', $_GET, [
    'associate' => true,
]);
if ($filters['where'] === '') {
    $filters['where'] = null;
}

// 絞り込み条件を引き継ぎ
$_view['comment_filter'] = $filters['pager'];

// 絞り込み条件を初期化
if (!isset($_GET['keyword'])) {
    $_GET['keyword'] = null;
}
if (!isset($_GET['approved'])) {
    $_GET['approved'] = null;
}

// ページを取得
if (isset($_GET['page'])) {
    $_GET['page'] = intval($_GET['page']);
} else {
    $_GET['page'] = 1;

    $_SESSION['bulk']['comment'] = [];
}

// コメントを取得
$_view['comments'] = model('select_comments', [
    'where'    => $filters['where'],
    'order_by' => 'comments.id DESC',
    'limit'    => [
        ':offset, :limit',
        [
            'offset' => $GLOBALS['setting']['number_limit_admin_comment'] * ($_GET['page'] - 1),
            'limit'  => $GLOBALS['setting']['number_limit_admin_comment'],
        ],
    ],
], [
    'associate' => true,
]);

$comment_count = model('select_comments', [
    'select' => 'COUNT(DISTINCT comments.id) AS count',
    'where'  => $filters['where'],
], [
    'associate' => true,
]);
$_view['comment_count'] = $comment_count[0]['count'];
$_view['comment_page']  = ceil($comment_count[0]['count'] / $GLOBALS['setting']['number_limit_admin_comment']);

// タイトル
$_view['title'] = 'コメント管理';
