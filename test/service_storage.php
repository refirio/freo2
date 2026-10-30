<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// 実際のストレージ（S3 など）を操作しないよう、テスト中はファイルに固定する
$GLOBALS['config']['storage_type'] = 'file';

// ライブラリを読み込み
service('storage.php');
service_storage_init();

// テスト用のディレクトリ
$test_directory = $GLOBALS['config']['file_target']['temp'] . 'test_storage/';

// 前回の実行で残ったファイルを削除
directory_rmdir($test_directory);
directory_mkdir($test_directory);

// オブジェクトの保存テスト
{
    // 保存
    $file      = service_storage_put($test_directory . 'photo.jpg', '画像のつもりのデータ');
    $directory = service_storage_put($test_directory . 'images/');

    // 結果
    test_not_equals('put storage file', $file, false);
    test_not_equals('put storage directory', $directory, false);
    test_equals('put storage file exists', is_file($test_directory . 'photo.jpg'), true);
    test_equals('put storage directory exists', is_dir($test_directory . 'images/'), true);
}

// オブジェクトの確認・取得テスト
{
    // 確認
    $exists  = service_storage_exist($test_directory . 'photo.jpg');
    $nothing = service_storage_exist($test_directory . 'nothing.jpg');
    $body    = service_storage_get($test_directory . 'photo.jpg');

    // 結果
    test_equals('exist storage', $exists, true);
    test_equals('exist storage nothing', $nothing, false);
    test_equals('get storage', $body, '画像のつもりのデータ');
}

// 一覧の並び順（自然順）テスト
{
    // データ（readdir() の順に左右されないよう、名前の順とは違う順で作成する）
    foreach (['photo10.jpg', 'photo2.jpg', 'Banana.txt', 'apple.txt'] as $name) {
        service_storage_put($test_directory . $name, 'データ');
    }

    // 確認
    $list  = service_storage_list($test_directory);
    $names = array_column($list, 'name');

    // 結果（数字は桁数ではなく数値の順、英字は大文字小文字を区別せずに並ぶこと）
    test_equals('list storage order', $names, ['images', 'apple.txt', 'Banana.txt', 'photo.jpg', 'photo2.jpg', 'photo10.jpg']);
}

// 一覧の並び順（ディレクトリが先）テスト
{
    // データ（名前の順ではファイルより後になるディレクトリを作成する）
    service_storage_put($test_directory . 'zip/');

    // 確認
    $list  = service_storage_list($test_directory);
    $types = array_column($list, 'type');

    // 結果（ディレクトリがまとめて先に並ぶこと）
    test_equals('list storage directory first', array_slice($types, 0, 2), ['directory', 'directory']);
    test_equals('list storage directory order', array_column(array_slice($list, 0, 2), 'name'), ['images', 'zip']);
}

// 一覧の内容テスト
{
    // 確認
    $list = service_storage_list($test_directory);

    $file = null;
    foreach ($list as $object) {
        if ($object['name'] === 'photo.jpg') {
            $file = $object;
        }
    }

    // 結果（ファイルは更新日時とファイルサイズを持つこと）
    test_array_haskey('list storage modified', $file, 'modified');
    test_array_haskey('list storage size', $file, 'size');
    test_equals('list storage size value', $file['size'], strlen('画像のつもりのデータ'));

    // 結果（. や .. は含まれないこと）
    test_equals('list storage dot', in_array('.', array_column($list, 'name'), true), false);
    test_equals('list storage dot dot', in_array('..', array_column($list, 'name'), true), false);
}

// オブジェクトの複製・移動テスト
{
    // 複製
    $copy = service_storage_copy($test_directory . 'copy.jpg', $test_directory . 'photo.jpg');

    // 結果（元のファイルも残ること）
    test_not_equals('copy storage', $copy, false);
    test_equals('copy storage file', service_storage_get($test_directory . 'copy.jpg'), '画像のつもりのデータ');
    test_equals('copy storage source', service_storage_exist($test_directory . 'photo.jpg'), true);

    // 移動
    $rename = service_storage_rename($test_directory . 'renamed.jpg', $test_directory . 'copy.jpg');

    // 結果（元のファイルは無くなること）
    test_not_equals('rename storage', $rename, false);
    test_equals('rename storage file', service_storage_exist($test_directory . 'renamed.jpg'), true);
    test_equals('rename storage source', service_storage_exist($test_directory . 'copy.jpg'), false);
}

// オブジェクトの削除テスト
{
    // 削除
    $file      = service_storage_remove($test_directory . 'renamed.jpg');
    $directory = service_storage_remove($test_directory . 'zip/');

    // 結果
    test_not_equals('remove storage file', $file, false);
    test_not_equals('remove storage directory', $directory, false);
    test_equals('remove storage file exists', is_file($test_directory . 'renamed.jpg'), false);
    test_equals('remove storage directory exists', is_dir($test_directory . 'zip/'), false);
}

// 古い一時ファイルの削除テスト
{
    // データ（一時ファイルの置き場所をテスト用のディレクトリに向け、残す時間を固定する）
    $temp_target    = $GLOBALS['config']['file_target']['temp'];
    $temp_retention = $GLOBALS['config']['temp_retention'];

    $GLOBALS['config']['file_target']['temp'] = $test_directory . 'temps/';
    $GLOBALS['config']['temp_retention']      = 60 * 60 * 8;

    service_storage_put($test_directory . 'temps/');
    service_storage_put($test_directory . 'temps/old.jpg', 'データ');
    service_storage_put($test_directory . 'temps/new.jpg', 'データ');
    service_storage_put($test_directory . 'temps/directory/');
    service_storage_put($test_directory . 'temps/.htaccess', 'Require all denied');
    touch($test_directory . 'temps/old.jpg', localdate() - 60 * 60 * 9);
    touch($test_directory . 'temps/new.jpg', localdate() - 60 * 60 * 7);
    touch($test_directory . 'temps/.htaccess', localdate() - 60 * 60 * 9);

    // 一覧
    $list = service_storage_list($test_directory . 'temps/');

    // 削除
    service_storage_temp_clean();

    // 結果（残す時間より古いファイルだけが消え、ディレクトリは残ること）
    test_equals('temp clean storage old', service_storage_exist($test_directory . 'temps/old.jpg'), false);
    test_equals('temp clean storage new', service_storage_exist($test_directory . 'temps/new.jpg'), true);
    test_equals('temp clean storage directory', is_dir($test_directory . 'temps/directory/'), true);

    // 結果（直接のアクセスを拒否する .htaccess は、一覧に出ず、古くても消えないこと）
    test_equals('list storage htaccess', in_array('.htaccess', array_column($list, 'name'), true), false);
    test_equals('temp clean storage htaccess', service_storage_exist($test_directory . 'temps/.htaccess'), true);

    // 設定を戻す
    $GLOBALS['config']['file_target']['temp'] = $temp_target;
    $GLOBALS['config']['temp_retention']      = $temp_retention;
}

// テストで作成したファイルを削除
directory_rmdir($test_directory);

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/storage.php',
    ]);
}
