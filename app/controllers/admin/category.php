<?php

// カテゴリを取得
$_view['categories'] = model('select_categories', [
    'order_by' => 'sort, id',
]);

// タイトル
$_view['title'] = 'カテゴリ管理';
