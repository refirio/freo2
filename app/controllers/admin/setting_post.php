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

// 投稿セッションを初期化
unset($_SESSION['post']);

// リダイレクト
redirect('/admin/setting?target=' . $_GET['target'] . '&ok=post');
