<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// 実際のストレージ（S3 など）に書き込まないよう、テスト中はファイルに固定する
$GLOBALS['config']['storage_type'] = 'file';

// 実際のエントリーのファイルを操作しないよう、保存先もテスト用のディレクトリに変える
$GLOBALS['config']['file_target']['entry'] = $GLOBALS['config']['file_target']['temp'] . 'test_entry/entries/';
$GLOBALS['config']['file_target']['field'] = $GLOBALS['config']['file_target']['temp'] . 'test_entry/fields/';

// ライブラリを読み込み
model('entries.php');
model('fields.php');
model('field_sets.php');
model('categories.php');
model('category_sets.php');
model('attributes.php');
model('attribute_sets.php');
service_storage_init();

// 前回の実行で残ったファイルを削除
$file_directory = $GLOBALS['config']['file_target']['temp'] . 'test_entry/';

directory_rmdir($file_directory);

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'entries;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'fields;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'field_sets;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'category_sets;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'attributes;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'attribute_sets;');

// 正常データ（管理画面のエントリー登録フォームからの送信を想定）
$data_entry = [
    'id'             => '',
    'type_id'        => 1,
    'public'         => 'all',
    'public_begin'   => '',
    'public_end'     => '',
    'password'       => '',
    'datetime'       => '2026-01-01 10:00',
    'code'           => 'test1',
    'title'          => 'テスト1',
    'text'           => '本文です。',
    'text_type'      => 'textarea',
    'comment'        => 'closed',
    'field_sets'     => [],
    'category_sets'  => [],
    'attribute_sets' => [],
];

// フィールドの検証で使うフィールド（種類ごとに1つ）
$data_fields = [
    [
        'type_id'    => 1,
        'name'       => '必須テキスト',
        'kind'       => 'text',
        'validation' => 'required',
        'sort'       => 1,
    ],
    [
        'type_id' => 1,
        'name'    => '数値',
        'kind'    => 'number',
        'sort'    => 2,
    ],
    [
        'type_id' => 1,
        'name'    => '英数字',
        'kind'    => 'alphabet',
        'sort'    => 3,
    ],
    [
        'type_id' => 1,
        'name'    => '複数行',
        'kind'    => 'textarea',
        'sort'    => 4,
    ],
    [
        'type_id' => 1,
        'name'    => '選択',
        'kind'    => 'select',
        'choices' => "赤\n青\n緑",
        'sort'    => 5,
    ],
    [
        'type_id' => 1,
        'name'    => '画像',
        'kind'    => 'image',
        'sort'    => 6,
    ],
];

// トランザクションを開始
db_transaction();

// 前提データを登録（フィールド・カテゴリー・属性）
$field_ids = [];
foreach ($data_fields as $data_field) {
    model('insert_fields', [
        'values' => $data_field,
    ]);

    $fields = model('select_fields', [
        'select'   => 'id',
        'order_by' => 'id DESC',
        'limit'    => 1,
    ]);
    $field_ids[$data_field['kind']] = intval($fields[0]['id']);
}

model('insert_categories', [
    'values' => [
        'type_id' => 1,
        'code'    => 'test_category',
        'name'    => 'テスト分類',
        'sort'    => 1,
    ],
]);
$categories = model('select_categories', [
    'select'   => 'id',
    'order_by' => 'id DESC',
    'limit'    => 1,
]);
$category_id = intval($categories[0]['id']);

model('insert_attributes', [
    'values' => [
        'name'       => 'テスト属性',
        'filterable' => 0,
        'sort'       => 1,
    ],
]);
$attributes = model('select_attributes', [
    'select'   => 'id',
    'order_by' => 'id DESC',
    'limit'    => 1,
]);
$attribute_id = intval($attributes[0]['id']);

// 初期値テスト
{
    // 確認
    $default_entry = model('default_entries');

    // 結果
    test_equals('default entry id', $default_entry['id'], null);
    test_equals('default entry type_id', $default_entry['type_id'], 0);
    test_equals('default entry approved', $default_entry['approved'], 1);
    test_equals('default entry public', $default_entry['public'], 'all');
    test_equals('default entry public_begin', $default_entry['public_begin'], null);
    test_equals('default entry code', $default_entry['code'], '');
    test_equals('default entry text_type', $default_entry['text_type'], 'wysiwyg');
    test_equals('default entry comment', $default_entry['comment'], 'closed');
    test_equals('default entry sort', $default_entry['sort'], null);
    test_equals('default entry field_sets', $default_entry['field_sets'], []);
    test_regexp('default entry created', $default_entry['created'], '^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}$');
    test_regexp('default entry datetime', $default_entry['datetime'], '^\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}$');
}

// 日時の正規化テスト
{
    // データ（入力欄は分までなので秒を補う。全角数字でも入力できること）
    $test_entry = $data_entry;
    $test_entry['public_begin'] = '2026-02-01 09:30';
    $test_entry['public_end']   = '２０２６-０３-０１ １０:３０';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);

    // 結果
    test_equals('normalize entry datetime', $test_entry['datetime'], '2026-01-01 10:00:00');
    test_equals('normalize entry public_begin', $test_entry['public_begin'], '2026-02-01 09:30:00');
    test_equals('normalize entry public_end', $test_entry['public_end'], '2026-03-01 10:30:00');
}

