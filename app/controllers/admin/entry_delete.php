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
    // トランザクションを開始
    db_transaction();

    // エントリーを削除
    $resource = service_entry_delete_typed('entry', $_POST['id']);
    if (!$resource) {
        error('データを削除できません。');
    }

    // トランザクションを終了
    db_commit();

    // リダイレクト
    redirect('/admin/entry?ok=delete');
} elseif (!empty($_POST['list'])) {
    // トランザクションを開始
    db_transaction();

    // エントリーを削除
    $resource = service_entry_delete_typed('entry', $_POST['list']);
    if (!$resource) {
        error('データを削除できません。');
    }

    // トランザクションを終了
    db_commit();

    // 一括処理セッションを初期化（この型の選択だけ）
    service_entry_bulk_clear('entry');

    // リダイレクト
    redirect('/admin/entry?page=' . intval($_POST['page']) . '&ok=delete');
} else {
    // リダイレクト
    redirect('/admin/entry?warning=delete');
}
