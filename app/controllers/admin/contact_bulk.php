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
        'where'    => 'contacts.id IN(' . implode(',', array_map('db_escape', array_keys($_SESSION['bulk']['contact']))) . ')',
        'order_by' => 'contacts.id DESC',
    ], [
        'associate' => true,
    ]);
    $_view['contact_bulks'] = array_keys($_SESSION['bulk']['contact']);

    // カテゴリを取得
    $categories = model('select_categories', [
        'order_by' => 'sort, id',
    ]);
    $category_sets = [];
    foreach ($categories as $category) {
        $category_sets[$category['id']] = $category;
    }
    $_view['category_sets'] = $category_sets;
    $_view['categories']    = $categories;
}

// タイトル
$_view['title'] = '一括処理';