// 日時の正規化（未入力）テスト
{
    // データ
    $test_entry = $data_entry;

    // 確認
    $test_entry = model('normalize_entries', $test_entry);

    // 結果（未入力のときは秒を補わないこと）
    test_equals('normalize entry public_begin (empty)', $test_entry['public_begin'], '');
    test_equals('normalize entry public_end (empty)', $test_entry['public_end'], '');
}

// フィールドの正規化テスト
{
    // データ（チェックボックスなど、複数の値を持つフィールドを想定）
    $test_entry = $data_entry;
    $test_entry['field_sets'] = [
        $field_ids['select']   => ['赤', '青'],
        $field_ids['text']     => 'テキスト',
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);

    // 結果（配列は改行区切りの文字列になること）
    test_equals('normalize entry field_sets (array)', $test_entry['field_sets'][$field_ids['select']], "赤\n青");
    test_equals('normalize entry field_sets (string)', $test_entry['field_sets'][$field_ids['text']], 'テキスト');
}

// 並び順の正規化（全角数字）テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['sort'] = '１２';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);

    // 結果
    test_equals('normalize entry sort', $test_entry['sort'], '12');
}

// 正常登録テスト
{
    // データ
    $test_entry = $data_entry;

    // 登録
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果（正常データでは警告が出ないこと）
    test_equals('validate entry', count($warnings), 0);

    if (empty($warnings)) {
        $inserted_id = test_entry_insert($test_entry);
    } else {
        debug($warnings);
    }

    // 結果
    $entries = model('select_entries', [
        'select' => 'type_id, approved, public, public_begin, password, datetime, code, title, text, text_type, comment, sort',
        'where'  => 'id = ' . intval($inserted_id),
    ]);

    test_equals('insert entry', count($entries), 1);
    test_equals('insert entry type_id', intval($entries[0]['type_id']), 1);
    test_equals('insert entry approved', intval($entries[0]['approved']), 1);
    test_equals('insert entry public', $entries[0]['public'], 'all');
    test_equals('insert entry code', $entries[0]['code'], 'test1');
    test_equals('insert entry title', $entries[0]['title'], 'テスト1');
    test_equals('insert entry datetime', $entries[0]['datetime'], '2026-01-01 10:00:00');

    // 結果（未入力の項目は NULL として保存されること）
    test_equals('insert entry public_begin', $entries[0]['public_begin'], null);
    test_equals('insert entry password', $entries[0]['password'], null);

    // 結果（並び順は自動で採番されること）
    test_equals('insert entry sort', intval($entries[0]['sort']), 1);
}

// 並び順の正規化（自動採番）テスト
{
    // データ（並び順を持たない管理画面からの入力を想定）
    $test_entry = $data_entry;
    $test_entry['code'] = 'test2';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);

    // 結果（登録済みの最大値 1 に 1 を加えた値になること）
    test_equals('normalize entry sort auto', $test_entry['sort'], 2);
}

// 関連データ付きの登録テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']           = 'test2';
    $test_entry['title']          = 'テスト2';
    $test_entry['field_sets']     = [
        $field_ids['text']     => 'テキストの値',
        $field_ids['textarea'] => '',
    ];
    $test_entry['category_sets']  = [$category_id];
    $test_entry['attribute_sets'] = [$attribute_id];

    // 登録
    $test_entry    = model('normalize_entries', $test_entry);
    $associate_id  = test_entry_insert($test_entry, [
        'field_sets'     => $test_entry['field_sets'],
        'category_sets'  => $test_entry['category_sets'],
        'attribute_sets' => $test_entry['attribute_sets'],
    ]);

    // 結果（フィールドがひも付くこと。未入力のフィールドは登録しないこと）
    $field_sets = model('select_field_sets', [
        'where' => 'entry_id = ' . intval($associate_id),
    ]);

    test_equals('insert entry field_sets', count($field_sets), 1);
    test_equals('insert entry field_sets field_id', intval($field_sets[0]['field_id']), $field_ids['text']);
    test_equals('insert entry field_sets text', $field_sets[0]['text'], 'テキストの値');

    // 結果（カテゴリーと属性がひも付くこと）
    $category_sets = model('select_category_sets', [
        'where' => 'entry_id = ' . intval($associate_id),
    ]);
    $attribute_sets = model('select_attribute_sets', [
        'where' => 'entry_id = ' . intval($associate_id),
    ]);

    test_equals('insert entry category_sets', count($category_sets), 1);
    test_equals('insert entry category_sets category_id', intval($category_sets[0]['category_id']), $category_id);
    test_equals('insert entry attribute_sets', count($attribute_sets), 1);
    test_equals('insert entry attribute_sets attribute_id', intval($attribute_sets[0]['attribute_id']), $attribute_id);
}

// 関連データを持つエントリーのIDを取得
$entries = model('select_entries', [
    'select' => 'id',
    'where'  => 'code = ' . db_escape('test2'),
]);
$associate_id = intval($entries[0]['id']);

