<?php

// フィールドを取得
$_view['fields'] = model('select_fields', [
    'order_by' => 'fields.sort, fields.id',
], [
    'associate' => true,
]);

// タイトル
$_view['title'] = 'フィールド管理';
