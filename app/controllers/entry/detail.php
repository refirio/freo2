<?php

import('app/services/entry.php');

// 表示対象を取得
if (isset($_params[2])) {
    $_GET['code'] = $_params[2];
}
if (!isset($_GET['code'])) {
    error('不正なアクセスです。');
}

// エントリーを取得
$entries = service_entry_select_published('entry', [
    'where' => [
        'entries.code = :code',
        [
            'code' => $_GET['code'],
        ],
    ],
]);
if (empty($entries)) {
    warning('エントリーが見つかりません。');
} else {
    $_view['entry'] = $entries[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。');
    }

    // パスワード認証
    if ($_POST['password'] === $entries[0]['password']) {
        $_SESSION['entry_passwords'][$entries[0]['id']] = true;

        // リダイレクト
        redirect('/entry/detail/' . $entries[0]['code']);
    } else {
        warning('パスワードが違います。');
    }
}

// タイトル
if (isset($_view['entry']['title'])) {
    $_view['title'] = $_view['entry']['title'];
} else {
    $_view['title'] = 'エントリー';
}
