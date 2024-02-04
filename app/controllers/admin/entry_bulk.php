<?php

if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
    // 処理対象を保持
    if (!isset($_SESSION['bulk']['entry'])) {
        $_SESSION['bulk']['entry'] = [];
    }
    if (empty($_POST['id'])) {
        foreach ($_POST['list'] as $id => $checked) {
            if ($checked === '1') {
                $_SESSION['bulk']['entry'][$id] = true;
            } else {
                unset($_SESSION['bulk']['entry'][$id]);
            }
        }
    } else {
        if ($_POST['checked'] === '1') {
            $_SESSION['bulk']['entry'][$_POST['id']] = true;
        } else {
            unset($_SESSION['bulk']['entry'][$_POST['id']]);
        }
    }

    ok();
} elseif (!empty($_SESSION['bulk']['entry'])) {
    // 処理対象を取得
    $_view['entries'] = model('select_entries', [
        'where'    => 'entries.id IN(' . implode(',', array_map('db_escape', array_keys($_SESSION['bulk']['entry']))) . ')',
        'order_by' => 'entries.datetime DESC, entries.id',
    ], [
        'associate' => true,
    ]);
    $_view['entry_bulks'] = array_keys($_SESSION['bulk']['entry']);

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
