<?php

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('訪問者によるユーザー新規登録は許可されていません。');
}

// タイトル
$_view['title'] = 'ユーザー登録完了';
