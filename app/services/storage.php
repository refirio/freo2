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
 * オブジェクトを出力（プログラムを通して表示する）
 *
 * 出力したら終了する。オブジェクトが無ければ false を返す
 *
 * @param string $key
 *
 * @return bool
 */
function service_storage_output($key)
{
    // マイムタイプ（アップロードを許可している形式から判断する）
    $mime = 'application/octet-stream';
    foreach ($GLOBALS['config']['file_permission']['file'] as $permission) {
        if (preg_match($permission['regexp'], $key)) {
            $mime = $permission['mime'];

            break;
        }
    }

    if ($GLOBALS['config']['storage_type'] === 's3') {
        // 署名付きURLへリダイレクトする（PHP で中継しない。オブジェクトの有無は確認しないので、無ければ S3 がエラーを返す）
        $url = s3_url($key, $GLOBALS['config']['storage_signed_expire'], $mime);
        if ($url === false) {
            return false;
        }

        // 同じ画像を見返すたびに URL を作り直さないよう、署名の期限の半分までは、ブラウザにリダイレクトを覚えさせる
        header('Cache-Control: private, max-age=' . intval($GLOBALS['config']['storage_signed_expire'] / 2));
        header('Location: ' . $url, true, 302);
        exit;
    } elseif ($GLOBALS['config']['storage_type'] === 'file') {
        if (!is_file($key)) {
            return false;
        }

        // ブラウザのキャッシュが最新なら、内容を送らない
        $modified = filemtime($key);
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $modified) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        // 見る人によって表示できるかが変わるので、共有のキャッシュには置かせず、ブラウザにも毎回確認させる
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($key));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modified) . ' GMT');
        header('Cache-Control: private, no-cache');

        // 出力バッファがあると内容をすべてメモリに溜めるので、外してから流す
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        readfile($key);
        exit;
    }

    return false;
}

/**
 * 公開のURLを開いたときのステータスを取得（署名を付けずに開けるかの確認に使う）
 *
 * 確認できなければ null を返す
 *
 * @param string $url
 * @param int    $timeout
 *
 * @return int|null
 */
function service_storage_status($url, $timeout = 3)
{
    $status = null;

    if (function_exists('curl_init')) {
        // 中身は要らないので、ヘッダーだけを取得する
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_NOBODY         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT        => $timeout,
        ]);
        if (curl_exec($curl) !== false) {
            $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        }
    } elseif (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'method'        => 'HEAD',
                'timeout'       => $timeout,
                'ignore_errors' => true,
            ],
        ]);

        $headers = @get_headers($url, false, $context);
        if ($headers && preg_match('/^HTTP\/\S+\s+(\d{3})/', $headers[0], $matches)) {
            $status = $matches[1];
        }
    }

    return $status ? intval($status) : null;
}

/**
 * 古い一時ファイルを削除（アップロードしたまま登録されなかったもの）
 *
 * @return void
 */
function service_storage_temp_clean()
{
    $limit = localdate() - $GLOBALS['config']['temp_retention'];

    foreach (service_storage_list($GLOBALS['config']['file_target']['temp']) as $file) {
        if ($file['type'] !== 'file') {
            continue;
        }

        // S3 の更新日時は日時のオブジェクトで返るので、タイムスタンプにそろえる
        $modified = is_numeric($file['modified']) ? intval($file['modified']) : strtotime((string)$file['modified']);

        if ($modified !== false && $modified < $limit) {
            service_storage_remove($GLOBALS['config']['file_target']['temp'] . $file['name']);
        }
    }
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
                if ($entry == '.' || $entry == '..' || $entry == '.gitkeep' || $entry == '.htaccess') {
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

    // 名前で並べ替える（ファイルは readdir() の順、S3 はキーのバイト順で返るため、どちらでも同じ並びにする）
    $compare = function ($a, $b) {
        return strnatcasecmp($a['name'], $b['name']);
    };
    usort($directories, $compare);
    usort($files, $compare);

    return array_merge($directories, $files);
}
