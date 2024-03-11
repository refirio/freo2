<?php

import('app/services/storage.php');

// 表示方法を検証
if (!isset($_GET['view'])) {
    $_GET['view'] = 'default';
}

// 対象を検証
if (!preg_match('/^[\w\-]+$/', $_GET['target'])) {
    error('不正なアクセスです。');
}
if (!preg_match('/^[\w\-]+$/', $_GET['key'])) {
    error('不正なアクセスです。');
}

// 形式を検証
if (!preg_match('/^[\w\-]+$/', $_GET['format'])) {
    error('不正なアクセスです。');
}

$file    = null;
$mime    = null;
$content = null;

if (empty($_SESSION['file'][$_GET['target']][$_GET['key']]['delete'])) {
    if (isset($_SESSION['file'][$_GET['target']][$_GET['key']]['name']) && isset($_SESSION['file'][$_GET['target']][$_GET['key']]['data'])) {
        // セッションからファイルを取得
        foreach (array_keys($GLOBALS['config']['file_permissions'][$_GET['format']]) as $permission) {
            if (preg_match($GLOBALS['config']['file_permissions'][$_GET['format']][$permission]['regexp'], $_SESSION['file'][$_GET['target']][$_GET['key']]['name'])) {
                // マイムタイプ
                $mime = $GLOBALS['config']['file_permissions'][$_GET['format']][$permission]['mime'];

                break;
            }
        }

        // コンテンツ
        $content = $_SESSION['file'][$_GET['target']][$_GET['key']]['data'];
    } elseif (($_GET['target'] === 'entry' || $_GET['target'] === 'page') && isset($_GET['id'])) {
        // 登録内容からファイルを取得
        $results = [];
        if ($_GET['target'] === 'entry') {
            $results = model('select_entries', [
                'where' => [
                    'id = :id',
                    [
                        'id' => $_GET['id'],
                    ],
                ],
            ]);
        } elseif ($_GET['target'] === 'page') {
            $results = model('select_pages', [
                'where' => [
                    'id = :id',
                    [
                        'id' => $_GET['id'],
                    ],
                ],
            ]);
        }
        if (empty($results)) {
            warning('データが見つかりません。');
        } else {
            $result = $results[0];
        }

        $file = $GLOBALS['config']['file_targets'][$_GET['target']] . intval($_GET['id']) . '/' . $result[$_GET['key']];
    } elseif ($_GET['target'] === 'field' && isset($_GET['id'])) {
        list($target, $id, $key) = explode('_', $_GET['key']);

        $results = model('select_fields', [
            'select' => 'target',
            'where'  => [
                'id = :id',
                [
                    'id' => $key,
                ],
            ],
        ]);
        if (empty($results)) {
            warning('データが見つかりません。');
        } else {
            $target = $results[0]['target'];
        }

        if ($target == 'entry') {
            $results = model('select_field_sets', [
                'where' => [
                    'field_id = :field_id AND entry_id = :entry_id',
                    [
                        'field_id' => $key,
                        'entry_id' => $_GET['id'],
                    ],
                ],
            ]);
            if (!empty($results)) {
                $result = $results[0];

                $file = $GLOBALS['config']['file_targets']['field'] . $result['entry_id'] . '_' . $result['field_id'] . '/' . $result['text'];
            }
        } elseif ($target == 'page') {
            $results = model('select_field_sets', [
                'where' => [
                    'field_id = :field_id AND page_id = :page_id',
                    [
                        'field_id' => $key,
                        'page_id'  => $_GET['id'],
                    ],
                ],
            ]);
            if (!empty($results)) {
                $result = $results[0];

                $file = $GLOBALS['config']['file_targets']['field'] . $result['page_id'] . '_' . $result['field_id'] . '/' . $result['text'];
            }
        }
    }
}

if (service_storage_exist($file)) {
    foreach (array_keys($GLOBALS['config']['file_permissions'][$_GET['format']]) as $permission) {
        if (preg_match($GLOBALS['config']['file_permissions'][$_GET['format']][$permission]['regexp'], $result[$_GET['key']])) {
            // マイムタイプ
            $mime = $GLOBALS['config']['file_permissions'][$_GET['format']][$permission]['mime'];

            break;
        }
    }

    // コンテンツ
    $content = service_storage_get($file);
}

if (isset($_GET['_type']) && $_GET['_type'] === 'json') {
    // ファイル情報を取得
    if ($content === null) {
        $width  = null;
        $height = null;
    } else {
        list($width, $height) = getimagesize('data:application/octet-stream;base64,' . base64_encode($content));
    }

    header('Content-Type: application/json; charset=' . MAIN_CHARSET);

    echo json_encode([
        'status' => 'OK',
        'mime'   => $mime,
        'width'  => $width,
        'height' => $height,
    ]);
} else {
    // ファイルを取得
    if ($mime === null) {
        $mime = 'image/png';
    }
    if ($content === null) {
        $mime    = 'image/png';
        $content = file_get_contents($GLOBALS['config']['file_dummies'][$_GET['format']]);
    } elseif (!empty($GLOBALS['config']['file_alternatives'][$_GET['format']])) {
        $mime    = 'image/png';
        $content = file_get_contents($GLOBALS['config']['file_alternatives'][$_GET['format']]);
    }

    header('Content-type: ' . $mime);

    echo $content;
}

exit;
