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
model('menus.php');
model('widgets.php');
model('types.php');
service('export.php');

// 設定を固定する（設定によって結果が変わらないようにする）
$setting_backups = [];
foreach (['number_limit_entry' => 2, 'page_url_omission' => 0, 'export_url' => 'https://static.example.com/sub/'] as $key => $value) {
    $setting_backups[$key] = isset($GLOBALS['setting'][$key]) ? $GLOBALS['setting'][$key] : null;

    $GLOBALS['setting'][$key] = $value;
}
$plugin_backup = isset($GLOBALS['plugin']) ? $GLOBALS['plugin'] : null;
$GLOBALS['plugin'] = [];

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'entries;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'category_sets;');

// トランザクションを開始
db_transaction();

// データ（エントリー・ページと、エントリーのカテゴリー）
model('insert_categories', [
    'values' => [
        'type_id' => 1,
        'code'    => 'test_export_category',
        'name'    => 'テスト',
        'sort'    => 1,
    ],
]);
$export_categories = model('select_categories', [
    'where' => 'code = ' . db_escape('test_export_category'),
]);

foreach ([
    ['code' => 'export_all1', 'datetime' => '2026-01-10 10:00:00', 'thumbnail' => 'thumb.jpg', 'comment' => 'opened'],
    ['code' => 'export_all2', 'datetime' => '2026-01-20 10:00:00'],
    ['code' => 'export_all3', 'datetime' => '2026-02-10 10:00:00'],
    ['code' => 'export_user', 'public' => 'user'],
    ['code' => 'export_password', 'public' => 'password', 'password' => 'abcd'],
    ['code' => 'export_none', 'public' => 'none'],
    ['code' => 'export_future', 'public_begin' => '2099-01-01 00:00:00'],
    ['code' => 'export_page', 'type_id' => 2],
    ['code' => 'export_page/child', 'type_id' => 2, 'pictures' => 'a.jpg'],
    ['code' => 'export_page_user', 'type_id' => 2, 'public' => 'user'],
] as $values) {
    model('insert_entries', [
        'values' => array_merge([
            'type_id'      => 1,
            'approved'     => 1,
            'public'       => 'all',
            'public_begin' => '',
            'public_end'   => '',
            'password'     => '',
            'datetime'     => '2026-01-01 10:00:00',
            'title'        => 'タイトル',
            'text'         => '本文',
            'text_type'    => 'html',
            'pictures'     => '',
            'thumbnail'    => '',
            'comment'      => 'closed',
            'sort'         => 1,
        ], $values),
    ]);
}
$export_entries = model('select_entries', [
    'where' => 'code = ' . db_escape('export_all1'),
]);
model('insert_category_sets', [
    'values' => [
        'category_id' => $export_categories[0]['id'],
        'entry_id'    => $export_entries[0]['id'],
    ],
]);

// 出力するURLの一覧の作成テスト
{
    // データ（管理者がログインしている状態にする）
    $GLOBALS['authority']  = ['power' => 3];
    $GLOBALS['attributes'] = [];

    // 確認
    $export_urls  = service_export_urls();
    $export_files = array_column($export_urls, 'file', 'url');

    // 結果（トップページと、エントリーの一覧のページ送り。3件を2件ずつ）
    test_equals('urls home', $export_files['/'], 'index.html');
    test_equals('urls list', $export_files['/entry/'], 'entry/index.html');
    test_equals('urls list page', $export_files['/entry/page/2/'], 'entry/page/2/index.html');
    test_array_not_haskey('urls list page over', $export_files, '/entry/page/3/');

    // 結果（カテゴリーと月）
    test_array_haskey('urls category', $export_files, '/entry/category/test_export_category/');
    test_array_not_haskey('urls category page', $export_files, '/entry/category/test_export_category/page/2/');
    test_array_haskey('urls archive', $export_files, '/entry/archive/2026-01/');
    test_array_haskey('urls archive other', $export_files, '/entry/archive/2026-02/');
    test_array_not_haskey('urls archive page', $export_files, '/entry/archive/2026-01/page/2/');

    // 結果（詳細は「全体に公開」のものだけ。管理者がログインしていても、会員向けの記事は入らない）
    test_equals('urls detail', $export_files['/entry/detail/export_all1'], 'entry/detail/export_all1/index.html');
    test_array_haskey('urls detail other', $export_files, '/entry/detail/export_all3');
    test_array_not_haskey('urls detail user', $export_files, '/entry/detail/export_user');
    test_array_not_haskey('urls detail password', $export_files, '/entry/detail/export_password');
    test_array_not_haskey('urls detail none', $export_files, '/entry/detail/export_none');
    test_array_not_haskey('urls detail future', $export_files, '/entry/detail/export_future');

    // 結果（ページ、画像のページ）
    test_equals('urls page', $export_files['/page/export_page/child'], 'page/export_page/child/index.html');
    test_array_haskey('urls page parent', $export_files, '/page/export_page');
    test_array_not_haskey('urls page user', $export_files, '/page/export_page_user');
    test_array_haskey('urls file entry', $export_files, '/file/entry/export_all1');
    test_array_haskey('urls file page', $export_files, '/file/page/export_page/child');
    test_array_not_haskey('urls file none', $export_files, '/file/entry/export_all2');
    test_array_not_haskey('urls page omission', $export_files, '/export_page');

    // 結果（RSS と 404 のページ）
    test_equals('urls feed', $export_files['/entry/feed'], 'entry/feed.xml');
    test_equals('urls 404', array_search('404.html', $export_files), '/export-not-found/');

    // 結果（権限と属性は元に戻る）
    test_equals('urls authority', $GLOBALS['authority'], ['power' => 3]);
    test_equals('urls attributes', $GLOBALS['attributes'], []);

    unset($GLOBALS['authority'], $GLOBALS['attributes']);
}

