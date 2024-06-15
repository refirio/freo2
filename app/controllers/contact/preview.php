<?php

// 投稿データを確認
if (empty($_SESSION['post']['contact'])) {
    // リダイレクト
    redirect('/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。');
    }

    // フォワード
    forward('/contact/post');
} else {
    $_view['contact'] = $_SESSION['post']['contact'];
}

// タイトル
$_view['title'] = 'お問い合わせ確認';