// 関連データの取得テスト
{
    // 取得
    $entries = model('select_entries', [
        'where' => 'entries.id = ' . intval($associate_id),
    ], [
        'associate' => true,
    ]);

    // 結果（型の情報が付くこと）
    test_equals('select associate entry', count($entries), 1);
    test_array_haskey('select associate entry type_code', $entries[0], 'type_code');
    test_array_haskey('select associate entry type_name', $entries[0], 'type_name');
    test_equals('select associate entry type_code (value)', $entries[0]['type_code'], 'entry');

    // 結果（関連データが付くこと）
    test_array_haskey('select associate entry field_sets', $entries[0], 'field_sets');
    test_array_haskey('select associate entry category_sets', $entries[0], 'category_sets');
    test_array_haskey('select associate entry attribute_sets', $entries[0], 'attribute_sets');
    test_equals('select associate entry field_sets (value)', $entries[0]['field_sets'][$field_ids['text']], 'テキストの値');
    test_equals('select associate entry category_sets (count)', count($entries[0]['category_sets']), 1);
    test_equals('select associate entry attribute_sets (count)', count($entries[0]['attribute_sets']), 1);
}

// 型の必須テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['type_id'] = '';
    $test_entry['code']    = 'test3';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate required entry type_id', count($warnings), 1);
}

// 承認の書式テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']     = 'test3';
    $test_entry['approved'] = 'あ';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate boolean entry approved', count($warnings), 1);
}

// 公開の値テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']   = 'test3';
    $test_entry['public'] = 'unknown';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate list entry public', count($warnings), 1);
}

// 公開開始日時の値テスト
{
    // データ（存在しない日付）
    $test_entry = $data_entry;
    $test_entry['code']         = 'test3';
    $test_entry['public_begin'] = '2026-01-32 10:00';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate datetime entry public_begin', count($warnings), 1);
}

// 公開終了日時の値テスト
{
    // データ（存在しない日付）
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['public_end'] = '2026-04-31 10:00';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate datetime entry public_end', count($warnings), 1);
}

// 公開開始日時の時刻テスト
{
    // データ（存在しない時刻）
    $test_entry = $data_entry;
    $test_entry['code']         = 'test3';
    $test_entry['public_begin'] = '2026-01-01 25:00';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate datetime entry public_begin (hour)', count($warnings), 1);
}

// 公開開始日時の時刻（境界値）テスト
{
    // データ（その日の最後の時刻のため警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code']         = 'test3';
    $test_entry['public_begin'] = '2026-01-01 23:59';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate datetime entry public_begin (hour boundary)', count($warnings), 0);
}

// 公開期間の未入力テスト
{
    // データ（公開期間は任意項目のため未入力でも警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate empty entry public_begin', count($warnings), 0);
}

// パスワードの書式テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']     = 'test3';
    $test_entry['password'] = 'あいうえお';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate regexp entry password', count($warnings), 1);
}

// パスワードの長さ（境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code']     = 'test3';
    $test_entry['password'] = str_repeat('a', 20);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate between entry password (boundary)', count($warnings), 0);
}

// パスワードの長さテスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']     = 'test3';
    $test_entry['password'] = str_repeat('a', 21);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate between entry password', count($warnings), 1);
}

// 日時の必須テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']     = 'test3';
    $test_entry['datetime'] = '';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate required entry datetime', count($warnings), 1);
}

// 日時の値テスト
{
    // データ（存在しない月）
    $test_entry = $data_entry;
    $test_entry['code']     = 'test3';
    $test_entry['datetime'] = '2026-13-01 10:00';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate datetime entry datetime', count($warnings), 1);
}

// 日時の書式テスト
{
    // データ（日付と時刻が空白で区切られていない）
    $test_entry = $data_entry;
    $test_entry['code']     = 'test3';
    $test_entry['datetime'] = '2026-01-01';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果（PHPの警告を出さずに、検証の警告だけが出ること）
    test_equals('validate datetime entry datetime (format)', count($warnings), 1);
}

// コードの必須テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code'] = '';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate required entry code', count($warnings), 1);
}

// コードの書式テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code'] = 'あいうえお';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate regexp entry code', count($warnings), 1);
}

// コードの書式（スラッシュ）テスト
{
    // データ（ページの階層に使うため、スラッシュは許可されること）
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3/child';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate regexp entry code (slash)', count($warnings), 0);
}

// コードの書式（スラッシュの位置）テスト
{
    // データ（先頭・末尾のスラッシュと、連続するスラッシュは警告になる）
    $test_codes = [
        'leading'  => '/test3',
        'trailing' => 'test3/',
        'double'   => 'test3//child',
        'only'     => '/',
    ];

    foreach ($test_codes as $test_key => $test_code) {
        $test_entry = $data_entry;
        $test_entry['code'] = $test_code;

        // 確認
        $test_entry = model('normalize_entries', $test_entry);
        $warnings   = model('validate_entries', $test_entry);

        // 結果
        test_equals('validate regexp entry code (slash ' . $test_key . ')', count($warnings), 1);
        test_contains('validate regexp entry code (slash ' . $test_key . ' message)', $warnings['code'], '「/」');
    }

    // データ（深い階層は警告にならない）
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3/child/grandchild';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate regexp entry code (slash deep)', count($warnings), 0);
}

