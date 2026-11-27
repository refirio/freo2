<?php

import('app/services/setting.php');

// フォワードを確認
if (forward() === null) {
    error('不正なアクセスです。');
}

// 投稿データを確認
if (empty($_SESSION['post'])) {
    // リダイレクト
    redirect('/admin/setting');
}

// トランザクションを開始
db_transaction();

// 設定を編集
$resource = service_setting_save($_SESSION['post']['setting']);
if (!$resource) {
    error('データを編集できません。');
}

// トランザクションを終了
db_commit();

// HTML出力を無効にしたら、サーバーに出力したページを削除（残っていると、Webサーバーが古いページを返し続けるため）
if (array_key_exists('export_use_html', $_SESSION['post']['setting']) && !$_SESSION['post']['setting']['export_use_html']) {
    import('app/services/export.php');

    if (!service_export_clear()) {
        error('出力したページを削除できません。');
    }
}

// 投稿セッションを初期化
unset($_SESSION['post']);

// リダイレクト
redirect('/admin/setting?target=' . $_GET['target'] . '&ok=post');
