<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
model('entries.php');
model('fields.php');
model('field_sets.php');
model('categories.php');
model('category_sets.php');
model('attributes.php');
model('attribute_sets.php');
model('logs.php');
model('types.php');
service('entry.php');

// リクエスト情報を用意
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'freo/2';

// パスワード認証時の表示を固定する（設定によって結果が変わらないようにする）
$setting_title = isset($GLOBALS['setting']['restricted_password_title']) ? $GLOBALS['setting']['restricted_password_title'] : null;
$setting_text  = isset($GLOBALS['setting']['restricted_password_text'])  ? $GLOBALS['setting']['restricted_password_text']  : null;

$GLOBALS['setting']['restricted_password_title'] = '要認証: ';
$GLOBALS['setting']['restricted_password_text']  = '<p>パスワード認証により公開されます。</p>';

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'entries;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'attributes;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'attribute_sets;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// 公開範囲の確認用データ（登録順に取得されるため、この順序が取得結果の順序になる）
$data_entries = [
    [
        'code'      => 'pub_all',
        'public'    => 'all',
        'text'      => "1行目\n2行目",
        'text_type' => 'textarea',
        'pictures'  => "a.jpg\nb.jpg",
        'thumbnail' => 'thumb.jpg',
    ],
    [
        'code'   => 'pub_user',
        'public' => 'user',
    ],
    [
        'code'   => 'pub_attribute',
        'public' => 'attribute',
    ],
    [
        'code'   => 'pub_attribute_other',
        'public' => 'attribute',
    ],
    [
        'code'      => 'pub_password',
        'public'    => 'password',
        'password'  => 'abcd',
        'text'      => '<p>認証後の本文です。</p>',
        'pictures'  => "a.jpg\nb.jpg",
        'thumbnail' => 'thumb.jpg',
    ],
    [
        'code'   => 'pub_none',
        'public' => 'none',
    ],
    [
        'code'     => 'pub_unapproved',
        'approved' => 0,
    ],
    [
        'code'         => 'pub_future',
        'public_begin' => '2099-01-01 00:00:00',
    ],
    [
        'code'       => 'pub_past',
        'public_end' => '2000-01-01 00:00:00',
    ],
    [
        'code'         => 'pub_period',
        'public_begin' => '2000-01-01 00:00:00',
        'public_end'   => '2099-01-01 00:00:00',
    ],
    [
        'code'      => 'pub_text_none',
        'text'      => '表示されない本文',
        'text_type' => 'none',
    ],
    [
        'code'    => 'pub_page',
        'type_id' => 2,
    ],
];

// トランザクションを開始
db_transaction();

// 前提データを登録（エントリー・属性）
$entry_ids = [];
foreach ($data_entries as $data_entry) {
    model('insert_entries', [
        'values' => test_entry_values($data_entry),
    ]);

    $entries = model('select_entries', [
        'select'   => 'id',
        'order_by' => 'id DESC',
        'limit'    => 1,
    ]);
    $entry_ids[$data_entry['code']] = intval($entries[0]['id']);
}

$attribute_ids = [];
foreach (['テスト属性1', 'テスト属性2'] as $index => $name) {
    model('insert_attributes', [
        'values' => [
            'name'       => $name,
            'filterable' => 0,
            'sort'       => $index + 1,
        ],
    ]);

    $attributes = model('select_attributes', [
        'select'   => 'id',
        'order_by' => 'id DESC',
        'limit'    => 1,
    ]);
    $attribute_ids[] = intval($attributes[0]['id']);
}

model('insert_attribute_sets', [
    'values' => [
        'attribute_id' => $attribute_ids[0],
        'entry_id'     => $entry_ids['pub_attribute'],
    ],
]);
model('insert_attribute_sets', [
    'values' => [
        'attribute_id' => $attribute_ids[1],
        'entry_id'     => $entry_ids['pub_attribute_other'],
    ],
]);

// 公開エントリーの取得（ユーザー登録なし）テスト
{
    // 取得（$GLOBALS['authority'] を用意しない状態が「ユーザー登録なし」にあたる）
    $entries = service_entry_select_published('entry', [
        'order_by' => 'entries.id',
    ]);

    // 結果（「全体に公開」と「パスワード認証で公開」だけが取得されること）
    test_equals('select published entry', array_column($entries, 'code'), ['pub_all', 'pub_password', 'pub_period', 'pub_text_none']);
}

