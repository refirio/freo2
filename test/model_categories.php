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

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');

// 正常データ
$data_category = [
    'type_id' => '1',
    'code'    => 'test1',
    'name'    => 'テスト1',
    'sort'    => '1',
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
        model('insert_categories', [
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

// 型の必須テスト
{
    // データ
    $test_category = $data_category;
    $test_category['type_id'] = '';
    $test_category['code'] = 'test2';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate required category type_id', count($warnings), 1);
}

// コードの必須テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = '';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate required category code', count($warnings), 1);
}

// コードの書式テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'あいうえお';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate katakana category code', count($warnings), 1);
}

// コードの長さ（最小）テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = '1';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category code', count($warnings), 1);
}

// コードの長さ（最大）テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = '123456789012345678901234567890123456789012345678901234567890123456789012345678901';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate min_length category code', count($warnings), 1);
}

// 名前の必須テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['name'] = '';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate required category name', count($warnings), 1);
}

// 名前の長さテスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['name'] = 'あいうえおかきくけこさしすせそたちつてとな';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category name', count($warnings), 1);
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
        model('update_categories', [
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
    model('delete_categories', [
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

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/models/categories.php',
    ]);
}
