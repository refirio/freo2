<?php

import('app/services/storage.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/admin/media_form');
}

$directory = $_SESSION['post']['media']['directory'];

if (isset($_SESSION['post']['media']['name']) && isset($_SESSION['post']['media']['rename'])) {
    // ファイルの名前を変更
    service_storage_rename($GLOBALS['config']['file_target']['media'] . ($directory ? $directory . '/' : '') . $_SESSION['post']['media']['rename'], $GLOBALS['config']['file_target']['media'] . ($directory ? $directory . '/' : '') . $_SESSION['post']['media']['name']);
} elseif (isset($_SESSION['medias'])) {
    // ディレクトリを作成
    service_storage_put($GLOBALS['config']['file_target']['media'] . ($directory ? $directory . '/' : ''));

    // アップロードファイルを一時領域から移動
    foreach ($_SESSION['medias'] as $media) {
        service_storage_rename($GLOBALS['config']['file_target']['media'] . ($directory ? $directory . '/' : '') . $media, $GLOBALS['config']['file_target']['temp'] . session_id() . '_' . $media);
    }

    // 古いファイルを削除
    $files = service_storage_list($GLOBALS['config']['file_target']['temp']);

    foreach ($files as $file) {
        if (preg_match('/^' . preg_quote(session_id() . '_', '/') . '/', $file['name']) || ($file['name'] != '.gitkeep' && $file['modified'] < localdate() - 60 * 60)) {
            service_storage_remove($GLOBALS['config']['file_target']['temp'] . $file['name']);
        }
    }
} else {
    // リダイレクト
    redirect('/admin/media_form?warning=post' . ($directory === '' ? '' : '&directory=' . $directory) . (empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']));
}

// 投稿セッションを初期化
unset($_SESSION['post']);
unset($_SESSION['medias']);

// リダイレクト
redirect('/admin/media?ok=post' . ($directory === '' ? '' : '&directory=' . $directory) . (empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']));
