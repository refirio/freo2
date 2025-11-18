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
    service_storage_rename($GLOBALS['config']['file_target']['media'] . $directory . '/' . $_SESSION['post']['media']['name'], $GLOBALS['config']['file_target']['media'] . $directory . '/' . $_SESSION['post']['media']['rename']);
} elseif (isset($_SESSION['media'])) {
    // ディレクトリを作成
    service_storage_put($GLOBALS['config']['file_target']['media'] . $directory . '/');

    // アップロードファイルを一時領域から移動
    foreach ($_SESSION['media'] as $media) {
        service_storage_rename($GLOBALS['config']['file_target']['temp'] . session_id() . '_' . $media, $GLOBALS['config']['file_target']['media'] . $directory . '/' . $media);
    }
} else {
    error('不正なアクセスです。');
}

// 投稿セッションを初期化
unset($_SESSION['post']);
unset($_SESSION['media']);

// リダイレクト
redirect('/admin/media?ok=post' . ($directory === '' ? '' : '&directory=' . $directory) . (empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']));
