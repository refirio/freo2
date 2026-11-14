<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
// insert_users() は関連する属性のひも付けも更新するため、そのモデルも必要になる
model('logs.php');
model('users.php');
model('attribute_sets.php');

// 既存データ削除
// users は admin などの前提データ（シナリオテストが使う）があるため、テスト用のユーザーだけを削除する
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');
db_query('DELETE FROM ' . DATABASE_PREFIX . 'users WHERE username LIKE ' . db_escape('%testuser%') . ' OR email LIKE ' . db_escape('%testuser%') . ';');

// 正常データ（service_log_record() が記録する内容を想定）
$data_log = [
    'user_id' => null,
    'ip'      => '127.0.0.1',
    'agent'   => 'freo/2',
    'page'    => '/admin/category',
    'message' => null,
    'model'   => 'categories',
    'exec'    => 'insert',
];

// トランザクションを開始
db_transaction();

// 前提データを登録（操作したユーザー。テスト用ユーザーは最小権限で作る）
model('insert_users', [
    'values' => [
        'username'       => 'testuser1',
        'password'       => 'dummy',
        'authority_id'   => 3,
        'enabled'        => 1,
        'name'           => 'テスト太郎',
        'email'          => 'testuser1@example.com',
        'email_verified' => 1,
        'twostep'        => 0,
    ],
]);
$users = model('select_users', [
    'select'   => 'id',
    'order_by' => 'id DESC',
    'limit'    => 1,
]);
$user_id = intval($users[0]['id']);

// 初期値テスト
{
    // 確認
    $default_log = model('default_logs');

    // 結果
    test_equals('default log id', $default_log['id'], null);
    test_equals('default log user_id', $default_log['user_id'], null);
    test_equals('default log ip', $default_log['ip'], '');
    test_equals('default log agent', $default_log['agent'], null);
    test_equals('default log page', $default_log['page'], '');
    test_equals('default log model', $default_log['model'], null);
    test_equals('default log exec', $default_log['exec'], null);
    test_regexp('default log created', $default_log['created'], '^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$');
}

// 初期値のキーテスト（テーブルの列と一致すること）
{
    // 確認
    $default_log = model('default_logs');
    $columns     = array_column(db_result(db_query('SHOW COLUMNS FROM ' . DATABASE_PREFIX . 'logs;')), 'Field');

    sort($columns);
    $keys = array_keys($default_log);
    sort($keys);

    // 結果
    test_equals('default log keys', $keys, $columns);
}

// 正常登録テスト
{
    // データ
    $test_log = $data_log;
    $test_log['user_id'] = $user_id;

    // 登録
    $warnings = model('validate_logs', $test_log);

    // 結果（正常データでは警告が出ないこと）
    test_equals('validate log', count($warnings), 0);

    if (empty($warnings)) {
        model('insert_logs', [
            'values' => $test_log,
        ]);
    } else {
        debug($warnings);
    }

    // 結果
    $logs = model('select_logs', [
        'select'   => 'user_id, ip, agent, page, model, exec, message',
        'order_by' => 'id DESC',
        'limit'    => 10,
    ]);

    test_equals('insert log', count($logs), 1);
    test_equals('insert log user_id', intval($logs[0]['user_id']), $user_id);
    test_equals('insert log ip', $logs[0]['ip'], '127.0.0.1');
    test_equals('insert log page', $logs[0]['page'], '/admin/category');
    test_equals('insert log model', $logs[0]['model'], 'categories');
    test_equals('insert log exec', $logs[0]['exec'], 'insert');

    // 結果（未入力の項目は NULL として保存されること）
    test_equals('insert log message', $logs[0]['message'], null);
}

// 登録した操作ログのIDを取得
$logs = model('select_logs', [
    'select'   => 'id',
    'order_by' => 'id DESC',
    'limit'    => 1,
]);
$inserted_id = intval($logs[0]['id']);

// IPアドレスの必須テスト
{
    // データ（CLIから実行すると clientip() が null を返すため、NOT NULL のカラムで止まる）
    $test_log = $data_log;
    $test_log['ip'] = '';

    // 確認
    $warnings = model('validate_logs', $test_log);

    // 結果
    test_equals('validate required log ip', count($warnings), 1);
}

// ページの必須テスト
{
    // データ
    $test_log = $data_log;
    $test_log['page'] = '';

    // 確認
    $warnings = model('validate_logs', $test_log);

    // 結果
    test_equals('validate required log page', count($warnings), 1);
}

