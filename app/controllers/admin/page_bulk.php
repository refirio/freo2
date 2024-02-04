<?php

if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
    // 処理対象を保持
    if (!isset($_SESSION['bulk']['page'])) {
        $_SESSION['bulk']['page'] = [];
    }
    if (empty($_POST['id'])) {
        foreach ($_POST['list'] as $id => $checked) {
            if ($checked === '1') {
                $_SESSION['bulk']['page'][$id] = true;
            } else {
                unset($_SESSION['bulk']['page'][$id]);
            }
        }
    } else {
        if ($_POST['checked'] === '1') {
            $_SESSION['bulk']['page'][$_POST['id']] = true;
        } else {
            unset($_SESSION['bulk']['page'][$_POST['id']]);
        }
    }

    ok();
} elseif (!empty($_SESSION['bulk']['page'])) {
    // 処理対象を取得
    $_view['pages'] = model('select_pages', [
        'where'    => 'pages.id IN(' . implode(',', array_map('db_escape', array_keys($_SESSION['bulk']['page']))) . ')',
        'order_by' => 'pages.datetime DESC, pages.id',
    ], [
        'associate' => true,
    ]);
    $_view['page_bulks'] = array_keys($_SESSION['bulk']['page']);
}

// タイトル
$_view['title'] = '一括処理';
