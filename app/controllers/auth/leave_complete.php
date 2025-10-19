<?php

// 機能の利用を確認
if (empty($GLOBALS['setting']['user_use_register'])) {
    error('自身のユーザ削除は許可されていません。');
}

// タイトル
$_view['title'] = 'ユーザ情報削除完了';
