<?php

// 対象を検証
if (!preg_match('/^[\w\-]+$/', $_GET['target'])) {
    error('不正なアクセスです。', ['token' => token('create')]);
}
if (!preg_match('/^[\w\-]+$/', $_GET['key'])) {
    error('不正なアクセスです。', ['token' => token('create')]);
}

// 形式を検証
if (!preg_match('/^[\w\-]+$/', $_GET['format'])) {
    error('不正なアクセスです。', ['token' => token('create')]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。', ['token' => token('create')]);
    }

    // 入力データを検証＆登録
    if (is_array($_FILES[$_GET['key']]['tmp_name'])) {
        // ファイル一括アップロード
        $files = [];
        foreach ($_FILES[$_GET['key']]['tmp_name'] as $index => $tmp_name) {
            if (is_uploaded_file($tmp_name)) {
                $names = [];
                $ext   = null;
                if (empty($GLOBALS['config']['file_permission'][$_GET['format']])) {
                    $ext = '*';
                } else {
                    foreach (array_keys($GLOBALS['config']['file_permission'][$_GET['format']]) as $permission) {
                        $names[] = $GLOBALS['config']['file_permission'][$_GET['format']][$permission]['name'];

                        if (preg_match($GLOBALS['config']['file_permission'][$_GET['format']][$permission]['regexp'], $_FILES[$_GET['key']]['name'][$index])) {
                            $ext = $GLOBALS['config']['file_permission'][$_GET['format']][$permission]['ext'];

                            break;
                        }
                    }
                }
                if ($ext === null) {
                    error('アップロードできるファイル形式は' . implode('、', $names) . 'のみです。', ['token' => token('create')]);
                }

                $_SESSION['file'][$_GET['target']][$_GET['key']][$index] = [
                    'name' => $_FILES[$_GET['key']]['name'][$index],
                    'data' => file_get_contents($tmp_name),
                ];

                $files[] = [
                    'name' => $_FILES[$_GET['key']]['name'][$index],
                    'data' => MAIN_FILE . '/admin/file?_type=image&target=' . $_GET['target'] . '&key=' . $_GET['key'] . '&index=' . $index . '&format=' . $_GET['format'],
                ];
            }
        }
    } else {
        // ファイルアップロード
        if (is_uploaded_file($_FILES[$_GET['key']]['tmp_name'])) {
            $names = [];
            $ext   = null;
            if (empty($GLOBALS['config']['file_permission'][$_GET['format']])) {
                $ext = '*';
            } else {
                foreach (array_keys($GLOBALS['config']['file_permission'][$_GET['format']]) as $permission) {
                    $names[] = $GLOBALS['config']['file_permission'][$_GET['format']][$permission]['name'];

                    if (preg_match($GLOBALS['config']['file_permission'][$_GET['format']][$permission]['regexp'], $_FILES[$_GET['key']]['name'])) {
                        $ext = $GLOBALS['config']['file_permission'][$_GET['format']][$permission]['ext'];

                        break;
                    }
                }
            }
            if ($ext === null) {
                error('アップロードできるファイル形式は' . implode('、', $names) . 'のみです。', ['token' => token('create')]);
            }

            $_SESSION['file'][$_GET['target']][$_GET['key']] = [
                'name' => $_FILES[$_GET['key']]['name'],
                'data' => file_get_contents($_FILES[$_GET['key']]['tmp_name']),
            ];

            $files[] = [
                'name' => $_FILES[$_GET['key']]['name'],
                'data' => MAIN_FILE . '/admin/file?_type=image&target=' . $_GET['target'] . '&key=' . $_GET['key'] . '&format=' . $_GET['format'],
            ];
        }
    }

    ok(null, ['files' => $files, 'token' => token('create')]);
} else {
    error('不正なアクセスです。', ['token' => token('create')]);
}
