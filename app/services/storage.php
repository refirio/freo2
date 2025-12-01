<?php

/**
 * 初期化
 *
 * @param array|null $config
 */
function service_storage_init($config = [])
{
    if ($GLOBALS['config']['storage_type'] === 's3') {
        import('libs/modules/s3.php');

        s3_init($config);
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        import('libs/modules/file.php');
        import('libs/modules/directory.php');
    }
}

/**
 * オブジェクトを確認
 *
 * @param string $key
 *
 * @return bool
 */
function service_storage_exist($key)
{
    $result = false;
    if ($GLOBALS['config']['storage_type'] === 's3') {
        $result = s3_exist($key);
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        $result = is_file($key);
    }

    return $result;
}

/**
 * オブジェクトを取得
 *
 * @param string $key
 *
 * @return string
 */
function service_storage_get($key)
{
    $result = null;
    if ($GLOBALS['config']['storage_type'] === 's3') {
        $result = s3_get($key);
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        $result = file_get_contents($key);
    }

    return $result;
}

/**
 * オブジェクトを保存
 *
 * @param string $key
 * @param string $body|null
 *
 * @return bool
 */
function service_storage_put($key, $body = null)
{
    $result = false;
    if ($GLOBALS['config']['storage_type'] === 's3') {
        $result = s3_put($key, $body);
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        if (preg_match('/\/$/', $key)) {
            $result = directory_mkdir($key);
        } else {
            $result = file_put_contents($key, $body);
        }
    }

    return $result;
}

/**
 * オブジェクトを複製
 *
 * @param string $key
 * @param string $source
 *
 * @return bool
 */
function service_storage_copy($key, $source)
{
    $result = false;
    if ($GLOBALS['config']['storage_type'] === 's3') {
        $result = s3_copy($key, $source);
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        if (preg_match('/\/$/', $key)) {
            // do nothing.
        } else {
            $result = copy($source, $key);
        }
    }

    return $result;
}

/**
 * オブジェクトを移動
 *
 * @param string $key
 * @param string $source
 *
 * @return bool
 */
function service_storage_rename($key, $source)
{
    $result = false;
    if ($GLOBALS['config']['storage_type'] === 's3') {
        $result = s3_copy($key, $source);
        $result = s3_remove($source);
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        if (preg_match('/\/$/', $key)) {
            // do nothing.
        } else {
            $result = rename($source, $key);
        }
    }

    return $result;
}

/**
 * オブジェクトを削除
 *
 * @param string $key
 *
 * @return bool
 */
function service_storage_remove($key)
{
    $result = false;
    if ($GLOBALS['config']['storage_type'] === 's3') {
        $result = s3_remove($key);
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        if (preg_match('/\/$/', $key)) {
            $result = directory_rmdir($key);
        } else {
            $result = unlink($key);
        }
    }

    return $result;
}

/**
 * オブジェクトを一覧
 *
 * @param string $key
 *
 * @return array
 */
function service_storage_list($key)
{
    $directories = [];
    $files       = [];
    if ($GLOBALS['config']['storage_type'] === 's3') {
        $list = s3_list($key);

        if (!empty($list['CommonPrefixes'])) {
            foreach ($list['CommonPrefixes'] as $prefix) {
                if (preg_match('/^' . preg_quote($key, '/') . '(\w+)\//', $prefix['Prefix'], $matches)) {
                    $name = $matches[1];
                } else {
                    $name = $prefix['Prefix'];
                }
                $directories[] = [
                    'type'     => 'directory',
                    'name'     => $name,
                    'modified' => null,
                    'size'     => null,
                ];
            }
        }

        if (!empty($list['Contents'])) {
            foreach ($list['Contents'] as $content) {
                if (preg_match('/^' . preg_quote($key, '/') . '(.+)/', $content['Key'], $matches)) {
                    $name = $matches[1];
                } else {
                    $name = $content['Key'];
                }
                $files[] = [
                    'type'     => 'file',
                    'name'     => $name,
                    'modified' => $content['LastModified'],
                    'size'     => $content['Size'],
                ];
            }
        }
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        if ($dh = opendir($key)) {
            while (($entry = readdir($dh)) !== false) {
                if ($entry == '.' || $entry == '..' || $entry == '.gitkeep') {
                    continue;
                }

                if (is_dir($key . $entry)) {
                    $directories[] = [
                        'type'     => 'directory',
                        'name'     => $entry,
                        'modified' => null,
                        'size'     => null,
                    ];
                } else {
                    $files[] = [
                        'type'     => 'file',
                        'name'     => $entry,
                        'modified' => filemtime($key . $entry),
                        'size'     => filesize($key . $entry),
                    ];
                }
            }
        }
    }

    return array_merge($directories, $files);
}