// ページURLの省略とプラグインのページのテスト
{
    // データ
    $GLOBALS['setting']['page_url_omission'] = 1;
    $GLOBALS['plugin'] = [
        'test_export_array'   => ['name' => '配列', 'setting' => [], 'export' => ['/test_export/', '/test_export/detail/a']],
        'test_export_none'    => ['name' => '非対応', 'setting' => [], 'export' => false],
        'test_export_unknown' => ['name' => '不明', 'setting' => []],
        'test_export_off'     => ['name' => '無効', 'export' => ['/test_export_off/']],
    ];

    // 確認
    $export_files = array_column(service_export_urls(), 'file', 'url');

    // 結果（省略したURLも出力する。プラグインは有効なもの（setting があるもの）の export だけ）
    test_array_haskey('urls page omission (enabled)', $export_files, '/export_page');
    test_equals('urls plugin', $export_files['/test_export/'], 'test_export/index.html');
    test_array_haskey('urls plugin detail', $export_files, '/test_export/detail/a');
    test_array_not_haskey('urls plugin disabled', $export_files, '/test_export_off/');

    // 結果（対応の状況）
    $export_plugins = service_export_plugins();

    test_equals('plugins count', count($export_plugins), 3);
    test_equals('plugins none', $export_plugins['test_export_none']['export'], false);
    test_equals('plugins unknown', $export_plugins['test_export_unknown']['export'], null);

    $GLOBALS['setting']['page_url_omission'] = 0;
}

// 出力する前の確認テスト
{
    // 確認
    $export_check = service_export_check();
    $export_text  = implode("\n", $export_check['notices']);

    // 結果（出力できる。全体に公開以外の4件、コメントを受け付ける1件、公開予約の1件、プラグインの2件を知らせる）
    test_equals('check errors', $export_check['errors'], []);
    test_contains('check public', $export_text, '（4件）は出力しません');
    test_contains('check comment', $export_text, '（1件）は、コメントのフォームを出力しません');
    test_contains('check period', $export_text, '公開期間を指定したエントリー・ページ（1件）');
    test_contains('check plugin none', $export_text, 'プラグイン「非対応」は、HTML出力に対応していません。');
    test_contains('check plugin unknown', $export_text, 'プラグイン「不明」は、HTML出力に対応しているか分かりません。');
    test_not_contains('check plugin array', $export_text, '「配列」');

    // データ（公開用URLが無い）
    $GLOBALS['setting']['export_url'] = '';

    // 結果（出力できない）
    $export_check = service_export_check();

    test_equals('check errors (url)', count($export_check['errors']), 1);

    // データ（公開用URLの形が違う）
    $GLOBALS['setting']['export_url'] = 'static.example.com';

    // 結果（出力できない）
    $export_check = service_export_check();

    test_equals('check errors (url format)', count($export_check['errors']), 1);

    // データ（公開用URLに空白がある）
    $GLOBALS['setting']['export_url'] = 'https://static example.com';

    // 結果（出力できない）
    $export_check = service_export_check();

    test_equals('check errors (url space)', count($export_check['errors']), 1);

    // データ（公開用URLの前後に空白がある）
    $GLOBALS['setting']['export_url'] = ' https://static.example.com ';

    // 結果（取り除いて使うので、出力できる）
    $export_check = service_export_check();

    test_equals('check errors (url trim)', $export_check['errors'], []);

    $GLOBALS['setting']['export_url'] = 'https://static.example.com/sub/';
    $GLOBALS['plugin']                = [];
}

