<?php

// 表示方法を検証
if (!isset($_GET['view'])) {
    $_GET['view'] = 'default';
}

// 対象を検証
if (!preg_match('/^[\w\-]+$/', $_GET['target'])) {
    error('不正なアクセスです。');
}
if (!preg_match('/^[\w\-]+$/', $_GET['key'])) {
    error('不正なアクセスです。');
}

// 形式を検証
if (!preg_match('/^[\w\-]+$/', $_GET['format'])) {
    error('不正なアクセスです。');
}

// ワンタイムトークン
if (!token('check', $_GET['view'])) {
    error('不正なアクセスです。');
}

// 画像を削除
$_SESSION['file'][$_GET['target']][$_GET['key']]['delete'] = true;

if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
    ok();
} else {
    // リダイレクト
    redirect('/admin/file_upload?ok=delete&view=' . $_GET['view'] . '&target=' . $_GET['target'] . '&key=' . $_GET['key'] . '&format=' . $_GET['format'] . (isset($_GET['id']) ? '&id=' . intval($_GET['id']): ''));
}