// 関連データの取得テスト
{
    // 取得
    $logs = model('select_logs', [
        'where' => 'logs.id = ' . intval($inserted_id),
    ], [
        'associate' => true,
    ]);

    // 結果（操作したユーザーの情報が付くこと）
    test_equals('select associate log', count($logs), 1);
    test_equals('select associate log user_username', $logs[0]['user_username'], 'testuser1');
    test_equals('select associate log user_name', $logs[0]['user_name'], 'テスト太郎');
    test_equals('select associate log user_email', $logs[0]['user_email'], 'testuser1@example.com');
}

// 関連データの取得（ユーザーなし）テスト
{
    // データ（公開側からの操作など、ログインしていない場合）
    $test_log = $data_log;
    $test_log['page'] = '/contact/';

    model('insert_logs', [
        'values' => $test_log,
    ]);

    // 取得
    $logs = model('select_logs', [
        'where' => 'logs.page = ' . db_escape('/contact/'),
    ], [
        'associate' => true,
    ]);

    // 結果（ユーザーがひも付いていなくても取得できること）
    test_equals('select associate log (no user)', count($logs), 1);
    test_equals('select associate log (no user username)', $logs[0]['user_username'], null);
}

// 関連データの取得（削除済みユーザー）テスト
{
    // データ（操作したユーザーを削除する）
    model('delete_users', [
        'where' => [
            'id = :id',
            [
                'id' => $user_id,
            ],
        ],
    ]);

    // 取得
    $logs = model('select_logs', [
        'where' => 'logs.id = ' . intval($inserted_id),
    ], [
        'associate' => true,
    ]);

    // 結果（削除済みユーザーの操作ログも取得でき、ユーザー名に削除済みの印が付くこと）
    test_equals('select associate log (deleted user)', count($logs), 1);
    test_regexp('select associate log (deleted user username)', $logs[0]['user_username'], '^DELETED \d+ testuser1$');
    test_equals('select associate log (deleted user name)', $logs[0]['user_name'], 'テスト太郎');

    // 取得（一覧の件数）
    $logs = model('select_logs', [
        'select' => 'COUNT(*) AS count',
    ], [
        'associate' => true,
    ]);

    // 結果（件数にも含まれること）
    test_equals('select associate log count (deleted user)', intval($logs[0]['count']), 2);
}

// 更新テスト
{
    // 更新（管理画面からログを編集する画面は無いが、モデルとしては更新できる）
    model('update_logs', [
        'set'   => [
            'message' => '確認しました。',
        ],
        'where' => [
            'id = :id',
            [
                'id' => $inserted_id,
            ],
        ],
    ]);

    // 結果
    $logs = model('select_logs', [
        'select' => 'message',
        'where'  => 'id = ' . intval($inserted_id),
    ]);

    test_equals('update logs message', $logs[0]['message'], '確認しました。');
}

// 削除テスト
{
    // 削除
    model('delete_logs', [
        'where' => [
            'id = :id',
            [
                'id' => $inserted_id,
            ],
        ],
    ]);

    // 結果（取得対象からは外れること）
    $logs = model('select_logs', [
        'where' => 'id = ' . intval($inserted_id),
    ]);

    test_equals('delete logs', count($logs), 0);

    // 結果（レコード自体は残り、削除日時が入ること）
    $logs = db_select([
        'select' => 'page, deleted',
        'from'   => DATABASE_PREFIX . 'logs',
        'where'  => [
            'id = :id',
            [
                'id' => $inserted_id,
            ],
        ],
    ]);

    test_equals('delete logs (record)', count($logs), 1);
    test_not_equals('delete logs (deleted)', $logs[0]['deleted'], null);
    test_equals('delete logs (page)', $logs[0]['page'], '/admin/category');
}

// 物理削除テスト
{
    // データ
    $test_log = $data_log;
    $test_log['page'] = '/admin/user';

    // 登録
    model('insert_logs', [
        'values' => $test_log,
    ]);

    $logs = model('select_logs', [
        'select'   => 'id',
        'order_by' => 'id DESC',
        'limit'    => 1,
    ]);
    $deleted_id = intval($logs[0]['id']);

    // 削除
    model('delete_logs', [
        'where' => [
            'id = :id',
            [
                'id' => $deleted_id,
            ],
        ],
    ], [
        'softdelete' => false,
    ]);

    // 結果（レコード自体が消えること）
    $logs = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'logs',
        'where'  => 'id = ' . intval($deleted_id),
    ]);

    test_equals('delete logs (physical)', count($logs), 0);
}

// トランザクションを終了
db_rollback();

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');
db_query('DELETE FROM ' . DATABASE_PREFIX . 'users WHERE username LIKE ' . db_escape('%testuser%') . ' OR email LIKE ' . db_escape('%testuser%') . ';');

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/models/logs.php',
    ]);
}
