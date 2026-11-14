<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// 実際のストレージ（S3など）に書き込まないよう、テスト中はファイルに固定する
$GLOBALS['config']['storage_type'] = 'file';

// ライブラリを読み込み
service('media.php');
service_storage_init();

// テスト用のディレクトリ（メディアのディレクトリからの相対パス）
$directory = 'test_thumbnail';

$media_directory     = $GLOBALS['config']['file_target']['media'] . $directory . '/';
$thumbnail_directory = service_media_thumbnail_key($directory . '/');

// 前回の実行で残ったファイルを削除
directory_rmdir($media_directory);
directory_rmdir($thumbnail_directory);
directory_mkdir($media_directory);

// GDが使えるかを確認（サムネイルの確認は、テスト用の画像の作成も含めてGDに依存する）
$gd = true;
foreach ([
    'imagecreatetruecolor',
    'imagecopyresampled',
    'imagefilledrectangle',
    'imagecolorallocate',
    'imagecreatefromgif',
    'imagegif',
    'imagecreatefromjpeg',
    'imagejpeg',
    'imagecreatefrompng',
    'imagepng',
] as $function) {
    if (!function_exists($function)) {
        $gd = false;
        break;
    }
}

// 名前の確認（通常のファイル名）テスト
{
    // 確認
    $name   = service_media_name_valid('photo.jpg');
    $symbol = service_media_name_valid('photo-01_2.jpg');
    $upper  = service_media_name_valid('PHOTO.JPG');

    // 結果（許可されること）
    test_equals('valid media name', $name, true);
    test_equals('valid media name (symbol)', $symbol, true);
    test_equals('valid media name (upper)', $upper, true);
}

// 名前の確認（日本語・空白）テスト
{
    // 確認
    $japanese = service_media_name_valid('写真.jpg');
    $space    = service_media_name_valid('my photo.jpg');

    // 結果（S3でそのままキーにするとS3Exceptionになるため、許可されないこと）
    test_equals('valid media name (japanese)', $japanese, false);
    test_equals('valid media name (space)', $space, false);
}

// 名前の確認（ファイル名に含まれる連続したドット）テスト
{
    // 確認
    $dots = service_media_name_valid('photo..jpg');

    // 結果（相対指定ではないので許可されること）
    test_equals('valid media name (dots)', $dots, true);
}

// 名前の確認（ディレクトリの区切り）テスト
{
    // 確認
    $slash      = service_media_name_valid('images/photo.jpg');
    $slash_last = service_media_name_valid('images/');

    // 結果（メディアの領域外を指定されないよう、許可されないこと）
    test_equals('valid media name (slash)', $slash, false);
    test_equals('valid media name (directory)', $slash_last, false);
}

// 名前の確認（ディレクトリを許可した場合）テスト
{
    // 確認
    $slash_last = service_media_name_valid('images/', true);
    $slash      = service_media_name_valid('images/photo.jpg', true);

    // 結果（末尾の / だけ許可されること）
    test_equals('valid media name (directory allowed)', $slash_last, true);
    test_equals('valid media name (directory allowed slash)', $slash, false);
}

// 名前の確認（相対指定）テスト
{
    // 確認
    $parent      = service_media_name_valid('..');
    $parent_last = service_media_name_valid('../', true);
    $parent_path = service_media_name_valid('../../index.php', true);
    $current     = service_media_name_valid('.');

    // 結果（許可されないこと）
    test_equals('valid media name (parent)', $parent, false);
    test_equals('valid media name (parent directory)', $parent_last, false);
    test_equals('valid media name (parent path)', $parent_path, false);
    test_equals('valid media name (current)', $current, false);
}

// 名前の確認（空・配列）テスト
{
    // 確認
    $empty = service_media_name_valid('');
    $array = service_media_name_valid(['photo.jpg']);

    // 結果（許可されないこと）
    test_equals('valid media name (empty)', $empty, false);
    test_equals('valid media name (array)', $array, false);
}

