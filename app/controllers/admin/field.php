<?php

// フィールドを取得
$_view['fields'] = model('select_fields', [
    'order_by' => 'sort, id',
]);

// タイトル
$_view['title'] = 'フィールド管理';
