<?php

// カテゴリーを取得
$_view['categories'] = model('select_categories', [
    'where'    => 'types.code = ' . db_escape('entry'),
    'order_by' => 'categories.sort, categories.id',
], [
    'associate' => true,
]);

// タイトル
$_view['title'] = 'カテゴリー管理';