// コードの長さ（境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code'] = str_repeat('a', 80);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate between entry code (boundary)', count($warnings), 0);
}

// コードの長さテスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code'] = str_repeat('a', 81);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate between entry code', count($warnings), 1);
}

// コードの重複テスト
{
    // データ（登録済みのコードと同じコードで新規登録）
    $test_entry = $data_entry;

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate duplicate entry code', count($warnings), 1);
}

// コードの重複（型が違う場合）テスト
{
    // データ（コードは型ごとに重複を確認するので、型が違えば同じコードを使える）
    $test_entry = $data_entry;
    $test_entry['type_id'] = 2;

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate duplicate entry code (other type)', count($warnings), 0);
}

// コードの重複（自分自身は除外）テスト
{
    // データ（登録済みのエントリー自身を編集）
    $test_entry = $data_entry;
    $test_entry['id'] = $inserted_id;

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate duplicate entry code (self)', count($warnings), 0);
}

// コードの重複チェック無効テスト
{
    // データ（登録済みのコードと同じコード）
    $test_entry = $data_entry;

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry, [
        'duplicate' => false,
    ]);

    // 結果
    test_equals('validate duplicate entry code (disabled)', count($warnings), 0);
}

// タイトルの必須テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']  = 'test3';
    $test_entry['title'] = '';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate required entry title', count($warnings), 1);
}

// タイトルの長さ（境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない。文字数で数えることの確認も兼ねてマルチバイト文字を使う）
    $test_entry = $data_entry;
    $test_entry['code']  = 'test3';
    $test_entry['title'] = str_repeat('あ', 100);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry title (boundary)', count($warnings), 0);
}

// タイトルの長さテスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']  = 'test3';
    $test_entry['title'] = str_repeat('あ', 101);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry title', count($warnings), 1);
}

// 本文の未入力テスト
{
    // データ（本文は任意項目のため未入力でも警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3';
    $test_entry['text'] = '';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate empty entry text', count($warnings), 0);
}

// 本文の長さ（境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3';
    $test_entry['text'] = str_repeat('あ', 50000);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry text (boundary)', count($warnings), 0);
}

// 本文の長さテスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3';
    $test_entry['text'] = str_repeat('あ', 50001);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry text', count($warnings), 1);
}

// 本文の保存（上限）テスト
{
    // データ（上限の文字数をすべて4バイトの文字にしても、列に収まること。後のテストとコードがぶつからないよう、確認したら消す）
    $test_entry = $data_entry;
    $test_entry['code'] = 'test_text';
    $test_entry['text'] = str_repeat('😀', 50000);

    // 登録
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    test_equals('validate max_length entry text (4 bytes)', count($warnings), 0);

    $text_id = test_entry_insert($test_entry);

    // 結果（切り詰められずに、そのまま保存されること）
    $entries = model('select_entries', [
        'select' => 'text',
        'where'  => 'id = ' . intval($text_id),
    ]);

    test_equals('insert max_length entry text', $entries[0]['text'], $test_entry['text']);

    // 後始末
    model('delete_entries', [
        'where' => 'id = ' . intval($text_id),
    ], [
        'softdelete' => false,
    ]);
}

// 本文形式の値テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']      = 'test3';
    $test_entry['text_type'] = 'unknown';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate list entry text_type', count($warnings), 1);
}

// コメントの受付の値テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']    = 'test3';
    $test_entry['comment'] = 'unknown';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate list entry comment', count($warnings), 1);
}

// 並び順の必須テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3';
    $test_entry['sort'] = '';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate required entry sort', count($warnings), 1);
}

// 並び順の書式テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3';
    $test_entry['sort'] = 'あ';

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate numeric entry sort', count($warnings), 1);
}

// 並び順の桁数（境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3';
    $test_entry['sort'] = str_repeat('1', 5);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry sort (boundary)', count($warnings), 0);
}

// 並び順の桁数テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code'] = 'test3';
    $test_entry['sort'] = str_repeat('1', 6);

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry sort', count($warnings), 1);
}

// フィールドの必須テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['text'] => '',
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate required entry field_sets', count($warnings), 1);
    test_array_haskey('validate required entry field_sets (key)', $warnings, 'field_sets_' . $field_ids['text']);
}

// フィールドの数値テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['number'] => 'あ',
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate decimal entry field_sets', count($warnings), 1);
}

// フィールドの数値（未入力）テスト
{
    // データ（必須のフィールドではないため、未入力でも警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['number'] => '',
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate decimal entry field_sets (empty)', count($warnings), 0);
}

// フィールドの英数字テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['alphabet'] => 'あ',
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate alpha_dash entry field_sets', count($warnings), 1);
}

