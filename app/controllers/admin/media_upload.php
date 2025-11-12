<?php

import('app/services/storage.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。', ['token' => token('create')]);
    }

    // アップロード項目数を取得
    $file_count = count($_FILES['media']['tmp_name']);

    // 入力データを検証＆登録
    $files = [];
    for ($i = 0; $i < $file_count; $i++) {
        if (is_uploaded_file($_FILES['media']['tmp_name'][$i])) {
            if (service_storage_put($GLOBALS['config']['file_target']['temp'] . session_id() . '_' . $_FILES['media']['name'][$i], file_get_contents($_FILES['media']['tmp_name'][$i])) === false) {
                error('ファイルを保存できません。', ['token' => token('create')]);
            }

            $files[] = $_FILES['media']['name'][$i];

            $_SESSION['media'] = $files;
        }
    }

    ok(null, ['files' => $files, 'token' => token('create')]);
} else {
    error('不正なアクセスです。', ['token' => token('create')]);
}