// 公開エントリーの取得（型の絞り込み）テスト
{
    // 取得
    $entries = service_entry_select_published('page', [
        'order_by' => 'entries.id',
    ]);

    // 結果（指定した型のエントリーだけが取得されること）
    test_equals('select published entry (type)', array_column($entries, 'code'), ['pub_page']);
}

// 公開エントリーの取得（条件の追加）テスト
{
    // 取得
    $entries = service_entry_select_published('entry', [
        'where' => [
            'entries.code = :code',
            [
                'code' => 'pub_all',
            ],
        ],
    ]);

    // 結果（指定した条件で絞り込めること）
    test_equals('select published entry (where)', count($entries), 1);
    test_equals('select published entry (where code)', $entries[0]['code'], 'pub_all');
}

// 公開エントリーの取得（条件を追加しても非公開は取得しない）テスト
{
    // 取得
    $entries = service_entry_select_published('entry', [
        'where' => [
            'entries.code = :code',
            [
                'code' => 'pub_none',
            ],
        ],
    ]);

    // 結果（条件を追加しても公開範囲の判定は外れないこと）
    test_equals('select published entry (where none)', count($entries), 0);
}

// パスワード認証エントリーの表示制限テスト
{
    // データ（フィールドの値を1つ持たせる）
    model('insert_fields', [
        'values' => [
            'type_id' => 1,
            'code'    => 'test_password_field',
            'name'    => 'テストフィールド',
            'kind'    => 'file',
            'sort'    => 1,
        ],
    ]);
    $fields = model('select_fields', [
        'where' => 'fields.code = ' . db_escape('test_password_field'),
    ]);
    $password_field_id = intval($fields[0]['id']);

    model('insert_field_sets', [
        'values' => [
            'field_id' => $password_field_id,
            'entry_id' => $entry_ids['pub_password'],
            'text'     => 'secret.pdf',
        ],
    ]);

    // 取得（パスワードを入力していない状態）
    unset($_SESSION['entry_passwords']);

    $entries = service_entry_select_published('entry', [
        'where' => 'entries.code = ' . db_escape('pub_password'),
    ]);

    // 結果（タイトルに文言が付き、本文・画像・サムネイル・フィールドの値が伏せられること）
    test_equals('select published entry (password)', count($entries), 1);
    test_equals('select published entry (password title)', $entries[0]['title'], '要認証: タイトル');
    test_equals('select published entry (password text)', $entries[0]['text'], '<p>パスワード認証により公開されます。</p>');
    test_equals('select published entry (password pictures)', $entries[0]['pictures'], null);
    test_equals('select published entry (password thumbnail)', $entries[0]['thumbnail'], null);
    test_equals('select published entry (password field_sets)', $entries[0]['field_sets'], []);
}

// パスワード認証エントリーの表示（認証後）テスト
{
    // 取得（パスワードを入力した状態）
    $_SESSION['entry_passwords'][$entry_ids['pub_password']] = true;

    $entries = service_entry_select_published('entry', [
        'where' => 'entries.code = ' . db_escape('pub_password'),
    ]);

    unset($_SESSION['entry_passwords']);

    // 結果（本来の内容が表示されること）
    test_equals('select published entry (authorized title)', $entries[0]['title'], 'タイトル');
    test_equals('select published entry (authorized text)', $entries[0]['text'], '<p>認証後の本文です。</p>');
    test_equals('select published entry (authorized pictures)', $entries[0]['pictures'], ['a.jpg', 'b.jpg']);
    test_equals('select published entry (authorized thumbnail)', $entries[0]['thumbnail'], 'thumb.jpg');
    test_equals('select published entry (authorized field_sets)', $entries[0]['field_sets'], [$password_field_id => 'secret.pdf']);
}

