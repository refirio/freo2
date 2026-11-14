<?php

import('app/services/entry.php');

if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
    // 処理対象を保持（型ごとに分ける）
    service_entry_bulk_select('page', $_POST);

    ok();
} else {
    // 処理対象を取得（選んだもののうち、この型のものだけ）
    $_view['entries'] = service_entry_bulk_entries('page');
    if (!empty($_view['entries'])) {
        $_view['entry_bulks'] = array_column($_view['entries'], 'id');
    }
}

// 操作した一覧の親ページ
$_view['parent'] = (isset($_GET['parent']) && is_string($_GET['parent'])) ? $_GET['parent'] : '';
$_view['back']   = '/admin/page' . ($_view['parent'] === '' ? '' : '?parent=' . rawurlencode($_view['parent']));

// タイトル
$_view['title'] = 'ページ一括削除';
