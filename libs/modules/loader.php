<?php

/*********************************************************************

 Functions for Loader

*********************************************************************/

/**
 * キャッシュを無視してファイルを読み込み
 *
 * @param  string $file
 * @param  bool   $nocache
 * @return string
 */
function loader_file($file, $nocache = true)
{
    if ($nocache) {
        if ($regexp = regexp_match('(.+)\?', $file)) {
            $filename  = $regexp[1];
            $connector = '&';
        } else {
            $filename  = $file;
            $connector = '?';
        }
        if (file_exists($filename)) {
            $file .= $connector . filemtime($filename);
        }
    }

    return $file;
}

/**
 * キャッシュを無視してCSSファイルを読み込み
 *
 * @param  string $file
 * @param  bool   $nocache
 * @return string
 */
function loader_css($file, $nocache = true)
{
    return loader_file('css/' . $file, $nocache);
}

/**
 * キャッシュを無視してJSファイルを読み込み
 *
 * @param  string $file
 * @param  bool   $nocache
 * @return string
 */
function loader_js($file, $nocache = true)
{
    return loader_file('js/' . $file, $nocache);
}