// 出力しないページへのメニューの確認テスト
{
    // データ
    foreach ([
        ['title' => 'テスト出力しない', 'url' => '/test_export_none/'],
        ['title' => 'テスト一覧', 'url' => '/entry'],
        ['title' => 'テストページ', 'url' => '/page/export_page/?a=1'],
        ['title' => 'テスト外部', 'url' => 'https://example.com/test_export_none/'],
        ['title' => 'テストお問い合わせ', 'url' => '/contact/'],
        ['title' => 'テスト無効', 'url' => '/test_export_disabled/', 'enabled' => 0],
    ] as $values) {
        model('insert_menus', [
            'values' => array_merge([
                'enabled' => 1,
                'sort'    => 1,
            ], $values),
        ]);
    }

    // 確認
    $export_text = implode("\n", service_export_check()['notices']);

    // 結果（有効なメニューのうち、出力しないサイトの中のページだけを知らせる。末尾の / とクエリ文字列は比べない）
    test_contains('check menu', $export_text, 'メニュー「テスト出力しない」（/test_export_none/）のリンク先は出力しません。');
    test_not_contains('check menu (exported)', $export_text, '「テスト一覧」');
    test_not_contains('check menu (query)', $export_text, '「テストページ」');
    test_not_contains('check menu (external)', $export_text, '「テスト外部」');
    test_not_contains('check menu (contact)', $export_text, '「テストお問い合わせ」');
    test_not_contains('check menu (disabled)', $export_text, '「テスト無効」');
}

// 出力するファイルの取得テスト
{
    // 結果（ディレクトリの index.html にする）
    test_equals('file home', service_export_file('/'), 'index.html');
    test_equals('file slash', service_export_file('/entry/page/2/'), 'entry/page/2/index.html');
    test_equals('file no slash', service_export_file('/entry/detail/hello'), 'entry/detail/hello/index.html');
    test_equals('file query', service_export_file('/sample/?a=1'), 'sample/index.html');
}

// URLの置き換えの内容の取得テスト
{
    // 確認
    $export_replaces = service_export_replaces();

    // 結果（公開用URLのパスと、管理用のURLの置き換え先）
    test_equals('replaces to', $export_replaces['to'], '/sub/');
    test_equals('replaces url', $export_replaces['url'][rtrim($GLOBALS['config']['http_url'], '/') . '/'], 'https://static.example.com/sub/');
    test_equals('replaces path', end($export_replaces['path']), $GLOBALS['config']['http_path']);

    // データ（公開用URLにパスが無い）
    $GLOBALS['setting']['export_url'] = 'https://static.example.com';

    // 結果
    $export_replaces = service_export_replaces();

    test_equals('replaces to (root)', $export_replaces['to'], '/');
    test_equals('replaces url (root)', $export_replaces['url'][rtrim($GLOBALS['config']['http_url'], '/') . '/'], 'https://static.example.com/');

    // データ（公開用URLの前後に空白がある）
    $GLOBALS['setting']['export_url'] = " https://static.example.com/sub \n";

    // 結果（空白を取り除く）
    $export_replaces = service_export_replaces();

    test_equals('replaces to (trim)', $export_replaces['to'], '/sub/');
    test_equals('replaces url (trim)', $export_replaces['url'][rtrim($GLOBALS['config']['http_url'], '/') . '/'], 'https://static.example.com/sub/');

    $GLOBALS['setting']['export_url'] = 'https://static.example.com/sub/';
}

// URLの置き換えテスト
{
    // データ（index.php を含む環境の管理用のURLから、サブディレクトリの公開用URLへ）
    $export_replaces = [
        'url'  => [
            'http://admin.example.com/index.php/' => 'https://static.example.com/sub/',
            'http://admin.example.com/'           => 'https://static.example.com/sub/',
        ],
        'path' => ['/index.php/', '/'],
        'to'   => '/sub/',
    ];

    // 結果（絶対URL）
    test_equals('convert url', service_export_convert('<a href="http://admin.example.com/index.php/entry/">', $export_replaces), '<a href="https://static.example.com/sub/entry/">');
    test_equals('convert url (file)', service_export_convert('<img src="http://admin.example.com/files/medias/a.jpg">', $export_replaces), '<img src="https://static.example.com/sub/files/medias/a.jpg">');

    // 結果（サイトの中の絶対パス。index.php を外し、二重に置き換えない）
    test_equals('convert path', service_export_convert('<a href="/entry/">', $export_replaces), '<a href="/sub/entry/">');
    test_equals('convert path (index.php)', service_export_convert('<a href="/index.php/entry/">', $export_replaces), '<a href="/sub/entry/">');
    test_equals('convert path (single quote)', service_export_convert("<form action='/entry/'>", $export_replaces), "<form action='/sub/entry/'>");
    test_equals('convert path (protocol relative)', service_export_convert('<script src="//cdn.example.com/a.js">', $export_replaces), '<script src="//cdn.example.com/a.js">');
    test_equals('convert path (text)', service_export_convert('<p>/entry/ を開く</p>', $export_replaces), '<p>/entry/ を開く</p>');

    // 結果（RSS は feed.xml。属性とタグの中だけ）
    test_equals('convert feed', service_export_convert('<link href="http://admin.example.com/entry/feed">', $export_replaces), '<link href="https://static.example.com/sub/entry/feed.xml">');
    test_equals('convert feed (xml)', service_export_convert('<docs>http://admin.example.com/entry/feed</docs>', $export_replaces), '<docs>https://static.example.com/sub/entry/feed.xml</docs>');
    test_equals('convert feed (text)', service_export_convert('<p>/entry/feed から</p>', $export_replaces), '<p>/entry/feed から</p>');

    // データ（公開用URLのパスに $ などを含む）
    $export_replaces['to'] = '/$1\\/';

    // 結果（後方参照として扱わない）
    test_equals('convert path (to)', service_export_convert('<a href="/entry/">', $export_replaces), '<a href="/$1\\/entry/">');
}

