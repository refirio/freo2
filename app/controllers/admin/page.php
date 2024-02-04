<?php

// ページを取得
$_view['pages'] = model('select_pages', [
    'order_by' => 'pages.datetime DESC, pages.id',
], [
    'associate' => true,
]);

// タイトル
$_view['title'] = 'ページ管理';