// フィールドの長さ（一行入力・境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['text'] => str_repeat('あ', 100),
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry field_sets text (boundary)', count($warnings), 0);
}

// フィールドの長さ（一行入力）テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['text'] => str_repeat('あ', 101),
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry field_sets text', count($warnings), 1);
}

// フィールドの長さ（複数行入力・境界値）テスト
{
    // データ（上限ちょうどのため警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['textarea'] => str_repeat('あ', 2000),
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry field_sets textarea (boundary)', count($warnings), 0);
}

// フィールドの長さ（複数行入力）テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['textarea'] => str_repeat('あ', 2001),
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate max_length entry field_sets textarea', count($warnings), 1);
}

// フィールドの選択肢テスト
{
    // データ（選択肢に無い値）
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['select'] => '黒',
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate list entry field_sets', count($warnings), 1);
}

// フィールドの選択肢（複数選択）テスト
{
    // データ（選択肢にある値だけなので警告は出ない）
    $test_entry = $data_entry;
    $test_entry['code']       = 'test3';
    $test_entry['field_sets'] = [
        $field_ids['select'] => ['赤', '緑'],
    ];

    // 確認
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);

    // 結果
    test_equals('validate list entry field_sets (multiple)', count($warnings), 0);
}

// 更新テスト
{
    // データ（編集フォームからの入力を想定し、検証には id を含める）
    $test_entry = $data_entry;
    $test_entry['id']    = $inserted_id;
    $test_entry['title'] = 'テスト1改';
    $test_entry['text']  = '更新後の本文です。';

    // 更新
    $test_entry = model('normalize_entries', $test_entry);
    $warnings   = model('validate_entries', $test_entry);
    if (empty($warnings)) {
        model('update_entries', [
            'set'   => [
                'title' => $test_entry['title'],
                'text'  => $test_entry['text'],
            ],
            'where' => [
                'id = :id',
                [
                    'id' => $inserted_id,
                ],
            ],
        ], [
            'id' => $inserted_id,
        ]);
    } else {
        debug($warnings);
    }

    // 結果
    $entries = model('select_entries', [
        'select' => 'title, text',
        'where'  => 'id = ' . intval($inserted_id),
    ]);

    test_equals('update entries title', $entries[0]['title'], 'テスト1改');
    test_equals('update entries text', $entries[0]['text'], '更新後の本文です。');
}

// フィールドの更新テスト
{
    // データ（画像のフィールドはファイルとして別に登録されるため、ひも付けを直接登録する）
    model('insert_field_sets', [
        'values' => [
            'field_id' => $field_ids['image'],
            'entry_id' => $associate_id,
            'text'     => 'photo.jpg',
        ],
    ]);

    // 更新
    model('update_entries', [
        'set'   => [
            'title' => 'テスト2改',
        ],
        'where' => [
            'id = :id',
            [
                'id' => $associate_id,
            ],
        ],
    ], [
        'id'         => $associate_id,
        'field_sets' => [
            $field_ids['text']     => '更新後のテキスト',
            $field_ids['textarea'] => '追加した複数行',
        ],
    ]);

    // 結果（入力したフィールドは置き換わること）
    $field_sets = model('select_field_sets', [
        'where' => 'entry_id = ' . intval($associate_id) . ' AND field_id = ' . $field_ids['text'],
    ]);

    test_equals('update entries field_sets', count($field_sets), 1);
    test_equals('update entries field_sets text', $field_sets[0]['text'], '更新後のテキスト');

    // 結果（画像のひも付けは消さないこと）
    $field_sets = model('select_field_sets', [
        'where' => 'entry_id = ' . intval($associate_id) . ' AND field_id = ' . $field_ids['image'],
    ]);

    test_equals('update entries field_sets (image)', count($field_sets), 1);
    test_equals('update entries field_sets (image text)', $field_sets[0]['text'], 'photo.jpg');
}

// ファイルのテスト用エントリーを登録
$test_entry = $data_entry;
$test_entry['code']  = 'test_file';
$test_entry['title'] = 'ファイルのテスト';

$test_entry = model('normalize_entries', $test_entry);
$file_id    = test_entry_insert($test_entry);

$file_entry_directory = $GLOBALS['config']['file_target']['entry'] . $file_id . '/';
$file_field_directory = $GLOBALS['config']['file_target']['field'] . $file_id . '_' . $field_ids['image'] . '/';

// 画像の保存テスト
{
    // データ（3件アップロードし、2件だけを選んだ状態）
    $test_files = [
        'pictures' => [
            [
                'name' => 'photo1.jpg',
                'data' => 'photo1のデータ',
            ],
            [
                'name' => 'photo2.jpg',
                'data' => 'photo2のデータ',
            ],
            [
                'name' => 'photo3.jpg',
                'data' => 'photo3のデータ',
            ],
        ],
    ];

    // 更新
    test_entry_file_update($file_id, $test_files, ['photo1.jpg', 'photo2.jpg']);

    // 結果（選んだ画像だけが保存されること）
    test_equals('set file entries pictures', is_file($file_entry_directory . 'photo1.jpg'), true);
    test_equals('set file entries pictures (data)', file_get_contents($file_entry_directory . 'photo2.jpg'), 'photo2のデータ');
    test_equals('set file entries pictures (not selected)', is_file($file_entry_directory . 'photo3.jpg'), false);

    // 結果（選んだ画像が改行区切りで記録されること）
    $entries = model('select_entries', [
        'select' => 'pictures',
        'where'  => 'id = ' . $file_id,
    ]);

    test_equals('set file entries pictures (column)', $entries[0]['pictures'], "photo1.jpg\nphoto2.jpg");
}

