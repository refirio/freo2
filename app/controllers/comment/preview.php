<?php

// 投稿データを確認
if (empty($_SESSION['post']['comment'])) {
    // リダイレクト
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。');
    }

    // フォワード
    forward('/comment/post');
} else {
    $_view['comment'] = $_SESSION['post']['comment'];

    // エントリーの型を取得
    $entries = model('select_entries', [
        'where' => [
            'entries.id = :id',
            [
                'id' => $_SESSION['post']['comment']['entry_id'],
            ],
        ],
    ], [
        'associate' => true,
    ]);

    // エントリーを取得
    $entries = service_entry_select_published($entries[0]['type_code'], [
        'where' => [
            'entries.id = :id',
            [
                'id' => $_SESSION['post']['comment']['entry_id'],
            ],
        ],
    ]);
    if (empty($entries)) {
        warning('エントリーが見つかりません。');
    } else {
        $_view['entry'] = $entries[0];
    }
}

// タイトル
$_view['title'] = 'コメント投稿確認';
