<?php

if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
    // 処理対象を保持
    if (!isset($_SESSION['bulk']['contacts'])) {
        $_SESSION['bulk']['contacts'] = [];
    }
    if (empty($_POST['id'])) {
        foreach ($_POST['list'] as $id => $checked) {
            if ($checked === '1') {
                $_SESSION['bulk']['contacts'][$id] = true;
            } else {
                unset($_SESSION['bulk']['contacts'][$id]);
            }
        }
    } else {
        if ($_POST['checked'] === '1') {
            $_SESSION['bulk']['contacts'][$_POST['id']] = true;
        } else {
            unset($_SESSION['bulk']['contacts'][$_POST['id']]);
        }
    }

    ok();
} elseif (!empty($_SESSION['bulk']['contacts'])) {
    // 処理対象を取得
    $_view['contacts'] = model('select_contacts', [
        'where'    => 'id IN(' . implode(',', array_map('db_escape', array_keys($_SESSION['bulk']['contacts']))) . ')',
        'order_by' => 'id DESC',
    ]);
    $_view['contact_bulks'] = array_keys($_SESSION['bulk']['contacts']);
}

// タイトル
$_view['title'] = 'お問い合わせ一括削除';
