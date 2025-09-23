<?php

// 属性を取得
$_view['attributes'] = model('select_attributes', [
    'order_by' => 'sort, id',
]);

// タイトル
$_view['title'] = '属性管理';