// ファイルのURLテスト
{
    // データ（設定を退避する）
    $storage_protect = $GLOBALS['config']['storage_protect'];
    $storage_url     = $GLOBALS['config']['storage_url'];

    $GLOBALS['config']['storage_url'] = 'http://example.com';

    // 確認（直接表示する）
    $GLOBALS['config']['storage_protect'] = false;

    $entry_url = app_file_url('entry', 1, 'a.jpg');
    $field_url = app_file_url('field', '1_2', 'b.pdf');
    $multi_url = app_file_url('entry', 1, '%E5%86%99.jpg');

    // 結果（保存先のURLになり、保存したファイル名はもう一度エンコードされること）
    test_equals('file url', $entry_url, 'http://example.com/' . $GLOBALS['config']['file_target']['entry'] . '1/a.jpg');
    test_equals('file url (field)', $field_url, 'http://example.com/' . $GLOBALS['config']['file_target']['field'] . '1_2/b.pdf');
    test_equals('file url (encoded)', $multi_url, 'http://example.com/' . $GLOBALS['config']['file_target']['entry'] . '1/%25E5%2586%2599.jpg');

    // 確認（プログラムを通して表示する）
    $GLOBALS['config']['storage_protect'] = true;

    $entry_url = app_file_url('entry', 1, 'a.jpg');
    $field_url = app_file_url('field', '1_2', 'b.pdf');
    $multi_url = app_file_url('entry', 1, '%E5%86%99.jpg');

    // 結果（/storage/ のURLになること）
    test_equals('file url (protect)', $entry_url, MAIN_FILE . '/storage/entry/1/a.jpg');
    test_equals('file url (protect field)', $field_url, MAIN_FILE . '/storage/field/1_2/b.pdf');
    test_equals('file url (protect encoded)', $multi_url, MAIN_FILE . '/storage/entry/1/%25E5%2586%2599.jpg');

    // 設定を戻す
    $GLOBALS['config']['storage_protect'] = $storage_protect;
    $GLOBALS['config']['storage_url']     = $storage_url;
}

// 公開側で表示できるファイルの判定テスト
{
    // データ（公開のエントリーにもフィールドの値を持たせる）
    model('insert_field_sets', [
        'values' => [
            'field_id' => $password_field_id,
            'entry_id' => $entry_ids['pub_all'],
            'text'     => 'doc.pdf',
        ],
    ]);

    unset($_SESSION['entry_passwords']);

    $entry_target = $GLOBALS['config']['file_target']['entry'];
    $field_target = $GLOBALS['config']['file_target']['field'];
    $all_key      = $entry_ids['pub_all'];
    $field_key    = $entry_ids['pub_all'] . '_' . $password_field_id;

    // 結果（公開のエントリーの画像・サムネイル・フィールドのファイルは、保存先が返ること）
    test_equals('file published', service_entry_file_published('entry', $all_key, 'a.jpg'), $entry_target . $all_key . '/a.jpg');
    test_equals('file published (thumbnail)', service_entry_file_published('entry', $all_key, 'thumb.jpg'), $entry_target . $all_key . '/thumb.jpg');
    test_equals('file published (field)', service_entry_file_published('field', $field_key, 'doc.pdf'), $field_target . $field_key . '/doc.pdf');

    // 結果（エントリーに登録されていないファイルは表示できないこと）
    test_equals('file published (not registered)', service_entry_file_published('entry', $all_key, 'c.jpg'), null);
    test_equals('file published (not registered field)', service_entry_file_published('field', $field_key, 'other.pdf'), null);

    // 結果（公開側で見えないエントリーのファイルは表示できないこと）
    test_equals('file published (user)', service_entry_file_published('entry', $entry_ids['pub_user'], 'a.jpg'), null);
    test_equals('file published (none)', service_entry_file_published('entry', $entry_ids['pub_none'], 'a.jpg'), null);
    test_equals('file published (nothing)', service_entry_file_published('entry', 999999, 'a.jpg'), null);

    // 結果（パスワード認証の前は表示できず、認証すると表示できること）
    $password_key = $entry_ids['pub_password'] . '_' . $password_field_id;

    test_equals('file published (password)', service_entry_file_published('entry', $entry_ids['pub_password'], 'a.jpg'), null);
    test_equals('file published (password field)', service_entry_file_published('field', $password_key, 'secret.pdf'), null);

    $_SESSION['entry_passwords'][$entry_ids['pub_password']] = true;

    test_equals('file published (authorized)', service_entry_file_published('entry', $entry_ids['pub_password'], 'a.jpg'), $entry_target . $entry_ids['pub_password'] . '/a.jpg');
    test_equals('file published (authorized field)', service_entry_file_published('field', $password_key, 'secret.pdf'), $field_target . $password_key . '/secret.pdf');

    unset($_SESSION['entry_passwords']);

    // 結果（対象・キーの形が正しくなければ表示できないこと）
    test_equals('file published (target)', service_entry_file_published('media', $all_key, 'a.jpg'), null);
    test_equals('file published (entry key)', service_entry_file_published('entry', $all_key . 'a', 'a.jpg'), null);
    test_equals('file published (field key)', service_entry_file_published('field', $all_key, 'doc.pdf'), null);
    test_equals('file published (entry key for field)', service_entry_file_published('entry', $field_key, 'a.jpg'), null);
}

