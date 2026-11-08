<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
import('libs/modules/directory.php');
service('update.php');

// テスト用のディレクトリ（プログラムの置き場所と公開ディレクトリを分けた配置を再現する）
$test_directory = $GLOBALS['config']['file_target']['temp'] . 'test_update/';
$test_app_root  = $test_directory . 'levis/';
$test_html_root = $test_directory . 'html/';

// 前回の実行で残ったファイルを削除
directory_rmdir($test_directory);
directory_mkdir($test_app_root . 'app/models/');
directory_mkdir($test_app_root . 'migrate/');
directory_mkdir($test_html_root . 'css/');

// 削除の対象にしてよいパスのテスト
{
    // 結果
    test_equals('removed path app', service_update_removed_path('app/models/pages.php'), true);
    test_equals('removed path libs', service_update_removed_path('libs/plugins/cookie.php'), true);
    test_equals('removed path migrate', service_update_removed_path('migrate/20251121223000-create_comments_table.sql'), true);
    test_equals('removed path sample theme', service_update_removed_path('themes/sample/app/views/home/index.php'), true);
    test_equals('removed path sample plugin', service_update_removed_path('plugins/sample/config.php'), true);
    test_equals('removed path files dot file', service_update_removed_path('files/temps/.htaccess'), false);
    test_equals('removed path root dot file', service_update_removed_path('.htaccess'), true);
    test_equals('removed path absolute', service_update_removed_path('/etc/passwd'), false);
    test_equals('removed path parent', service_update_removed_path('../config.php'), false);
    test_equals('removed path parent in middle', service_update_removed_path('app/../config.php'), false);
    test_equals('removed path current in middle', service_update_removed_path('app/./config.php'), false);
    test_equals('removed path empty segment', service_update_removed_path('app//config.php'), false);
    test_equals('removed path trailing slash', service_update_removed_path('app/models/'), false);
    test_equals('removed path backslash', service_update_removed_path('app\\models\\pages.php'), false);
    test_equals('removed path space', service_update_removed_path('app/models/my pages.php'), false);
    test_equals('removed path empty', service_update_removed_path(''), false);
    test_equals('removed path files', service_update_removed_path('files/entries/1/photo.jpg'), false);
    test_equals('removed path vendor', service_update_removed_path('libs/vendor/autoload.php'), false);
    test_equals('removed path other plugin', service_update_removed_path('plugins/order/config.php'), false);
    test_equals('removed path other theme', service_update_removed_path('themes/mytheme/config.php'), false);
    test_equals('removed path theme named like sample', service_update_removed_path('themes/sample2/config.php'), false);
}

// ハッシュのテスト
{
    // データ
    file_put_contents($test_directory . 'lf.txt', "line1\nline2\n");
    file_put_contents($test_directory . 'crlf.txt', "line1\r\nline2\r\n");
    file_put_contents($test_directory . 'other.txt', "line1\nline3\n");

    // 結果
    test_equals('hash lf', service_update_hash($test_directory . 'lf.txt'), hash('sha256', "line1\nline2\n"));
    test_equals('hash crlf is same as lf', service_update_hash($test_directory . 'crlf.txt'), service_update_hash($test_directory . 'lf.txt'));
    test_not_equals('hash other content', service_update_hash($test_directory . 'other.txt'), service_update_hash($test_directory . 'lf.txt'));
}

// 削除済みファイルの一覧の読み込みテスト
{
    // データ（2つめの行は CRLF、不正なハッシュ・不正なパス・空白1つの行は読み飛ばす）
    $test_list_file = $test_directory . 'removed_files.txt';
    file_put_contents($test_list_file, implode('', [
        hash('sha256', 'v1') . '  app/models/pages.php' . "\n",
        hash('sha256', 'v2') . '  app/models/pages.php' . "\r\n",
        hash('sha256', 'css') . '  css/pages.css' . "\n",
        'xyz  app/models/invalid_hash.php' . "\n",
        hash('sha256', 'x') . '  ../config.php' . "\n",
        hash('sha256', 'x') . ' app/models/one_space.php' . "\n",
        "\n",
    ]));

    // 取得
    $list = service_update_removed_list($test_list_file);

    // 結果
    test_equals('removed list paths', array_keys($list), ['app/models/pages.php', 'css/pages.css']);
    test_equals('removed list hashes', $list['app/models/pages.php'], [hash('sha256', 'v1'), hash('sha256', 'v2')]);
    test_equals('removed list not found', service_update_removed_list($test_directory . 'not_found.txt'), []);
}

