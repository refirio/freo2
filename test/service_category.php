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

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// トランザクションを開始
db_transaction();

// 正常データ
$data_category = [
    'type_id' => '1',
    'code'    => 'test1',
    'name'    => 'テスト1',
    'sort'    => '1',
];
$data_categories = [
    [
        'name' => '分類1',
        'sort' => '1',
    ],
    [
        'name' => '分類2',
        'sort' => '2',
    ],
    [
        'name' => '分類3',
        'sort' => '3',
    ],
];

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
}

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

    $updated_data = array_pop($categories);
    $test_data    = [
        $updated_data,
    ];
    test_array_subset('update categories', $test_data, $test_category);
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
            insert_categories([
                'values' => $category,
            ]);
        } else {
            debug($warnings);
        }
    }

    // 結果
    $categories = model('select_categories', [
        'select'   => 'sort',
        'order_by' => 'id',
    ]);

    test_equals('sort_categories', array_column($categories, 'sort'), ['1', '2', '3']);

    // 並び順を更新
    service_category_sort([
        1 => '3',
        2 => '2',
        3 => '1',
    ]);

    // 結果
    $categories = model('select_categories', [
        'select'   => 'sort',
        'order_by' => 'id',
    ]);

    test_equals('sort_categories', array_column($categories, 'sort'), ['3', '2', '1']);
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
