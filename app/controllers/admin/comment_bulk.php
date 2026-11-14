<?php

if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
    // 処理対象を保持
    if (!isset($_SESSION['bulk']['comments'])) {
        $_SESSION['bulk']['comments'] = [];
    }
    if (empty($_POST['id'])) {
        foreach ($_POST['list'] as $id => $checked) {
            if ($checked === '1') {
                $_SESSION['bulk']['comments'][$id] = true;
            } else {
                unset($_SESSION['bulk']['comments'][$id]);
            }
        }
    } else {
        if ($_POST['checked'] === '1') {
            $_SESSION['bulk']['comments'][$_POST['id']] = true;
        } else {
            unset($_SESSION['bulk']['comments'][$_POST['id']]);
        }
    }

    ok();
} elseif (!empty($_SESSION['bulk']['comments'])) {
    // 処理対象を取得
    $_view['comments'] = model('select_comments', [
        'where'    => 'id IN(' . implode(',', array_map('db_escape', array_keys($_SESSION['bulk']['comments']))) . ')',
        'order_by' => 'id DESC',
    ]);
    $_view['comment_bulks'] = array_keys($_SESSION['bulk']['comments']);
}

// タイトル
$_view['title'] = 'コメント一括削除';
