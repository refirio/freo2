<?php

import('app/services/update.php');

// 未実行のマイグレーション
$update_targets = service_update_pending();

// DB の更新の結果
$_view['update_results'] = [];

// 不要なファイルの削除の結果
$_view['update_remove_results'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        error('不正なアクセスです。');
    }

    if (isset($_POST['exec']) && $_POST['exec'] === 'migrate') {
        // 二重送信などで、すでに更新が済んでいる
        if (empty($update_targets)) {
            redirect('/admin/update');
        }

        // DB を更新
        $_view['update_results'] = service_update_migrate($update_targets);

        // 成功したら、完了の画面へ
        if (!service_update_failed($_view['update_results'])) {
            redirect('/admin/update?ok=migrate');
        }

        // 失敗したら、結果をこの画面に出す（残っている更新を取り直す）
        $update_targets = service_update_pending();
    } elseif (isset($_POST['exec']) && $_POST['exec'] === 'remove') {
        // DB の更新が残っているうちは消さない
        if (!empty($update_targets)) {
            redirect('/admin/update');
        }

        // 不要なファイルを削除
        $_view['update_remove_results'] = service_update_remove();

        // すべて消せたら、完了の画面へ（消せなかったものがあれば、この画面に出す）
        if (empty($_view['update_remove_results']['failed'])) {
            redirect('/admin/update?ok=remove');
        }
    } else {
        error('不正なアクセスです。');
    }
}

$_view['update_targets'] = $update_targets;

// サーバーに残っている削除済みファイル（DB の更新が済んでから扱う）
$_view['update_removed'] = empty($update_targets) ? service_update_removed() : ['delete' => [], 'keep' => []];

// タイトル
$_view['title'] = 'システムの更新';