// 本文形式の変換テスト
{
    // 取得
    $entries = service_entry_select_published('entry', [
        'where'    => 'entries.code IN(' . db_escape('pub_all') . ', ' . db_escape('pub_period') . ', ' . db_escape('pub_text_none') . ')',
        'order_by' => 'entries.id',
    ]);

    // 結果（複数行入力はエスケープして段落と改行に変換されること）
    test_equals('select published entry (textarea)', $entries[0]['text'], "<p>1行目<br>\n2行目</p>");

    // 結果（HTML直接入力はそのまま出力されること）
    test_equals('select published entry (html)', $entries[1]['text'], '本文');

    // 結果（本文形式が「なし」なら本文を持たないこと）
    test_equals('select published entry (none)', $entries[2]['text'], null);
}

// 画像の変換テスト
{
    // 取得
    $entries = service_entry_select_published('entry', [
        'where' => 'entries.code = ' . db_escape('pub_all'),
    ]);

    // 結果（改行区切りの画像が配列になること）
    test_equals('select published entry (pictures)', $entries[0]['pictures'], ['a.jpg', 'b.jpg']);
}

// 公開エントリーの取得（ゲスト）テスト
{
    // データ（会員登録したユーザーとしてログインしている状態。属性は1つ目だけを持つ）
    $GLOBALS['authority']['power'] = 0;
    $GLOBALS['attributes']         = [$attribute_ids[0]];

    // 取得
    $entries = service_entry_select_published('entry', [
        'order_by' => 'entries.id',
    ]);

    // 結果（「登録ユーザーに公開」と、自分が持つ属性の「指定の属性に公開」が増えること）
    test_equals('select published entry (guest)', array_column($entries, 'code'), ['pub_all', 'pub_user', 'pub_attribute', 'pub_password', 'pub_period', 'pub_text_none']);
}

// 公開エントリーの取得（閲覧者以上）テスト
{
    // データ（管理画面に入れる権限でログインしている状態）
    $GLOBALS['authority']['power'] = 1;

    // 取得
    $entries = service_entry_select_published('entry', [
        'order_by' => 'entries.id',
    ]);

    // 結果（「非公開」以外がすべて取得されること。属性を持たないエントリーも取得できること）
    test_equals('select published entry (authority)', array_column($entries, 'code'), ['pub_all', 'pub_user', 'pub_attribute', 'pub_attribute_other', 'pub_password', 'pub_period', 'pub_text_none']);
}

// 公開エントリーの取得（権限の変化）テスト
{
    // データ（ログアウトした状態に戻す）
    unset($GLOBALS['authority']['power']);
    unset($GLOBALS['attributes']);

    // 取得
    $entries = service_entry_select_published('entry', [
        'order_by' => 'entries.id',
    ]);

    // 結果（最初に判定した権限を保持せず、そのときの権限で判定されること）
    test_equals('select published entry (authority changed)', array_column($entries, 'code'), ['pub_all', 'pub_password', 'pub_period', 'pub_text_none']);
}

// エントリーの登録テスト
{
    // データ
    $test_entry = test_entry_values([
        'code'  => 'test1',
        'title' => 'テスト1',
    ]);

    // 登録
    service_entry_insert([
        'values' => $test_entry,
    ]);

    // 結果
    $entries = model('select_entries', [
        'select' => 'code, title',
        'where'  => 'code = ' . db_escape('test1'),
    ]);

    test_equals('insert entry', count($entries), 1);
    test_equals('insert entry title', $entries[0]['title'], 'テスト1');

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'where' => 'exec = ' . db_escape('insert'),
    ]);

    test_equals('insert entry log', count($logs), 1);
    test_equals('insert entry log model', $logs[0]['model'], 'entries');
    test_equals('insert entry log ip', $logs[0]['ip'], '127.0.0.1');
}

// 登録したエントリーのIDを取得
$entries = model('select_entries', [
    'select' => 'id',
    'where'  => 'code = ' . db_escape('test1'),
]);
$inserted_id = intval($entries[0]['id']);