// サムネイルの保存テスト
{
    // データ（日本語のファイル名）
    $test_files = [
        'thumbnail' => [
            'name' => 'サムネイル.png',
            'data' => 'サムネイルのデータ',
        ],
    ];

    // 更新
    test_entry_file_update($file_id, $test_files, ['photo1.jpg', 'photo2.jpg']);

    // 結果（ファイル名はURLエンコードされること）
    test_equals('set file entries thumbnail', is_file($file_entry_directory . rawurlencode('サムネイル') . '.png'), true);

    $entries = model('select_entries', [
        'select' => 'thumbnail',
        'where'  => 'id = ' . $file_id,
    ]);

    test_equals('set file entries thumbnail (column)', $entries[0]['thumbnail'], rawurlencode('サムネイル') . '.png');

    // 結果（画像はそのまま残ること）
    test_equals('set file entries thumbnail (pictures)', is_file($file_entry_directory . 'photo1.jpg'), true);
}

// 一時ファイルからの保存テスト
{
    // データ（アップロードされたファイルは一時ファイルに入っていて、セッションには名前と場所だけがある）
    directory_mkdir($file_directory . 'temps/');
    file_put_contents($file_directory . 'temps/upload4', 'photo4のデータ');

    $test_files = [
        'pictures' => [
            [
                'name' => 'photo4.jpg',
                'temp' => $file_directory . 'temps/upload4',
            ],
        ],
    ];

    // 更新
    test_entry_file_update($file_id, $test_files, ['photo1.jpg', 'photo2.jpg', 'photo4.jpg']);

    // 結果（一時ファイルの内容で保存され、一時ファイルは残ること。消すのは登録の後の service_entry_file_clear()）
    test_equals('set file entries pictures (temp)', file_get_contents($file_entry_directory . 'photo4.jpg'), 'photo4のデータ');
    test_equals('set file entries pictures (temp kept)', is_file($file_directory . 'temps/upload4'), true);
}

// 画像の削除テスト
{
    // データ（アップロードはせず、選んだ画像から photo2.jpg を外した状態）
    $test_files = [
        'thumbnail' => [],
    ];

    // 更新
    test_entry_file_update($file_id, $test_files, ['photo1.jpg']);

    // 結果（選ばれなくなった画像が削除されること）
    test_equals('set file entries pictures (removed)', is_file($file_entry_directory . 'photo2.jpg'), false);
    test_equals('set file entries pictures (kept)', is_file($file_entry_directory . 'photo1.jpg'), true);

    $entries = model('select_entries', [
        'select' => 'pictures',
        'where'  => 'id = ' . $file_id,
    ]);

    test_equals('set file entries pictures (removed column)', $entries[0]['pictures'], 'photo1.jpg');
}

// サムネイルの削除テスト
{
    // データ
    $test_files = [
        'thumbnail' => [
            'delete' => true,
        ],
    ];

    // 更新
    test_entry_file_update($file_id, $test_files, ['photo1.jpg']);

    // 結果
    test_equals('set file entries thumbnail (deleted)', is_file($file_entry_directory . rawurlencode('サムネイル') . '.png'), false);

    $entries = model('select_entries', [
        'select' => 'thumbnail',
        'where'  => 'id = ' . $file_id,
    ]);

    test_equals('set file entries thumbnail (deleted column)', $entries[0]['thumbnail'], null);
}

// フィールドの画像の保存テスト
{
    // データ（編集画面からの送信を想定し、キーは field_<エントリーID>_<フィールドID>）
    $test_files = [
        'field_' . $file_id . '_' . $field_ids['image'] => [
            'name' => '写真.jpg',
            'data' => '写真のデータ',
        ],
    ];

    // 更新
    test_entry_file_update($file_id, $test_files, ['photo1.jpg']);

    // 結果（フィールド用のディレクトリに保存されること）
    test_equals('set file entries field', is_file($file_field_directory . rawurlencode('写真') . '.jpg'), true);

    // 結果（ファイル名がひも付けとして登録されること）
    $field_sets = model('select_field_sets', [
        'where' => 'entry_id = ' . $file_id . ' AND field_id = ' . $field_ids['image'],
    ]);

    test_equals('set file entries field (field_sets)', count($field_sets), 1);
    test_equals('set file entries field (field_sets text)', $field_sets[0]['text'], rawurlencode('写真') . '.jpg');
}

