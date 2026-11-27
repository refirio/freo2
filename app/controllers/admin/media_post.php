<?php

import('app/services/storage.php');
import('app/services/media.php');

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

    // サムネイルを作り直す
    service_media_thumbnail_remove(($directory ? $directory . '/' : '') . $_SESSION['post']['media']['name']);
    service_media_thumbnail_create(($directory ? $directory . '/' : '') . $_SESSION['post']['media']['rename']);
} elseif (isset($_SESSION['medias'])) {
    // ディレクトリを作成
    service_storage_put($GLOBALS['config']['file_target']['media'] . ($directory ? $directory . '/' : ''));

    // アップロードファイルを一時領域から移動
    foreach ($_SESSION['medias'] as $media) {
        service_storage_rename($GLOBALS['config']['file_target']['media'] . ($directory ? $directory . '/' : '') . $media, $GLOBALS['config']['file_target']['temp'] . session_id() . '_' . $media);

        // サムネイルを作成
        service_media_thumbnail_remove(($directory ? $directory . '/' : '') . $media);
        service_media_thumbnail_create(($directory ? $directory . '/' : '') . $media);
    }

    // このメディアの一時ファイルと、古い一時ファイルを削除（記事の入力画面の一時ファイルは、そちらで削除するので残す）
    $files = service_storage_list($GLOBALS['config']['file_target']['temp']);

    foreach ($files as $file) {
        if (preg_match('/^' . preg_quote(session_id() . '_', '/') . '/', $file['name']) && !preg_match('/^' . preg_quote(session_id() . '_entry_', '/') . '/', $file['name'])) {
            service_storage_remove($GLOBALS['config']['file_target']['temp'] . $file['name']);
        }
    }

    service_storage_temp_clean();
} else {
    // リダイレクト
    redirect('/admin/media_form?warning=post' . ($directory === '' ? '' : '&directory=' . $directory) . (empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']));
}

// 投稿セッションを初期化
unset($_SESSION['post']);
unset($_SESSION['medias']);

// リダイレクト
redirect('/admin/media?ok=post' . ($directory === '' ? '' : '&directory=' . $directory) . (empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']));
