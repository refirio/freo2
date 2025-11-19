<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        error('不正なアクセスです。');
    }

    // 入力データを検証
    if (!empty($_POST['directory']) && (!preg_match('/^[\w\-\.\/]+$/', $_POST['directory']) || preg_match('/\.\./', $_POST['directory']) || preg_match('/\/\//', $_POST['directory']))) {
        error('ディレクトリの指定が不正です。');
    }

    if (isset($_POST['name']) && isset($_POST['rename'])) {
        if (!preg_match('/^[\w\-\.]+$/', $_POST['name']) || !preg_match('/^[\w\-\.]+$/', $_POST['rename'])) {
            error('名前の指定が不正です。');
        }
        if ($GLOBALS['authority']['power'] < 3) {
            if (!preg_match('/\.(' . implode('|', $GLOBALS['config']['media_author_ext']) . ')$/i', $_POST['name'])) {
                error('指定された拡張子のファイルは扱えません。');
            }
            if (!preg_match('/\.(' . implode('|', $GLOBALS['config']['media_author_ext']) . ')$/i', $_POST['rename'])) {
                error('指定された拡張子は使用できません。');
            }
        }

        // 登録データ
        $_SESSION['post']['media'] = [
            'directory' => rtrim($_POST['directory'], '/'),
            'name'      => $_POST['name'],
            'rename'    => $_POST['rename'],
        ];
    } elseif (isset($_POST['directory'])) {
        // 登録データ
        $_SESSION['post']['media'] = [
            'directory' => rtrim($_POST['directory'], '/'),
        ];
    } else {
        error('不正なアクセスです。');
    }

    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        ok();
    } else {
        // フォワード
        forward('/admin/media_post');
    }
}

// ディレクトリを取得
if (!isset($_GET['directory'])) {
    $_GET['directory'] = '';
}

// タイトル
if (empty($_GET['type'])) {
    $_view['title'] = 'メディア登録';
} else {
    $_view['title'] = 'メディア編集';
}
