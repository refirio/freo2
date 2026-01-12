<?php

import('app/services/storage.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。', ['token' => token('create')]);
    }

    // アップロード項目数を取得
    $file_count = count($_FILES['medias']['tmp_name']);

    // 入力データを検証＆登録
    $files = [];
    for ($i = 0; $i < $file_count; $i++) {
        if (is_uploaded_file($_FILES['medias']['tmp_name'][$i])) {
            if ($GLOBALS['authority']['power'] < 3) {
                if (!preg_match('/\.(' . implode('|', $GLOBALS['config']['media_author_ext']) . ')$/i', $_FILES['medias']['name'][$i])) {
                    error('指定された拡張子は使用できません。', ['token' => token('create')]);
                }
            }
            if (service_storage_put($GLOBALS['config']['file_target']['temp'] . session_id() . '_' . $_FILES['medias']['name'][$i], file_get_contents($_FILES['medias']['tmp_name'][$i])) === false) {
                error('ファイルを保存できません。', ['token' => token('create')]);
            }

            $files[] = $_FILES['medias']['name'][$i];

            $_SESSION['medias'] = $files;
        }
    }

    ok(null, ['files' => $files, 'token' => token('create')]);
} else {
    error('不正なアクセスです。', ['token' => token('create')]);
}
