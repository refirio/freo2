<?php

import('app/services/entry.php');

// ワンタイムトークン
if (!token('check')) {
    error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
}

// アクセス元
if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
    error('不正なアクセスです。');
}

if (!empty($_POST['id'])) {
    // 削除するページのコードを取得
    $entries = model('select_entries', [
        'select' => 'code',
        'where'  => [
            'id = :id',
            [
                'id' => $_POST['id'],
            ],
        ],
    ]);

    // トランザクションを開始
    db_transaction();

    // エントリーを削除
    $resource = service_entry_delete_typed('page', $_POST['id']);
    if (!$resource) {
        error('データを削除できません。');
    }

    // トランザクションを終了
    db_commit();

    // 親ページを取得
    $parent = empty($entries) ? null : service_entry_parent($entries[0]['code'], service_entry_page_codes());

    // リダイレクト
    redirect(service_entry_page_list('ok=delete', $parent));
} elseif (!empty($_POST['list'])) {
    // トランザクションを開始
    db_transaction();

    // エントリーを削除
    $resource = service_entry_delete_typed('page', $_POST['list']);
    if (!$resource) {
        error('データを削除できません。');
    }

    // トランザクションを終了
    db_commit();

    // 一括処理セッションを初期化（この型の選択だけ）
    service_entry_bulk_clear('page');

    // 操作した一覧の親ページを取得（親ページも削除した場合は、残っている祖先）
    $parent = null;
    if (isset($_POST['parent']) && $_POST['parent'] !== '') {
        $codes = service_entry_page_codes();
        if (in_array($_POST['parent'], $codes, true)) {
            $parent = $_POST['parent'];
        } else {
            $parent = service_entry_parent($_POST['parent'], $codes);
        }
    }

    // リダイレクト
    redirect(service_entry_page_list('ok=delete', $parent));
} else {
    // リダイレクト
    redirect('/admin/page?warning=delete');
}