// エントリーの更新テスト
{
    // 更新
    service_entry_update([
        'set'   => [
            'title' => 'テスト1改',
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

    // 結果
    $entries = model('select_entries', [
        'select' => 'title',
        'where'  => 'id = ' . $inserted_id,
    ]);

    test_equals('update entry', $entries[0]['title'], 'テスト1改');

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'where' => 'exec = ' . db_escape('update'),
    ]);

    test_equals('update entry log', count($logs), 1);
    test_equals('update entry log model', $logs[0]['model'], 'entries');
}

// 最終編集日時の確認テスト
{
    // 更新（編集開始後に更新されていないので、競合とは判定されない）
    service_entry_update([
        'set'   => [
            'title' => 'テスト1改改',
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

    // 結果
    $entries = model('select_entries', [
        'select' => 'title',
        'where'  => 'id = ' . $inserted_id,
    ]);

    test_equals('update entry (modified check)', $entries[0]['title'], 'テスト1改改');
}

// 並び順の一括変更テスト
{
    // データ
    $sort_ids = [
        $entry_ids['pub_all'],
        $entry_ids['pub_user'],
        $entry_ids['pub_none'],
    ];

    // 並び順を更新
    service_entry_sort([
        $sort_ids[0] => 3,
        $sort_ids[1] => 2,
        $sort_ids[2] => 1,
    ]);

    // 結果
    $entries = model('select_entries', [
        'select'   => 'sort',
        'where'    => 'id IN(' . implode(',', $sort_ids) . ')',
        'order_by' => 'id',
    ]);

    test_equals('sort entries', array_column($entries, 'sort'), [3, 2, 1]);

    // 結果（不正な値は無視されること）
    service_entry_sort([
        $sort_ids[0] => 'あ', // 並び順が数字でない
        'あ'         => 1,    // IDが不正
    ]);

    $entries = model('select_entries', [
        'select'   => 'sort',
        'where'    => 'id IN(' . implode(',', $sort_ids) . ')',
        'order_by' => 'id',
    ]);

    test_equals('sort entries (invalid)', array_column($entries, 'sort'), [3, 2, 1]);
}

// 操作ログの重複抑止テスト
{
    // 結果（service_log_record() は同じ model と exec の組み合わせを1リクエストにつき1回しか記録しないため、更新を繰り返してもログは増えない）
    $logs = model('select_logs', [
        'where' => 'exec = ' . db_escape('update'),
    ]);

    test_equals('record entry log once', count($logs), 1);
}

// エントリーの削除テスト
{
    // 削除（ファイルの削除は実際のストレージを操作するため行わない）
    service_entry_delete([
        'where' => [
            'id = :id',
            [
                'id' => $inserted_id,
            ],
        ],
    ], [
        'file' => false,
    ]);

    // 結果
    $entries = model('select_entries', [
        'where' => 'id = ' . $inserted_id,
    ]);

    test_equals('delete entry', count($entries), 0);

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'where' => 'exec = ' . db_escape('delete'),
    ]);

    test_equals('delete entry log', count($logs), 1);
    test_equals('delete entry log model', $logs[0]['model'], 'entries');
}

// 親のコードの取得テスト
{
    // データ
    $codes = ['about', 'about/staff', 'about/staff/yamada', 'about/history/2026', 'news/2026', 'About'];

    // 結果（直接の親）
    test_equals('parent code', service_entry_parent('about/staff', $codes), 'about');
    test_equals('parent code (deep)', service_entry_parent('about/staff/yamada', $codes), 'about/staff');

    // 結果（途中の階層が無ければ、最も近い祖先）
    test_equals('parent code (nearest ancestor)', service_entry_parent('about/history/2026', $codes), 'about');

    // 結果（祖先が無ければ親は無い）
    test_equals('parent code (no ancestor)', service_entry_parent('news/2026', $codes), null);
    test_equals('parent code (no slash)', service_entry_parent('about', $codes), null);

    // 結果（大文字と小文字は区別する）
    test_equals('parent code (case sensitive)', service_entry_parent('ABOUT/company', $codes), null);
    test_equals('parent code (case sensitive match)', service_entry_parent('About/company', $codes), 'About');

    // 結果（前方が一致するだけのコードは親ではない）
    test_equals('parent code (prefix only)', service_entry_parent('aboutus/company', $codes), null);
}

// ページのコードの一括取得テスト
{
    // データ（ページを2件登録して、1件を削除する）
    foreach (['test_page', 'test_page/deleted'] as $code) {
        model('insert_entries', [
            'values' => test_entry_values([
                'type_id' => 2,
                'code'    => $code,
            ]),
        ]);
    }
    model('delete_entries', [
        'where' => 'code = ' . db_escape('test_page/deleted'),
    ], [
        'file' => false,
    ]);

    // 確認
    $codes = service_entry_page_codes();

    // 結果（ページだけを取得し、削除済みは含まない）
    test_equals('page codes', in_array('test_page', $codes, true), true);
    test_equals('page codes (other type)', in_array('pub_all', $codes, true), false);
    test_equals('page codes (deleted)', in_array('test_page/deleted', $codes, true), false);
}

// 親ページの階層順の取得テスト
{
    // データ（test_page/none は登録しない）
    foreach (['test_page/child' => '子', 'test_page/none/grand' => '離れた孫', 'test_page/child/grand' => '孫'] as $code => $title) {
        model('insert_entries', [
            'values' => test_entry_values([
                'type_id' => 2,
                'code'    => $code,
                'title'   => $title,
            ]),
        ]);
    }

    // 結果（一覧のトップに近い順）
    $parents = service_entry_page_parents('test_page/child/grand');

    test_equals('page parents', array_column($parents, 'code'), ['test_page', 'test_page/child', 'test_page/child/grand']);
    test_equals('page parents title', $parents[1]['title'], '子');

    // 結果（途中の階層が無ければ飛ばす）
    $parents = service_entry_page_parents('test_page/none/grand');

    test_equals('page parents (nearest ancestor)', array_column($parents, 'code'), ['test_page', 'test_page/none/grand']);

    // 結果（親が無い）
    test_equals('page parents (null)', service_entry_page_parents(null), []);
}

// ページ一覧のURLの作成テスト
{
    test_equals('page list url', service_entry_page_list('ok=post', null), '/admin/page?ok=post');
    test_equals('page list url (parent)', service_entry_page_list('ok=post', 'about/staff'), '/admin/page?ok=post&parent=about%2Fstaff');
}

// 公開側の詳細ページのURLの取得テスト
{
    // 結果（ページは /page/<コード>、それ以外は /<型コード>/detail/<コード>）
    test_equals('entry url (entry)', service_entry_url(['type_code' => 'entry', 'code' => 'hello']), '/entry/detail/hello');
    test_equals('entry url (page)', service_entry_url(['type_code' => 'page', 'code' => 'about/staff']), '/page/about/staff');
    test_equals('entry url (plugin type)', service_entry_url(['type_code' => 'gallery', 'code' => 'work1']), '/gallery/detail/work1');
}

// 公開側のエントリー一覧のURLの作成テスト
{
    // 結果（1ページ目は page/ を付けない。カテゴリーと年月の両方を渡したらカテゴリーを使う）
    test_equals('list url', service_entry_list_url(), '/entry/');
    test_equals('list url (page)', service_entry_list_url(null, null, 2), '/entry/page/2/');
    test_equals('list url (category)', service_entry_list_url('news'), '/entry/category/news/');
    test_equals('list url (category page)', service_entry_list_url('news', null, 3), '/entry/category/news/page/3/');
    test_equals('list url (archive)', service_entry_list_url(null, '2026-10'), '/entry/archive/2026-10/');
    test_equals('list url (archive page 1)', service_entry_list_url(null, '2026-10', 1), '/entry/archive/2026-10/');
    test_equals('list url (both)', service_entry_list_url('news', '2026-10'), '/entry/category/news/');
}

// 公開側のエントリー一覧のパスの解析テスト
{
    // 結果（末尾のスラッシュの有無に関係なく解析する）
    test_equals('list parse (empty)', service_entry_list_parse([]), ['category' => null, 'archive' => null, 'page' => 1]);
    test_equals('list parse (slash)', service_entry_list_parse(['']), ['category' => null, 'archive' => null, 'page' => 1]);
    test_equals('list parse (page)', service_entry_list_parse(['page', '2', '']), ['category' => null, 'archive' => null, 'page' => 2]);
    test_equals('list parse (category)', service_entry_list_parse(['category', 'news', '']), ['category' => 'news', 'archive' => null, 'page' => 1]);
    test_equals('list parse (category page)', service_entry_list_parse(['category', 'news', 'page', '3']), ['category' => 'news', 'archive' => null, 'page' => 3]);
    test_equals('list parse (archive)', service_entry_list_parse(['archive', '2026-10', '']), ['category' => null, 'archive' => '2026-10', 'page' => 1]);

    // 結果（形が正しくなければ null）
    test_equals('list parse (unknown)', service_entry_list_parse(['foo']), null);
    test_equals('list parse (category only)', service_entry_list_parse(['category', '']), null);
    test_equals('list parse (archive format)', service_entry_list_parse(['archive', '2026-1']), null);
    test_equals('list parse (page zero)', service_entry_list_parse(['page', '0']), null);
    test_equals('list parse (page string)', service_entry_list_parse(['page', 'abc']), null);
    test_equals('list parse (category and archive)', service_entry_list_parse(['category', 'news', 'archive', '2026-10']), null);
    test_equals('list parse (page first)', service_entry_list_parse(['page', '2', 'category', 'news']), null);
    test_equals('list parse (extra)', service_entry_list_parse(['category', 'news', 'page', '2', 'foo']), null);
}

// 公開側のエントリー一覧の絞り込み条件の変換テスト
{
    // データ（エントリーとページのカテゴリー）
    foreach ([1 => 'test_list_entry', 2 => 'test_list_page'] as $type_id => $code) {
        model('insert_categories', [
            'values' => [
                'type_id' => $type_id,
                'code'    => $code,
                'name'    => 'テスト',
                'sort'    => 1,
            ],
        ]);
    }
    $list_categories = array_column(model('select_categories', [
        'where' => 'code LIKE ' . db_escape('test_list_%'),
    ]), 'id', 'code');

    // 結果（カテゴリー1つ・年月・ページはパスで表せる。カテゴリーはコードにする）
    test_equals('list filter (empty)', service_entry_list_filter([]), ['category' => null, 'archive' => null, 'page' => 1]);
    test_equals('list filter (page)', service_entry_list_filter(['page' => '2']), ['category' => null, 'archive' => null, 'page' => 2]);
    test_equals('list filter (page int)', service_entry_list_filter(['page' => 2]), ['category' => null, 'archive' => null, 'page' => 2]);
    test_equals('list filter (category)', service_entry_list_filter(['category_sets' => [$list_categories['test_list_entry']], 'page' => '3']), ['category' => 'test_list_entry', 'archive' => null, 'page' => 3]);
    test_equals('list filter (archive)', service_entry_list_filter(['archive' => '2026-10']), ['category' => null, 'archive' => '2026-10', 'page' => 1]);
    test_equals('list filter (empty values)', service_entry_list_filter(['category_sets' => [], 'archive' => '', 'keyword' => '']), ['category' => null, 'archive' => null, 'page' => 1]);
    test_equals('list filter (other key)', service_entry_list_filter(['test' => 'start', 'archive' => '2026-10']), ['category' => null, 'archive' => '2026-10', 'page' => 1]);

    // 結果（パスで表せない絞り込みは null）
    test_equals('list filter (categories)', service_entry_list_filter(['category_sets' => [$list_categories['test_list_entry'], $list_categories['test_list_entry']]]), null);
    test_equals('list filter (category and archive)', service_entry_list_filter(['category_sets' => [$list_categories['test_list_entry']], 'archive' => '2026-10']), null);
    test_equals('list filter (category of other type)', service_entry_list_filter(['category_sets' => [$list_categories['test_list_page']]]), null);
    test_equals('list filter (category not found)', service_entry_list_filter(['category_sets' => [0]]), null);
    test_equals('list filter (category string)', service_entry_list_filter(['category_sets' => 'abc']), null);
    test_equals('list filter (category array)', service_entry_list_filter(['category_sets' => [['1']]]), null);
    test_equals('list filter (archive format)', service_entry_list_filter(['archive' => '2026-1']), null);
    test_equals('list filter (archive array)', service_entry_list_filter(['archive' => ['2026-10']]), null);
    test_equals('list filter (page zero)', service_entry_list_filter(['page' => '0']), null);
    test_equals('list filter (page string)', service_entry_list_filter(['page' => 'abc']), null);
    test_equals('list filter (keyword)', service_entry_list_filter(['keyword' => 'a']), null);
    test_equals('list filter (title)', service_entry_list_filter(['title' => 'a', 'page' => '2']), null);
    test_equals('list filter (field_sets)', service_entry_list_filter(['field_sets' => ['1']]), null);
}

// パスワード認証テスト
{
    // データ
    $entry_passwords = isset($_SESSION['entry_passwords']) ? $_SESSION['entry_passwords'] : null;
    unset($_SESSION['entry_passwords']);

    $test_entry = [
        'id'       => $entry_ids['pub_password'],
        'password' => 'abcd',
    ];

    // 結果（誤ったパスワードでは認証されず、セッションにも記録されないこと）
    test_equals('entry password (wrong)', service_entry_password($test_entry, 'abcde'), false);
    test_equals('entry password (wrong session)', isset($_SESSION['entry_passwords'][$entry_ids['pub_password']]), false);
    test_equals('entry password (empty)', service_entry_password($test_entry, ''), false);

    // 結果（正しいパスワードで認証され、セッションに記録されること）
    test_equals('entry password (correct)', service_entry_password($test_entry, 'abcd'), true);
    test_equals('entry password (correct session)', $_SESSION['entry_passwords'][$entry_ids['pub_password']], true);

    // セッションを元に戻す
    if ($entry_passwords === null) {
        unset($_SESSION['entry_passwords']);
    } else {
        $_SESSION['entry_passwords'] = $entry_passwords;
    }
}

// アップロードされたファイルの一時ファイルテスト
{
    // データ（一時ファイルの置き場所をテスト用のディレクトリに向け、アップロードされたファイルの代わりを置く）
    service_storage_init();

    $temp_target  = $GLOBALS['config']['file_target']['temp'];
    $session_file = $_SESSION['file'] ?? null;

    $GLOBALS['config']['file_target']['temp'] = $GLOBALS['config']['file_target']['temp'] . 'test_entry_file/';

    directory_rmdir($GLOBALS['config']['file_target']['temp']);
    directory_mkdir($GLOBALS['config']['file_target']['temp']);
    file_put_contents($GLOBALS['config']['file_target']['temp'] . 'upload.tmp', '画像のつもりのデータ');

    // 確認（保存）
    $file = service_entry_file_upload('写真.jpg', $GLOBALS['config']['file_target']['temp'] . 'upload.tmp');

    // 結果（名前と一時ファイルの場所だけを返し、内容は一時ファイルに入ること）
    test_equals('entry file upload name', $file['name'], '写真.jpg');
    test_array_not_haskey('entry file upload data', $file, 'data');
    test_regexp('entry file upload temp', $file['temp'], '^' . preg_quote($GLOBALS['config']['file_target']['temp'], '/') . '.*_entry_[0-9a-f]{32}$');
    test_equals('entry file upload content', file_get_contents($file['temp']), '画像のつもりのデータ');

    // 結果（内容の取得。一時ファイルが無ければ null、以前の形なら data を返すこと）
    test_equals('entry file data', service_entry_file_data($file), '画像のつもりのデータ');
    test_equals('entry file data (no temp file)', service_entry_file_data(['name' => 'a.jpg', 'temp' => $GLOBALS['config']['file_target']['temp'] . 'nothing']), null);
    test_equals('entry file data (data)', service_entry_file_data(['name' => 'a.jpg', 'data' => '内容']), '内容');

    // 確認（破棄。一括アップロードと単体のアップロードの一時ファイルが両方消えること）
    $file2 = service_entry_file_upload('b.jpg', $GLOBALS['config']['file_target']['temp'] . 'upload.tmp');

    $_SESSION['file'] = [
        'entry' => [
            'pictures'  => [$file],
            'thumbnail' => $file2,
        ],
    ];

    service_entry_file_clear();

    // 結果
    test_equals('entry file clear session', isset($_SESSION['file']), false);
    test_equals('entry file clear temp (pictures)', is_file($file['temp']), false);
    test_equals('entry file clear temp (thumbnail)', is_file($file2['temp']), false);

    // 設定とセッションを元に戻す
    directory_rmdir($GLOBALS['config']['file_target']['temp']);

    $GLOBALS['config']['file_target']['temp'] = $temp_target;

    if ($session_file !== null) {
        $_SESSION['file'] = $session_file;
    }
}

// トランザクションを終了
db_rollback();

// 権限と設定を元に戻す
unset($GLOBALS['authority']['power']);
unset($GLOBALS['attributes']);

if ($setting_title === null) {
    unset($GLOBALS['setting']['restricted_password_title']);
} else {
    $GLOBALS['setting']['restricted_password_title'] = $setting_title;
}
if ($setting_text === null) {
    unset($GLOBALS['setting']['restricted_password_text']);
} else {
    $GLOBALS['setting']['restricted_password_text'] = $setting_text;
}

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'entries;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'attributes;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'attribute_sets;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/entry.php',
    ]);
}

/**
 * エントリーの登録内容を作成
 *
 * 指定しなかった項目は、公開中のエントリーの値で補う。
 *
 * @param array $values
 *
 * @return array
 */
function test_entry_values($values)
{
    return array_merge([
        'type_id'      => 1,
        'approved'     => 1,
        'public'       => 'all',
        'public_begin' => '',
        'public_end'   => '',
        'password'     => '',
        'datetime'     => '2026-01-01 10:00:00',
        'code'         => '',
        'title'        => 'タイトル',
        'text'         => '本文',
        'text_type'    => 'html',
        'pictures'     => '',
        'thumbnail'    => '',
        'comment'      => 'closed',
        'sort'         => 1,
    ], $values);
}
