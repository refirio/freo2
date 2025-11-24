<?php

if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
    // 処理対象を保持
    if (!isset($_SESSION['bulk']['comment'])) {
        $_SESSION['bulk']['comment'] = [];
    }
    if (empty($_POST['id'])) {
        foreach ($_POST['list'] as $id => $checked) {
            if ($checked === '1') {
                $_SESSION['bulk']['comment'][$id] = true;
            } else {
                unset($_SESSION['bulk']['comment'][$id]);
            }
        }
    } else {
        if ($_POST['checked'] === '1') {
            $_SESSION['bulk']['comment'][$_POST['id']] = true;
        } else {
            unset($_SESSION['bulk']['comment'][$_POST['id']]);
        }
    }

    ok();
} elseif (!empty($_SESSION['bulk']['comment'])) {
    // 処理対象を取得
    $_view['comments'] = model('select_comments', [
        'where'    => 'id IN(' . implode(',', array_map('db_escape', array_keys($_SESSION['bulk']['comment']))) . ')',
        'order_by' => 'id DESC',
    ]);
    $_view['comment_bulks'] = array_keys($_SESSION['bulk']['comment']);
}

// タイトル
$_view['title'] = 'コメント一括削除';
