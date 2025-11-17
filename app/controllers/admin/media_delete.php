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

// 入力データを検証
if (!isset($_GET['directory']) || !preg_match('/^[\w\-\/]+$/', $_GET['directory'])) {
    $_GET['directory'] = '';
}
if (!isset($_POST['directory']) || !preg_match('/^[\w\-\/]+$/', $_POST['directory'])) {
    $_POST['directory'] = '';
}
if (!isset($_POST['name']) || !preg_match('/^[\w\-\/\.]+$/', $_POST['name'])) {
    $_POST['name'] = '';
}

$directory = $_GET['directory'];

if (!empty($_POST['name'])) {
    // メディアを削除
    service_storage_remove($GLOBALS['config']['file_target']['media'] . $_POST['directory'] . '/' . $_POST['name']);

    // リダイレクト
    redirect('/admin/media?ok=delete' . ($_POST['directory'] === '' ? '' : '&directory=' . $_POST['directory']) . (empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']));
} elseif (empty($_POST['medias'])) {
    // リダイレクト
    redirect('/admin/media?warning=delete' . ($directory === '' ? '' : '&directory=' . $directory) . (empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']));
} elseif (empty($_POST['confirm'])) {
    // メディアを削除
    foreach ($_POST['medias'] as $media) {
        service_storage_remove($GLOBALS['config']['file_target']['media'] . $directory . '/' . $media);
    }

    // リダイレクト
    redirect('/admin/media?ok=delete' . ($directory === '' ? '' : '&directory=' . $directory) . (empty($_REQUEST['_type']) ? '' : '&_type=' . $_REQUEST['_type']));
} else {
    // メディアを取得
    $_view['medias'] = $_POST['medias'];

    // タイトル
    $_view['title'] = 'メディア削除';
}
