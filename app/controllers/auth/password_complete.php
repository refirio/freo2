<?php

// 権限を確認
if (isset($GLOBALS['authority'])) {
    // リダイレクト
    redirect('/auth/');
}

// タイトル
$_view['title'] = 'パスワード再登録';
