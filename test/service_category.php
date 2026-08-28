<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
model('categories.php');
model('logs.php');
service('category.php');

// リクエスト情報を用意
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'freo/2';

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// 正常データ
$data_category = [
    'type_id' => 1,
    'code'    => 'test1',
    'name'    => 'テスト1',
    'sort'    => 1,
];
$data_categories = [
    [
        'type_id' => 1,
        'code'    => 'sort1',
        'name'    => '分類1',
        'sort'    => 1,
    ],
    [
        'type_id' => 1,
        'code'    => 'sort2',
        'name'    => '分類2',
        'sort'    => 2,
    ],
    [
        'type_id' => 1,
        'code'    => 'sort3',
        'name'    => '分類3',
        'sort'    => 3,
    ],
];

// トランザクションを開始
db_transaction();

// 正常登録テスト
{
    // データ
    $test_category = $data_category;

    // 登録
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);
    if (empty($warnings)) {
        service_category_insert([
            'values' => $test_category,
        ]);
    } else {
        debug($warnings);
    }

    // 結果
    $categories = model('select_categories', [
        'select'   => 'type_id, code, name, sort',
        'order_by' => 'id DESC',
        'limit'    => 10,
    ]);

    $inserted_data = array_shift($categories);
    $test_data     = [
        $inserted_data,
    ];
    test_array_subset('insert category', $test_data, $test_category);

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'order_by' => 'id DESC',
    ]);

    test_equals('insert category log', count($logs), 1);
    test_equals('insert category log model', $logs[0]['model'], 'categories');
    test_equals('insert category log exec', $logs[0]['exec'], 'insert');
    test_equals('insert category log ip', $logs[0]['ip'], '127.0.0.1');
    test_equals('insert category log user_id', $logs[0]['user_id'], null);
}

// 登録したカテゴリーのIDを取得
$categories = model('select_categories', [
    'select'   => 'id',
    'order_by' => 'id DESC',
    'limit'    => 1,
]);
$inserted_id = $categories[0]['id'];

// 更新テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['name'] = 'テスト2';
    $test_category['sort'] = 2;

    // 更新
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);
    if (empty($warnings)) {
        service_category_update([
            'set'   => $test_category,
            'where' => [
                'code = :code',
                [
                    'code' => 'test1',
                ],
            ],
        ]);
    } else {
        debug($warnings);
    }

    // 結果
    $categories = model('select_categories', [
        'select'   => 'type_id, code, name, sort',
        'order_by' => 'id DESC',
        'limit'    => 10,
    ]);

    $updated_data = array_shift($categories);
    $test_data    = [
        $updated_data,
    ];
    test_array_subset('update categories', $test_data, $test_category);

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'where' => [
            'exec = :exec',
            [
                'exec' => 'update',
            ],
        ],
        'order_by' => 'id DESC',
    ]);

    test_equals('update category log', count($logs), 1);
    test_equals('update category log model', $logs[0]['model'], 'categories');
}

// 最終編集日時の確認テスト
{
    // データ（編集フォームからの入力を想定し、検証には id を含める。id が無いとコードの重複チェックが自分自身を検出してしまう）
    $test_category = $data_category;
    $test_category['id']   = $inserted_id;
    $test_category['code'] = 'test2';
    $test_category['name'] = 'テスト3';

    // 更新（編集開始後に更新されていないので、競合とは判定されない）
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);
    if (empty($warnings)) {
        service_category_update([
            'set'   => [
                'type_id' => $test_category['type_id'],
                'code'    => $test_category['code'],
                'name'    => $test_category['name'],
            ],
            'where' => [
                'id = :id',
                [
                    'id' => $inserted_id,
                ],
            ],
        ], [
            'id'     => $inserted_id,
            'update' => localdate('Y-m-d H:i:s'),
        ]);
    } else {
        debug($warnings);
    }

    // 結果
    $categories = model('select_categories', [
        'select' => 'name',
        'where'  => 'id = ' . intval($inserted_id),
    ]);

    test_equals('update categories (modified check)', $categories[0]['name'], 'テスト3');
}

// 操作ログの重複抑止テスト
{
    // 結果（service_log_record() は同じ model と exec の組み合わせを1リクエストにつき1回しか記録しない。直前の更新でも exec が update のため、ログは増えない）
    $logs = model('select_logs', [
        'order_by' => 'id DESC',
    ]);

    test_equals('record category log once', count($logs), 2);
}

// 削除テスト
{
    // 削除
    service_category_delete([
        'where' => [
            'code = :code',
            [
                'code' => 'test2',
            ],
        ],
    ]);

    // 結果
    $categories = model('select_categories', [
        'order_by' => 'id DESC',
        'limit'    => 10,
    ]);

    test_equals('delete categories', count($categories), 0);

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'where' => [
            'exec = :exec',
            [
                'exec' => 'delete',
            ],
        ],
        'order_by' => 'id DESC',
    ]);

    test_equals('delete category log', count($logs), 1);
    test_equals('delete category log model', $logs[0]['model'], 'categories');
}

// トランザクションを終了
db_rollback();

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// トランザクションを開始
db_transaction();

// 並び順の一括変更テスト
{
    // 登録
    foreach ($data_categories as $category) {
        $category = model('normalize_categories', $category);
        $warnings = model('validate_categories', $category);
        if (empty($warnings)) {
            service_category_insert([
                'values' => $category,
            ]);
        } else {
            debug($warnings);
        }
    }

    // 結果
    $categories = model('select_categories', [
        'select'   => 'id, sort',
        'order_by' => 'id',
    ]);

    test_equals('sort categories (before)', array_column($categories, 'sort'), [1, 2, 3]);

    // 並び順を更新（IDは決め打ちにせず、登録済みのものを使う）
    $ids = array_column($categories, 'id');

    service_category_sort([
        $ids[0] => 3,
        $ids[1] => 2,
        $ids[2] => 1,
    ]);

    // 結果
    $categories = model('select_categories', [
        'select'   => 'sort',
        'order_by' => 'id',
    ]);

    test_equals('sort categories (after)', array_column($categories, 'sort'), [3, 2, 1]);

    // 結果（不正な値は無視されること）
    service_category_sort([
        $ids[0] => 'あ', // 並び順が数字でない
        'あ'    => 1,    // IDが不正
    ]);

    $categories = model('select_categories', [
        'select'   => 'sort',
        'order_by' => 'id',
    ]);

    test_equals('sort categories (invalid)', array_column($categories, 'sort'), [3, 2, 1]);
}

// トランザクションを終了
db_rollback();

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/category.php',
    ]);
}
