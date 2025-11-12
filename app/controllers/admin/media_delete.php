<?php

import('app/services/storage.php');

// ワンタイムトークン
if (!token('check')) {
    error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
}

// アクセス元
if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
    error('不正なアクセスです。');
}

if (empty($_POST['medias'])) {
    // リダイレクト
    redirect('/admin/media?warning=delete');
} elseif (empty($_POST['confirm'])) {
    // メディアを削除
    foreach ($_POST['medias'] as $media) {
        service_storage_remove($GLOBALS['config']['file_target']['media'] . $media);
    }

    // リダイレクト
    redirect('/admin/media?ok=delete');
} else {
    // メディアを取得
    $_view['medias'] = $_POST['medias'];

    // タイトル
    $_view['title'] = 'メディア削除';
}