if (!$gd) {
    // サムネイルの確認は飛ばす（テスト用の画像の作成もGDに依存するため）
    test_skip('thumbnail tests', 'GDが有効ではありません。');
} else {
    // 一時ファイルが残らないことを確認するため、テスト開始時の数を控えておく
    $temp_count = count(glob(sys_get_temp_dir() . '/media_*'));

    // 画像を作成
    test_media_image($media_directory . 'landscape.jpg', 800, 600);
    test_media_image($media_directory . 'portrait.png', 300, 900);
    test_media_image($media_directory . 'small.gif', 50, 40);
    test_media_image($media_directory . 'upper.JPG', 800, 600);
    file_put_contents($media_directory . 'text.txt', 'テキスト');
    file_put_contents($media_directory . 'broken.jpg', '画像ではないデータ');

    // サムネイルの作成（横長）テスト
    {
        // 作成
        $result = service_media_thumbnail_create($directory . '/landscape.jpg');

        // 結果（縦横比を保って400×400に収まること）
        test_equals('create thumbnail landscape', $result, true);
        test_equals('create thumbnail landscape size', test_media_size($thumbnail_directory . 'landscape.jpg'), [400, 300]);

        // 結果（オリジナルは変わらないこと）
        test_equals('create thumbnail landscape original', test_media_size($media_directory . 'landscape.jpg'), [800, 600]);
    }

    // サムネイルの作成（縦長・端数あり）テスト
    {
        // 作成
        $result = service_media_thumbnail_create($directory . '/portrait.png');

        // 結果（幅は300 × 400 / 900 = 133.3 を丸める）
        test_equals('create thumbnail portrait', $result, true);
        test_equals('create thumbnail portrait size', test_media_size($thumbnail_directory . 'portrait.png'), [133, 400]);
    }

    // サムネイルの作成（縮小不要）テスト
    {
        // 作成
        $result = service_media_thumbnail_create($directory . '/small.gif');

        // 結果（拡大しないこと）
        test_equals('create thumbnail small', $result, true);
        test_equals('create thumbnail small size', test_media_size($thumbnail_directory . 'small.gif'), [50, 40]);
    }

    // サムネイルの作成（大文字の拡張子）テスト
    {
        // 作成
        $result = service_media_thumbnail_create($directory . '/upper.JPG');

        // 結果
        test_equals('create thumbnail upper', $result, true);
        test_equals('create thumbnail upper size', test_media_size($thumbnail_directory . 'upper.JPG'), [400, 300]);
    }

    // サムネイルの作成（画像以外）テスト
    {
        // 作成
        $result = service_media_thumbnail_create($directory . '/text.txt');

        // 結果（作成されないこと）
        test_equals('create thumbnail text', $result, false);
        test_equals('create thumbnail text file', is_file($thumbnail_directory . 'text.txt'), false);
    }

    // サムネイルの作成（壊れた画像）テスト
    {
        // 作成
        $result = service_media_thumbnail_create($directory . '/broken.jpg');

        // 結果（作成されないこと）
        test_equals('create thumbnail broken', $result, false);
        test_equals('create thumbnail broken file', is_file($thumbnail_directory . 'broken.jpg'), false);
    }

    // サムネイルの作成（存在しないファイル）テスト
    {
        // 作成
        $result = service_media_thumbnail_create($directory . '/nothing.jpg');

        // 結果
        test_equals('create thumbnail nothing', $result, false);
    }

    // サムネイルの作成（メモリ不足）テスト
    {
        // データ（展開に約36MB必要な画像を作成してから、メモリの上限を現在の使用量 + 32MBに下げる）
        test_media_image($media_directory . 'large.jpg', 3000, 3000);

        $memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', (string) (memory_get_usage() + 32 * 1024 * 1024));

        // 作成
        $result = service_media_thumbnail_create($directory . '/large.jpg');

        ini_set('memory_limit', $memory_limit);

        // 結果（Fatal error にならず、作成されないこと）
        test_equals('create thumbnail memory', $result, false);
        test_equals('create thumbnail memory file', is_file($thumbnail_directory . 'large.jpg'), false);
    }

    // 一時ファイルの削除テスト
    {
        // 結果（成功・失敗のどちらでも一時ファイルが残らないこと）
        test_equals('create thumbnail temp', count(glob(sys_get_temp_dir() . '/media_*')), $temp_count);
    }

    // サムネイルの一覧テスト
    {
        // 取得
        $thumbnails = service_media_thumbnail_list($directory);
        sort($thumbnails);

        // 結果（作成できたものだけが返ること）
        test_equals('list thumbnail', $thumbnails, ['landscape.jpg', 'portrait.png', 'small.gif', 'upper.JPG']);

        // 結果（サムネイルのディレクトリが無い場合は空）
        test_equals('list thumbnail nothing', service_media_thumbnail_list($directory . '/nothing'), []);
    }

    // サムネイルの削除テスト
    {
        // 削除
        $result = service_media_thumbnail_remove($directory . '/landscape.jpg');

        // 結果
        test_equals('remove thumbnail', $result, true);
        test_equals('remove thumbnail file', is_file($thumbnail_directory . 'landscape.jpg'), false);

        // 結果（オリジナルは削除しないこと）
        test_equals('remove thumbnail original', is_file($media_directory . 'landscape.jpg'), true);
    }

    // サムネイルの削除（サムネイルの無いファイル）テスト
    {
        // 削除
        $result = service_media_thumbnail_remove($directory . '/text.txt');

        // 結果（Warningを出さずに成功すること）
        test_equals('remove thumbnail nothing', $result, true);
    }

    // サムネイルの削除（ディレクトリ）テスト
    {
        // 削除
        $result = service_media_thumbnail_remove($directory . '/');

        // 結果（中身ごと削除されること）
        test_equals('remove thumbnail directory', $result, true);
        test_equals('remove thumbnail directory exists', is_dir($thumbnail_directory), false);
    }
}

// テストで作成したファイルを削除
directory_rmdir($media_directory);
directory_rmdir($thumbnail_directory);

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/media.php',
    ]);
}

/**
 * テスト用の画像を作成
 *
 * @param string $file
 * @param int    $width
 * @param int    $height
 *
 * @return void
 */
function test_media_image($file, $width, $height)
{
    $image = imagecreatetruecolor($width, $height);
    imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, imagecolorallocate($image, 200, 100, 50));

    if (preg_match('/\.gif$/i', $file)) {
        imagegif($image, $file);
    } elseif (preg_match('/\.png$/i', $file)) {
        imagepng($image, $file);
    } else {
        imagejpeg($image, $file);
    }
}

/**
 * 画像の幅と高さを取得
 *
 * @param string $file
 *
 * @return array|null
 */
function test_media_size($file)
{
    if (!is_file($file)) {
        return null;
    }

    $size = getimagesize($file);

    return [$size[0], $size[1]];
}
