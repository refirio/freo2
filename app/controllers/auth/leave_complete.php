<?php

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('自身のユーザー削除は許可されていません。');
}

// タイトル
$_view['title'] = 'ユーザー情報削除完了';
