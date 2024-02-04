<?php

/**
 * 初期化
 *
 * @param array|null $config
 */
function service_storage_init($config = [])
{
    if ($GLOBALS['config']['storage_type'] === 's3') {
        import('libs/plugins/s3.php');

        s3_init($config);
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        import('libs/plugins/file.php');
        import('libs/plugins/directory.php');
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
 * @return bool
 */
function service_storage_get($key)
{
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
 * オブジェクトを削除
 *
 * @param string $key
 *
 * @return bool
 */
function service_storage_remove($key)
{
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
