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
    'type_id' => 1,
    'code'    => 'test1',
    'name'    => 'テスト1',
    'sort'    => 1,
];

// トランザクションを開始
db_transaction();

// 初期値テスト
{
    // 確認
    $default_category = model('default_categories');

    // 結果
    test_equals('default category id', $default_category['id'], null);
    test_equals('default category code', $default_category['code'], '');
    test_equals('default category name', $default_category['name'], '');
    test_equals('default category memo', $default_category['memo'], null);
    test_equals('default category deleted', $default_category['deleted'], null);
    test_regexp('default category created', $default_category['created'], '^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$');
}

// 正常登録テスト
{
    // データ
    $test_category = $data_category;

    // 登録
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果（正常データでは警告が出ないこと）
    test_equals('validate category', count($warnings), 0);

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

// 登録したカテゴリーのIDを取得
$categories = model('select_categories', [
    'select'   => 'id',
    'order_by' => 'id DESC',
    'limit'    => 1,
]);
$inserted_id = $categories[0]['id'];

// 関連データの取得テスト
{
    // 取得
    $categories = model('select_categories', [
        'where'    => 'categories.id = ' . intval($inserted_id),
        'order_by' => 'categories.sort, categories.id',
    ], [
        'associate' => true,
    ]);

    // 結果
    test_equals('select associate category', count($categories), 1);
    test_array_haskey('select associate category type_code', $categories[0], 'type_code');
    test_array_haskey('select associate category type_name', $categories[0], 'type_name');
    test_array_haskey('select associate category type_sort', $categories[0], 'type_sort');
}

// 並び順の正規化（全角数字）テスト
{
    // データ
    $test_category = $data_category;
    $test_category['sort'] = '１２';

    // 確認
    $test_category = model('normalize_categories', $test_category);

    // 結果
    test_equals('normalize category sort', $test_category['sort'], '12');
}

// 並び順の正規化（自動採番）テスト
{
    // データ（並び順を持たない管理画面からの入力を想定）
    $test_category = [
        'id'      => '',
        'type_id' => 1,
        'code'    => 'test2',
        'name'    => 'テスト2',
        'memo'    => '',
    ];

    // 確認
    $test_category = model('normalize_categories', $test_category);

    // 結果（登録済みの最大値 1 に 1 を加えた値になること）
    test_equals('normalize category sort auto', $test_category['sort'], 2);
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
    test_equals('validate alpha_dash category code', count($warnings), 1);
}

// コードの長さ（最小・境界値）テスト
{
    // データ（下限ちょうどのため警告は出ない）
    $test_category = $data_category;
    $test_category['code'] = str_repeat('a', 2);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate min_length category code (boundary)', count($warnings), 0);
}

// コードの長さ（最小）テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = str_repeat('a', 1);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate min_length category code', count($warnings), 1);
}

// コードの長さ（最大・境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_category = $data_category;
    $test_category['code'] = str_repeat('a', 80);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category code (boundary)', count($warnings), 0);
}

// コードの長さ（最大）テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = str_repeat('a', 81);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category code', count($warnings), 1);
}

// コードの重複テスト
{
    // データ（登録済みのコードと同じコードで新規登録）
    $test_category = $data_category;

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate duplicate category code', count($warnings), 1);
}

// コードの重複（自分自身は除外）テスト
{
    // データ（登録済みのカテゴリー自身を編集）
    $test_category = $data_category;
    $test_category['id'] = $inserted_id;

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate duplicate category code (self)', count($warnings), 0);
}

// コードの重複チェック無効テスト
{
    // データ（登録済みのコードと同じコード）
    $test_category = $data_category;

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category, [
        'duplicate' => false,
    ]);

    // 結果
    test_equals('validate duplicate category code (disabled)', count($warnings), 0);
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

// 名前の長さ（境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない。文字数で数えることの確認も兼ねてマルチバイト文字を使う）
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['name'] = str_repeat('あ', 20);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category name (boundary)', count($warnings), 0);
}

// 名前の長さテスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['name'] = str_repeat('あ', 21);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category name', count($warnings), 1);
}

// メモの未入力テスト
{
    // データ（メモは任意項目のため未入力でも警告は出ない）
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['memo'] = '';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate empty category memo', count($warnings), 0);
}

// メモの長さ（境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['memo'] = str_repeat('あ', 5000);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category memo (boundary)', count($warnings), 0);
}

// メモの長さテスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['memo'] = str_repeat('あ', 5001);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category memo', count($warnings), 1);
}

// 並び順の必須テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['sort'] = '';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate required category sort', count($warnings), 1);
}

// 並び順の書式テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['sort'] = 'あ';

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate numeric category sort', count($warnings), 1);
}

// 並び順の桁数（境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['sort'] = str_repeat('1', 5);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category sort (boundary)', count($warnings), 0);
}

// 並び順の桁数テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test2';
    $test_category['sort'] = str_repeat('1', 6);

    // 確認
    $test_category = model('normalize_categories', $test_category);
    $warnings      = model('validate_categories', $test_category);

    // 結果
    test_equals('validate max_length category sort', count($warnings), 1);
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

    $updated_data = array_shift($categories);
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

    // 結果（取得対象からは外れること）
    $categories = model('select_categories', [
        'order_by' => 'id DESC',
        'limit'    => 10,
    ]);

    test_equals('delete categories', count($categories), 0);

    // 結果（レコード自体は残り、削除日時とコードが書き換わること）
    $categories = db_select([
        'select' => 'code, deleted',
        'from'   => DATABASE_PREFIX . 'categories',
        'where'  => [
            'id = :id',
            [
                'id' => $inserted_id,
            ],
        ],
    ]);

    test_equals('delete categories (record)', count($categories), 1);
    test_not_equals('delete categories (deleted)', $categories[0]['deleted'], null);
    test_regexp('delete categories (code)', $categories[0]['code'], '^DELETED \d{14} test2$');
}

// 物理削除テスト
{
    // データ
    $test_category = $data_category;
    $test_category['code'] = 'test3';
    $test_category['name'] = 'テスト3';
    $test_category['sort'] = 3;

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

    // 削除
    model('delete_categories', [
        'where' => [
            'code = :code',
            [
                'code' => 'test3',
            ],
        ],
    ], [
        'softdelete' => false,
    ]);

    // 結果（レコード自体が消えること）
    $categories = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'categories',
        'where'  => [
            'code = :code',
            [
                'code' => 'test3',
            ],
        ],
    ]);

    test_equals('delete categories (physical)', count($categories), 0);
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
