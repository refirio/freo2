<?php

import('app/services/storage.php');
import('libs/modules/file.php');

/**
 * メディアの名前が正しいかを確認
 *
 * @param string $name
 * @param bool   $directory
 *
 * @return bool
 */
function service_media_name_valid($name, $directory = false)
{
    if (!is_string($name) || $name === '') {
        return false;
    }

    // 相対指定（.. や . だけの名前）は許可しない
    if (preg_match('/(^|\/)\.{1,2}(\/|$)/', $name)) {
        return false;
    }

    // ディレクトリ（末尾が /）を許可する
    if ($directory === true) {
        return (bool) preg_match('/^[\w\-\.]+\/?$/', $name);
    }

    return (bool) preg_match('/^[\w\-\.]+$/', $name);
}

/**
 * サムネイルのキーを取得
 *
 * @param string $path
 *
 * @return string
 */
function service_media_thumbnail_key($path)
{
    // メディアと同じ構成で、メディアの領域外に置く（メディアの一覧に表示されないようにするため）
    return $GLOBALS['config']['file_target']['thumbnail'] . 'medias/' . $path;
}

/**
 * サムネイルを作成
 *
 * @param string $path
 *
 * @return bool
 */
function service_media_thumbnail_create($path)
{
    // 画像以外は作成しない
    if (!preg_match('/\.(gif|jpeg|jpg|jpe|png)$/i', $path, $matches)) {
        return false;
    }
    $extension = strtolower($matches[1]);

    // GDが使えない環境では作成しない（GDが有効でも、形式ごとの関数はビルドによって無いことがある）
    $functions = [
        'gif'  => ['imagecreatefromgif', 'imagegif'],
        'jpeg' => ['imagecreatefromjpeg', 'imagejpeg'],
        'jpg'  => ['imagecreatefromjpeg', 'imagejpeg'],
        'jpe'  => ['imagecreatefromjpeg', 'imagejpeg'],
        'png'  => ['imagecreatefrompng', 'imagepng'],
    ];
    foreach (array_merge(['imagecreatetruecolor', 'imagecopyresampled'], $functions[$extension]) as $function) {
        if (!function_exists($function)) {
            return false;
        }
    }

    $key = $GLOBALS['config']['file_target']['media'] . $path;
    if (!service_storage_exist($key)) {
        return false;
    }

    // ローカルの一時ファイルに保存（file_resize() はローカルのファイルを拡張子で判別して扱うため）
    $temp = tempnam(sys_get_temp_dir(), 'media_');
    if ($temp === false) {
        return false;
    }
    $original  = $temp . '_original.' . $extension;
    $thumbnail = $temp . '_thumbnail.' . $extension;

    $result = file_put_contents($original, service_storage_get($key)) !== false;

    // 画像の展開に必要なメモリ（1ピクセルあたり約4バイト）が足りなければ作成しない
    if ($result) {
        $size = getimagesize($original);
        if ($size === false) {
            $result = false;
        } else {
            $limit = ini_get('memory_limit');
            if ($limit !== false && $limit !== '' && $limit !== '-1') {
                // 単位（128Mなど）をバイトに換算する（break せずに下の単位まで掛ける）
                $bytes = (int) $limit;
                switch (strtoupper(substr($limit, -1))) {
                    case 'G':
                        $bytes *= 1024;
                    case 'M':
                        $bytes *= 1024;
                    case 'K':
                        $bytes *= 1024;
                }
                if (memory_get_usage() + $size[0] * $size[1] * 4 + 16 * 1024 * 1024 > $bytes) {
                    $result = false;
                }
            }
        }
    }

    // サムネイルを作成して保存
    if ($result) {
        $result = file_resize($original, $thumbnail, $GLOBALS['config']['resize_width'], $GLOBALS['config']['resize_height'], $GLOBALS['config']['resize_quality']);
    }
    if ($result) {
        $thumbnail_key = service_media_thumbnail_key($path);

        service_storage_put(dirname($thumbnail_key) . '/');

        $result = service_storage_put($thumbnail_key, file_get_contents($thumbnail)) !== false;
    }

    // 一時ファイルを削除
    foreach ([$temp, $original, $thumbnail] as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }

    return $result;
}

/**
 * サムネイルを削除
 *
 * @param string $path
 *
 * @return bool
 */
function service_media_thumbnail_remove($path)
{
    $key = service_media_thumbnail_key($path);

    // サムネイルの無いファイルは何もしない（ディレクトリは中身ごと削除する）
    if (!preg_match('/\/$/', $key) && !service_storage_exist($key)) {
        return true;
    }

    return service_storage_remove($key);
}

/**
 * サムネイルを一覧
 *
 * @param string $directory
 *
 * @return array
 */
function service_media_thumbnail_list($directory)
{
    $key = service_media_thumbnail_key($directory === '' ? '' : $directory . '/');

    // サムネイルを一度も作成していないディレクトリ
    if ($GLOBALS['config']['storage_type'] === 'file' && !is_dir($key)) {
        return [];
    }

    $names = [];
    foreach (service_storage_list($key) as $thumbnail) {
        if ($thumbnail['type'] === 'file') {
            $names[] = $thumbnail['name'];
        }
    }

    return $names;
}
