<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
// パスの組み立てだけなので、トランザクションもテーブルの初期化も行わない
import('libs/modules/loader.php');

// テスト用のファイルを作成
// パスは公開ディレクトリ（index.php のある場所）からの相対パスで、CLIもそこで実行する
$test_file = $GLOBALS['config']['file_target']['temp'] . 'test_loader.css';

file_put_contents($test_file, 'body { color: #000000; }');

// ファイルの読み込みテスト
{
    // 確認
    $file = loader_file($test_file);

    // 結果（更新日時をクエリに付けること）
    test_equals('loader_file', $file, $test_file . '?' . filemtime($test_file));
}

// ファイルの読み込み（クエリ付き）テスト
{
    // 確認
    $file = loader_file($test_file . '?v=1');

    // 結果（すでにクエリがあれば & でつなぐこと）
    test_equals('loader_file (query)', $file, $test_file . '?v=1&' . filemtime($test_file));
}

// ファイルの読み込み（キャッシュ回避なし）テスト
{
    // 確認
    $file = loader_file($test_file, false);

    // 結果（何も付けないこと）
    test_equals('loader_file (nocache false)', $file, $test_file);
}

// ファイルの読み込み（存在しないファイル）テスト
{
    // 確認
    $file = loader_file($GLOBALS['config']['file_target']['temp'] . 'nothing.css');

    // 結果（更新日時を取得できないので、そのまま返すこと）
    test_equals('loader_file (nothing)', $file, $GLOBALS['config']['file_target']['temp'] . 'nothing.css');
}

// CSSファイルの読み込みテスト
{
    // 確認
    $file    = loader_css('common.css');
    $nothing = loader_css('nothing.css');

    // 結果（css/ を付けること）
    test_equals('loader_css', $file, 'css/common.css?' . filemtime('css/common.css'));
    test_equals('loader_css (nothing)', $nothing, 'css/nothing.css');
}

// JSファイルの読み込みテスト
{
    // 確認
    $file    = loader_js('common.js');
    $nothing = loader_js('nothing.js');

    // 結果（js/ を付けること）
    test_equals('loader_js', $file, 'js/common.js?' . filemtime('js/common.js'));
    test_equals('loader_js (nothing)', $nothing, 'js/nothing.js');
}

// 更新日時が変わった場合テスト
{
    // データ（ファイルを更新する）
    $before = loader_file($test_file);

    touch($test_file, time() + 60);
    clearstatcache(true, $test_file);

    // 確認
    $after = loader_file($test_file);

    // 結果（クエリの値が変わること＝ブラウザのキャッシュが使われないこと）
    test_not_equals('loader_file (modified)', $after, $before);
}

// テストで作成したファイルを削除
unlink($test_file);

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'libs/modules/loader.php',
    ]);
}