// サーバーに残っている削除済みファイルの取得テスト
{
    // データ（pages.php は過去の版と同じ内容（改行は CRLF）、edited.php は編集したもの、css は公開ディレクトリ側にある）
    file_put_contents($test_app_root . 'app/models/pages.php', "<?php\r\n// v2\r\n");
    file_put_contents($test_app_root . 'app/models/edited.php', "<?php\n// edited\n");
    file_put_contents($test_html_root . 'css/pages.css', "body {}\n");

    $test_list = [
        'app/models/pages.php'   => [hash('sha256', "<?php\n// v1\n"), hash('sha256', "<?php\n// v2\n")],
        'app/models/edited.php'  => [hash('sha256', "<?php\n// v1\n")],
        'app/models/missing.php' => [hash('sha256', "<?php\n// v1\n")],
        'css/pages.css'          => [hash('sha256', "body {}\n")],
    ];

    // 取得
    $removed = service_update_removed($test_list, [$test_app_root, $test_html_root]);

    // 結果
    test_equals('removed delete paths', array_column($removed['delete'], 'path'), ['app/models/pages.php', 'css/pages.css']);
    test_equals('removed keep paths', array_column($removed['keep'], 'path'), ['app/models/edited.php']);
    test_equals('removed delete file', $removed['delete'][0]['file'], realpath($test_app_root . 'app/models/pages.php'));

    // 取得（標準の配置のように、2つの場所が同じ）
    $removed = service_update_removed($test_list, [$test_app_root, $test_app_root]);

    // 結果
    test_equals('removed same roots delete paths', array_column($removed['delete'], 'path'), ['app/models/pages.php']);
    test_equals('removed same roots keep paths', array_column($removed['keep'], 'path'), ['app/models/edited.php']);
}

// 削除済みファイルの削除テスト
{
    // 削除
    $results = service_update_remove($test_list, [$test_app_root, $test_html_root]);

    // 結果
    test_equals('remove deleted paths', array_column($results['deleted'], 'path'), ['app/models/pages.php', 'css/pages.css']);
    test_equals('remove failed', $results['failed'], []);
    test_equals('remove deleted app file', is_file($test_app_root . 'app/models/pages.php'), false);
    test_equals('remove deleted public file', is_file($test_html_root . 'css/pages.css'), false);
    test_equals('remove kept edited file', is_file($test_app_root . 'app/models/edited.php'), true);

    // 削除（2回目は何も無い）
    $results = service_update_remove($test_list, [$test_app_root, $test_html_root]);

    // 結果
    test_equals('remove again deleted', $results['deleted'], []);
}

// DB の更新に失敗したかの判定テスト
{
    // 結果
    test_equals('failed empty', service_update_failed([]), false);
    test_equals('failed success', service_update_failed([['error' => null], ['error' => null]]), false);
    test_equals('failed error', service_update_failed([['error' => null], ['error' => 'Table not found']]), true);
    test_equals('failed empty error message', service_update_failed([['error' => '']]), true);
}

// プログラムを置いたディレクトリのテスト
{
    // 取得
    $roots = service_update_roots();

    // 結果
    test_equals('roots app first', MAIN_APPLICATION_PATH === '' || substr($roots[0], -strlen(MAIN_APPLICATION_PATH)) === MAIN_APPLICATION_PATH, true);
    test_equals('roots count', count($roots), MAIN_APPLICATION_PATH === '' ? 1 : 2);
}

// テスト用のディレクトリを削除
directory_rmdir($test_directory);

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    service_coverage_output(service_coverage_end(), [
        'app/services/update.php',
    ]);
}
