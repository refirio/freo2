<?php

// エントリーを取得
$_view['entries'] = model('select_entries', [
    'where'    => 'types.code = ' . db_escape('page'),
    'order_by' => 'entries.code, entries.id',
], [
    'associate' => true,
]);

// カテゴリーを取得
$_view['categories'] = model('select_categories', [
    'where'    => 'types.code = ' .  db_escape('page'),
    'order_by' => 'categories.sort, categories.id',
], [
    'associate' => true,
]);

// タイトル
$_view['title'] = 'ページ管理';
