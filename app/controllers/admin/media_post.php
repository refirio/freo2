<?php

import('app/services/storage.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['media'])) {
    // リダイレクト
    redirect('/admin/media_form');
}

// ファイルを移動
foreach ($_SESSION['media'] as $media) {
    service_storage_move($GLOBALS['config']['file_target']['temp'] . session_id() . '_' . $media, $GLOBALS['config']['file_target']['media'] . $media);
}

// リダイレクト
redirect('/admin/media?ok=post');
