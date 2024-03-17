<?php

// エントリーを取得
$_view['entries'] = model('select_entries', [
    'where'    => 'types.code = ' . db_escape('page'),
    'order_by' => 'entries.code, entries.id',
], [
    'associate' => true,
]);

// タイトル
$_view['title'] = 'ページ管理';
