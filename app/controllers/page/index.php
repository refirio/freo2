<?php

import('app/services/entry.php');

// 表示対象を取得
if (isset($_params[1])) {
    $_GET['code'] = implode('/', array_slice($_params, 1));
}
if (!isset($_GET['code'])) {
    error('不正なアクセスです。');
}

// ページを取得
$pages = service_entry_select_published('page', [
    'where' => [
        'entries.code = :code',
        [
            'code' => $_GET['code'],
        ],
    ],
]);
if (empty($pages)) {
    warning('ページが見つかりません。');
} else {
    $_view['page'] = $pages[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。');
    }

    // パスワード認証
    if ($_POST['password'] === $pages[0]['password']) {
        $_SESSION['entry_passwords'][$pages[0]['id']] = true;

        // リダイレクト
        redirect('/page/' . $pages[0]['code']);
    } else {
        warning('パスワードが違います。');
    }
}

// タイトル
if (isset($_view['page']['title'])) {
    $_view['title'] = $_view['page']['title'];
} else {
    $_view['title'] = 'ページ';
}