// ページの描画テスト（コマンドラインから出力するとき。別のプロセスで描画するので、CLI で実行したときだけ確認する）
if (php_sapi_name() !== 'cli') {
    test_skip('render tests', 'CLI で実行したときだけ確認します。');
} else {
    // データ（出力中であることを示す、作業用のディレクトリ）
    $export_token = str_repeat('a', 32);
    $export_dir   = service_export_dir($export_token);

    directory_mkdir($export_dir);

    // 確認
    $export_body       = service_export_render(str_repeat('b', 32), '/');
    $export_token_body = service_export_render($export_token, '/');

    // 結果（画面の HTML を返す。出力中でなければログインのメニューが出て、出力中なら出ない）
    test_contains('render', (string)$export_body, '</html>');
    test_contains('render (not export)', (string)$export_body, '/auth/"');
    test_contains('render (export)', (string)$export_token_body, '</html>');
    test_not_contains('render (export menu)', (string)$export_token_body, '/auth/"');

    // 結果（警告と転送は描画できない扱い）
    test_equals('render warning', service_export_render($export_token, '/entry/foo'), null);
    test_equals('render redirect', service_export_render($export_token, '/entry/?page=2'), null);

    directory_rmdir($export_dir);
}

// 取得したページの保存テスト
{
    // データ
    $export_token = 'test' . str_repeat('0', 28);
    $export_dir   = service_export_dir($export_token);

    directory_mkdir($export_dir . 'html/');

    // 結果（HTML と XML だけ。ディレクトリは作る）
    test_equals('save', service_export_save($export_token, 'entry/detail/a/index.html', 'a'), true);
    test_equals('save content', file_get_contents($export_dir . 'html/entry/detail/a/index.html'), 'a');
    test_equals('save xml', service_export_save($export_token, 'entry/feed.xml', 'b'), true);
    test_equals('save php', service_export_save($export_token, 'a.php', 'c'), false);
    test_equals('save parent', service_export_save($export_token, '../a.html', 'c'), false);
    test_equals('save absolute', service_export_save($export_token, '/a.html', 'c'), false);
    test_equals('save outside', is_file($export_dir . 'a.html'), false);

    // 結果（ディレクトリの複製。. で始まるファイルと PHP のファイルは複製しない）
    directory_mkdir($export_dir . 'from/sub/');
    file_put_contents($export_dir . 'from/a.css', 'a');
    file_put_contents($export_dir . 'from/sub/b.js', 'b');
    file_put_contents($export_dir . 'from/.htaccess', 'c');
    file_put_contents($export_dir . 'from/c.php', 'c');

    test_equals('copy', service_export_copy($export_dir . 'from/', $export_dir . 'to/'), true);
    test_equals('copy file', file_get_contents($export_dir . 'to/sub/b.js'), 'b');
    test_equals('copy dot', is_file($export_dir . 'to/.htaccess'), false);
    test_equals('copy php', is_file($export_dir . 'to/c.php'), false);

    // 結果（ZIP にまとめる）
    test_equals('zip', service_export_zip($export_token), true);

    $zip = new ZipArchive();
    $zip->open($export_dir . 'export.zip');

    test_equals('zip files', $zip->numFiles, 2);
    test_equals('zip name', $zip->getFromName('entry/detail/a/index.html'), 'a');

    $zip->close();

    directory_rmdir($export_dir);
}

// トランザクションを終了
db_rollback();

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'entries;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'category_sets;');

// 設定を戻す
foreach ($setting_backups as $key => $value) {
    $GLOBALS['setting'][$key] = $value;
}
if ($plugin_backup === null) {
    unset($GLOBALS['plugin']);
} else {
    $GLOBALS['plugin'] = $plugin_backup;
}

// コードカバレッジを出力
if (!isset($_GET['_test'])) {
    service_coverage_output(service_coverage_end(), ['app/services/export.php']);
}