// フィールドの画像の削除テスト
{
    // データ
    $test_files = [
        'field_' . $file_id . '_' . $field_ids['image'] => [
            'delete' => true,
        ],
    ];

    // 更新
    test_entry_file_update($file_id, $test_files, ['photo1.jpg']);

    // 結果（ファイルとひも付けの両方が削除されること）
    test_equals('set file entries field (deleted)', is_file($file_field_directory . rawurlencode('写真') . '.jpg'), false);

    $field_sets = model('select_field_sets', [
        'where' => 'entry_id = ' . $file_id . ' AND field_id = ' . $field_ids['image'],
    ]);

    test_equals('set file entries field (deleted field_sets)', count($field_sets), 0);
}

// 削除（ファイルごと）テスト
{
    // 削除（既定ではエントリーとフィールドのディレクトリも削除する）
    model('delete_entries', [
        'where' => [
            'id = :id',
            [
                'id' => $file_id,
            ],
        ],
    ]);

    // 結果
    test_equals('delete entries (file)', is_dir($file_entry_directory), false);
    test_equals('delete entries (field file)', is_dir($file_field_directory), false);
}

// 削除テスト
{
    // 削除（ファイルの削除は「削除（ファイルごと）テスト」で確認するため、ここでは行わない）
    model('delete_entries', [
        'where' => [
            'id = :id',
            [
                'id' => $associate_id,
            ],
        ],
    ], [
        'file' => false,
    ]);

    // 結果（取得対象からは外れること）
    $entries = model('select_entries', [
        'where' => 'id = ' . intval($associate_id),
    ]);

    test_equals('delete entries', count($entries), 0);

    // 結果（レコード自体は残り、削除日時とコードが書き換わること）
    $entries = db_select([
        'select' => 'code, deleted',
        'from'   => DATABASE_PREFIX . 'entries',
        'where'  => [
            'id = :id',
            [
                'id' => $associate_id,
            ],
        ],
    ]);

    test_equals('delete entries (record)', count($entries), 1);
    test_not_equals('delete entries (deleted)', $entries[0]['deleted'], null);
    test_regexp('delete entries (code)', $entries[0]['code'], '^DELETED \d{14} test2$');

    // 結果（関連データは削除されること）
    $field_sets = model('select_field_sets', [
        'where' => 'entry_id = ' . intval($associate_id),
    ]);
    $category_sets = model('select_category_sets', [
        'where' => 'entry_id = ' . intval($associate_id),
    ]);
    $attribute_sets = model('select_attribute_sets', [
        'where' => 'entry_id = ' . intval($associate_id),
    ]);

    test_equals('delete entries (field_sets)', count($field_sets), 0);
    test_equals('delete entries (category_sets)', count($category_sets), 0);
    test_equals('delete entries (attribute_sets)', count($attribute_sets), 0);
}

// 物理削除テスト
{
    // データ
    $test_entry = $data_entry;
    $test_entry['code']  = 'test3';
    $test_entry['title'] = 'テスト3';

    // 登録
    $test_entry = model('normalize_entries', $test_entry);
    $deleted_id = test_entry_insert($test_entry);

    // 削除
    model('delete_entries', [
        'where' => [
            'id = :id',
            [
                'id' => $deleted_id,
            ],
        ],
    ], [
        'softdelete' => false,
        'file'       => false,
    ]);

    // 結果（レコード自体が消えること）
    $entries = db_select([
        'select' => 'id',
        'from'   => DATABASE_PREFIX . 'entries',
        'where'  => 'id = ' . intval($deleted_id),
    ]);

    test_equals('delete entries (physical)', count($entries), 0);
}

// 関連データの削除（複数件）テスト
{
    // データ（2件を登録し、それぞれにカテゴリーをひも付ける）
    $deleted_ids = [];
    foreach (['test4', 'test5'] as $index => $code) {
        $test_entry = $data_entry;
        $test_entry['code']  = $code;
        $test_entry['title'] = 'テスト' . ($index + 4);

        $test_entry    = model('normalize_entries', $test_entry);
        $deleted_ids[] = test_entry_insert($test_entry, [
            'category_sets' => [$category_id],
        ]);
    }

    // 無関係なひも付け（2件のIDを区切り文字なしで連結した値。区切り文字が無いと IN(45) のようになり、これを削除してしまう）
    $unrelated_id = intval(implode('', $deleted_ids));

    model('insert_category_sets', [
        'values' => [
            'category_id' => $category_id,
            'entry_id'    => $unrelated_id,
        ],
    ]);

    // 削除
    model('delete_entries', [
        'where' => 'code IN(' . db_escape('test4') . ', ' . db_escape('test5') . ')',
    ], [
        'file' => false,
    ]);

    // 結果（削除したエントリーのひも付けは消えること）
    $category_sets = model('select_category_sets', [
        'where' => 'entry_id IN(' . implode(',', $deleted_ids) . ')',
    ]);

    test_equals('delete entries (associate)', count($category_sets), 0);

    // 結果（無関係なひも付けは残ること）
    $category_sets = model('select_category_sets', [
        'where' => 'entry_id = ' . $unrelated_id,
    ]);

    test_equals('delete entries (associate unrelated)', count($category_sets), 1);
}

