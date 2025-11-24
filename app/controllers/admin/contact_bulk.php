<?php

if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
    // 処理対象を保持
    if (!isset($_SESSION['bulk']['contact'])) {
        $_SESSION['bulk']['contact'] = [];
    }
    if (empty($_POST['id'])) {
        foreach ($_POST['list'] as $id => $checked) {
            if ($checked === '1') {
                $_SESSION['bulk']['contact'][$id] = true;
            } else {
                unset($_SESSION['bulk']['contact'][$id]);
            }
        }
    } else {
        if ($_POST['checked'] === '1') {
            $_SESSION['bulk']['contact'][$_POST['id']] = true;
        } else {
            unset($_SESSION['bulk']['contact'][$_POST['id']]);
        }
    }

    ok();
} elseif (!empty($_SESSION['bulk']['contact'])) {
    // 処理対象を取得
    $_view['contacts'] = model('select_contacts', [
        'where'    => 'id IN(' . implode(',', array_map('db_escape', array_keys($_SESSION['bulk']['contact']))) . ')',
        'order_by' => 'id DESC',
    ]);
    $_view['contact_bulks'] = array_keys($_SESSION['bulk']['contact']);
}

// タイトル
$_view['title'] = 'お問い合わせ一括削除';
