<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
model('logs.php');
service('log.php');

// リクエスト情報を用意
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'freo/2';

// 設定を退避して固定
$backup_log_retention = $GLOBALS['config']['log_retention'];

$GLOBALS['config']['log_retention'] = 180;

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// 正常データ
$data_log = [
    'user_id' => null,
    'ip'      => '127.0.0.1',
    'agent'   => 'freo/2',
    'page'    => '/admin/category',
    'message' => null,
    'model'   => 'categories',
    'exec'    => 'insert',
];

// 作成日時を指定して操作ログを登録する
function test_log_insert($data, $created)
{
    $data['created']  = $created;
    $data['modified'] = $created;
    $data['page']     = '/test/' . $created;

    model('insert_logs', [
        'values' => $data,
    ]);
}

// 残っている操作ログのページを取得する（物理削除を確認するため、削除済みも含める）
function test_log_pages()
{
    $logs = db_select([
        'select'   => 'page',
        'from'     => DATABASE_PREFIX . 'logs',
        'order_by' => 'id',
    ]);

    return array_column($logs, 'page');
}

// 保存日数の境界（基準の日時）
$now      = localdate();
$boundary = localdate('Y-m-d H:i:s', $now - 180 * 60 * 60 * 24);
$older    = localdate('Y-m-d H:i:s', $now - 180 * 60 * 60 * 24 - 1);
$newer    = localdate('Y-m-d H:i:s', $now - 179 * 60 * 60 * 24);
$oldest   = localdate('Y-m-d H:i:s', $now - 365 * 60 * 60 * 24);

// トランザクションを開始
db_transaction();

// 設定の初期値テスト
{
    // 結果（config.php で定義した値が180日であること）
    test_equals('log retention default', $backup_log_retention, 180);
}

// 保存日数を過ぎた操作ログの削除テスト
{
    // データ
    test_log_insert($data_log, $oldest);
    test_log_insert($data_log, $older);
    test_log_insert($data_log, $boundary);
    test_log_insert($data_log, $newer);

    // 削除
    service_log_purge();

    // 結果（保存日数より古いものだけが消え、ちょうど保存日数のものは残ること）
    $pages = test_log_pages();

    test_equals('purge logs count', count($pages), 2);
    test_equals('purge logs (oldest)', in_array('/test/' . $oldest, $pages, true), false);
    test_equals('purge logs (older)', in_array('/test/' . $older, $pages, true), false);
    test_equals('purge logs (boundary)', in_array('/test/' . $boundary, $pages, true), true);
    test_equals('purge logs (newer)', in_array('/test/' . $newer, $pages, true), true);
}

db_rollback();
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');
db_transaction();

// 論理削除済みの操作ログの削除テスト
{
    // データ
    test_log_insert($data_log, $oldest);

    model('delete_logs', [
        'where' => 'TRUE',
    ]);

    // 削除
    service_log_purge();

    // 結果（論理削除済みでも、古ければレコードごと消えること）
    test_equals('purge logs (softdeleted)', count(test_log_pages()), 0);
}

db_rollback();
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');
db_transaction();

// 保存日数の指定テスト
{
    // データ
    test_log_insert($data_log, $oldest);
    test_log_insert($data_log, $newer);

    // 削除（引数で保存日数を指定すると、設定より優先されること）
    service_log_purge(30);

    // 結果
    test_equals('purge logs (retention 30)', count(test_log_pages()), 0);
}

db_rollback();
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');
db_transaction();

// 保存日数が 0 のテスト
{
    // データ
    test_log_insert($data_log, $oldest);
    test_log_insert($data_log, $newer);

    // 削除（設定を 0 にすると、何も削除しないこと）
    $GLOBALS['config']['log_retention'] = 0;

    $resource = service_log_purge();

    $GLOBALS['config']['log_retention'] = 180;

    // 結果
    test_equals('purge logs (retention 0) result', $resource, null);
    test_equals('purge logs (retention 0)', count(test_log_pages()), 2);
}

db_rollback();
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');
db_transaction();

// 操作ログの記録で削除されるテスト
{
    // データ
    test_log_insert($data_log, $oldest);
    test_log_insert($data_log, $newer);

    // 記録
    service_log_record(null, 'categories', 'insert');

    // 結果（古いログが消え、新しいログと今回のログが残ること）
    $pages = test_log_pages();

    test_equals('record log purge count', count($pages), 2);
    test_equals('record log purge (oldest)', in_array('/test/' . $oldest, $pages, true), false);
    test_equals('record log purge (newer)', in_array('/test/' . $newer, $pages, true), true);
}

// 同じリクエストでは、2回目の記録で削除しないテスト
{
    // データ
    test_log_insert($data_log, $oldest);

    // 記録（操作の種類を変えて、記録自体はされるようにする）
    service_log_record(null, 'entries', 'update');

    // 結果（記録は増えるが、古いログは残ること）
    $pages = test_log_pages();

    test_equals('record log no purge count', count($pages), 4);
    test_equals('record log no purge (oldest)', in_array('/test/' . $oldest, $pages, true), true);
}

// トランザクションを終了
db_rollback();

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// 設定を復元
$GLOBALS['config']['log_retention'] = $backup_log_retention;

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/log.php',
    ]);
}
