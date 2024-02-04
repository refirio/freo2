<?php

// ウィジェットを取得
$_view['widgets'] = model('select_widgets', [
    'order_by' => 'sort, id',
]);

// タイトル
$_view['title'] = 'ウィジェット管理';