// 絞り込みテスト
{
    // 確認
    $filter = model('filter_entries', [
        'type_id'       => '1',
        'public'        => 'all',
        'title'         => 'テスト',
        'keyword'       => '本文',
        'category_sets' => ['2', '3'],
    ], [
        'associate' => true,
    ]);

    // 結果（条件が AND で連結されること）
    test_contains('filter entries type_id', $filter['where'], 'entries.type_id = 1');
    test_contains('filter entries public', $filter['where'], 'entries.public = ' . db_escape('all'));
    test_contains('filter entries title', $filter['where'], 'entries.title LIKE ' . db_escape('%テスト%'));
    test_contains('filter entries keyword', $filter['where'], 'entries.text LIKE ' . db_escape('%本文%'));
    test_contains('filter entries category_sets', $filter['where'], '(category_sets.category_id = 2 OR category_sets.category_id = 3)');
    test_contains('filter entries and', $filter['where'], ' AND ');

    // 結果（ページャー用のクエリ文字列が作られること）
    test_contains('filter entries pager', $filter['pager'], 'type_id=1');
    test_contains('filter entries pager (array)', $filter['pager'], 'category_sets[]=2');
}

// 絞り込み（未入力）テスト
{
    // 確認
    $filter = model('filter_entries', [
        'type_id' => '',
        'public'  => '',
        'title'   => '',
    ], [
        'associate' => true,
    ]);

    // 結果（未入力の項目は条件に含めないこと）
    test_equals('filter entries (empty)', $filter['where'], '');
    test_equals('filter entries pager (empty)', $filter['pager'], '');
}

// 絞り込み（関連データなし）テスト
{
    // 確認
    $filter = model('filter_entries', [
        'type_id' => '1',
    ]);

    // 結果
    test_equals('filter entries (not associate)', $filter['where'], null);
    test_equals('filter entries pager (not associate)', $filter['pager'], null);
}

// 表示用データ作成テスト
{
    // 確認（入力欄は分までなので、秒を落とす）
    $view_entry = model('view_entries', [
        'public_begin' => '2026-02-01 09:30:00',
        'public_end'   => '2026-03-01 10:30:00',
        'datetime'     => '2026-01-01 10:00:00',
        'title'        => 'テスト1',
    ]);

    // 結果
    test_equals('view entries public_begin', $view_entry['public_begin'], '2026-02-01 09:30');
    test_equals('view entries public_end', $view_entry['public_end'], '2026-03-01 10:30');
    test_equals('view entries datetime', $view_entry['datetime'], '2026-01-01 10:00');
    test_equals('view entries title', $view_entry['title'], 'テスト1');
}

// 関連データの削除（該当なし）テスト
{
    // 削除（条件に一致するエントリーが無い。フィールド・カテゴリー・属性のひも付けは既定で削除する）
    $resource = model('delete_entries', [
        'where' => 'code = \'not_exist\'',
    ]);

    // 結果（エラーにならないこと）
    test_equals('delete entries (no target)', (bool)$resource, true);
}

// トランザクションを終了
db_rollback();

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'entries;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'fields;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'field_sets;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'categories;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'category_sets;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'attributes;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'attribute_sets;');

// テストで作成したファイルを削除
directory_rmdir($file_directory);

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/models/entries.php',
    ]);
}

/**
 * ファイルを指定してエントリーを更新
 *
 * 管理画面の entry_post.php と同じように、アップロードしたファイルと、
 * 選択された画像のファイル名を渡す。
 *
 * @param int   $entry_id
 * @param array $files
 * @param array $picture_files
 *
 * @return void
 */
function test_entry_file_update($entry_id, $files, $picture_files)
{
    model('update_entries', [
        'set'   => [
            'title' => 'ファイルのテスト',
        ],
        'where' => [
            'id = :id',
            [
                'id' => $entry_id,
            ],
        ],
    ], [
        'id'            => $entry_id,
        'files'         => $files,
        'picture_files' => $picture_files,
    ]);
}

/**
 * エントリーを登録してIDを取得
 *
 * 正規化した配列には id やフィールドなどカラム以外のキーが含まれるため、
 * 管理画面の entry_post.php と同じように values を組み立てる。
 *
 * @param array $entry
 * @param array $options
 *
 * @return int|null
 */
function test_entry_insert($entry, $options = [])
{
    $resource = model('insert_entries', [
        'values' => [
            'type_id'      => $entry['type_id'],
            'approved'     => isset($entry['approved']) ? $entry['approved'] : 1,
            'public'       => $entry['public'],
            'public_begin' => $entry['public_begin'],
            'public_end'   => $entry['public_end'],
            'password'     => $entry['password'],
            'datetime'     => $entry['datetime'],
            'code'         => $entry['code'],
            'title'        => $entry['title'],
            'text'         => $entry['text'],
            'text_type'    => $entry['text_type'],
            'comment'      => $entry['comment'],
            'sort'         => isset($entry['sort']) ? $entry['sort'] : null,
        ],
    ], $options);
    if (!$resource) {
        return null;
    }

    $entries = model('select_entries', [
        'select'   => 'id',
        'order_by' => 'id DESC',
        'limit'    => 1,
    ]);

    return intval($entries[0]['id']);
}
