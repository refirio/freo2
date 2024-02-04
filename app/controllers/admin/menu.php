<?php

// メニューを取得
$_view['menus'] = model('select_menus', [
    'order_by' => 'sort, id',
]);

// タイトル
$_view['title'] = 'メニュー管理';
