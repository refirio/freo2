<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// プラグインと設定を退避（テストの中で書き換える）
$plugin_backup  = isset($GLOBALS['plugin']) ? $GLOBALS['plugin'] : null;
$setting_backup = isset($GLOBALS['setting']) ? $GLOBALS['setting'] : null;

// データの定義の取得テスト（data を持つプラグインが無いとき）
{
    // データ
    $GLOBALS['plugin'] = [
        'nodata' => [
            'code' => 'nodata',
        ],
    ];

    // 確認
    $data = app_data();

    // 結果（本体の定義だけ）
    test_equals('data body only', array_keys($data), array_keys($GLOBALS['config']['data']));
    test_equals('data body entry', $data['entry']['type'], 'entry');
    test_equals('data body plugin', $data['entry']['plugin'], null);
    test_equals('data body category', $data['category']['table'], 'categories');
}

// データの定義の取得テスト（プラグインが無いとき）
{
    // データ
    unset($GLOBALS['plugin']);

    // 確認
    $data = app_data();

    // 結果
    test_equals('data no plugin', array_keys($data), array_keys($GLOBALS['config']['data']));
}

// データの定義の取得テスト（data を持つプラグイン）
{
    // データ
    $GLOBALS['plugin'] = [
        'gallerytest' => [
            'code'            => 'gallerytest',
            'data'            => [
                'gallerytest' => [
                    'type'    => 'gallerytest',
                    'name'    => 'テストギャラリー',
                    'form'    => '/admin/gallerytest_form',
                    'setting' => [
                        'use_approve'   => 'use_approve',
                        'use_thumbnail' => 'use_thumbnail',
                        'default_code'  => 'default_code',
                    ],
                ],
                'entry'       => [
                    'type' => 'other',
                    'name' => '本体と同じコード',
                ],
                'broken'      => 'not array',
                'ruletest'    => [
                    'table' => 'ruletest_rules',
                    'name'  => 'テストのルール',
                ],
            ],
            'setting'         => [
                'use_approve' => 1,
            ],
            'setting_default' => [
                'use_approve'   => 0,
                'use_thumbnail' => 1,
            ],
        ],
        'brokentest'  => [
            'code' => 'brokentest',
            'data' => 'not array',
        ],
    ];

    // 確認
    $data = app_data();

    // 結果
    test_equals('data plugin added', $data['gallerytest']['name'], 'テストギャラリー');
    test_equals('data plugin code', $data['gallerytest']['plugin'], 'gallerytest');
    test_equals('data plugin table', $data['ruletest']['plugin'], 'gallerytest');
    test_equals('data plugin cannot override body', $data['entry']['type'], 'entry');
    test_array_not_haskey('data plugin broken definition', $data, 'broken');
    test_equals('data plugin count', count($data), count($GLOBALS['config']['data']) + 2);
}

// 型・テーブルからの取得テスト
{
    // 結果
    test_equals('data type body', app_data_type('page')['code'], 'page');
    test_equals('data type plugin', app_data_type('gallerytest')['code'], 'gallerytest');
    test_equals('data type plugin form', app_data_type('gallerytest')['form'], '/admin/gallerytest_form');
    test_equals('data type unknown', app_data_type('nothing'), null);
    test_equals('data table body', app_data_table('categories')['name'], 'カテゴリー');
    test_equals('data table plugin', app_data_table('ruletest_rules')['code'], 'ruletest');
    test_equals('data table unknown', app_data_table('nothing'), null);
    test_equals('data table entries', app_data_table('entries'), null);
}

// 設定の取得テスト
{
    // データ
    $GLOBALS['setting'] = [
        'entry_use_approve'  => 1,
        'entry_default_code' => null,
    ];
    $test_entry   = app_data_type('entry');
    $test_gallery = app_data_type('gallerytest');

    // 結果（本体は本体の設定から）
    test_equals('data setting body', app_data_setting($test_entry, 'use_approve'), 1);
    test_equals('data setting body null', app_data_setting($test_entry, 'default_code', 'YmdHis'), null);
    test_equals('data setting body not saved', app_data_setting($test_entry, 'text_type', 'wysiwyg'), 'wysiwyg');

    // 結果（プラグインはプラグインの設定から。保存されていなければ設定の初期値）
    test_equals('data setting plugin', app_data_setting($test_gallery, 'use_approve'), 1);
    test_equals('data setting plugin default', app_data_setting($test_gallery, 'use_thumbnail'), 1);
    test_equals('data setting plugin not saved', app_data_setting($test_gallery, 'default_code', ''), '');

    // 結果（setting に書いていない項目は既定値）
    test_equals('data setting not defined', app_data_setting($test_gallery, 'use_pictures', false), false);
    test_equals('data setting no setting', app_data_setting(app_data_table('categories'), 'use_approve', 0), 0);
}

// プラグインと設定を元に戻す
if ($plugin_backup === null) {
    unset($GLOBALS['plugin']);
} else {
    $GLOBALS['plugin'] = $plugin_backup;
}
if ($setting_backup === null) {
    unset($GLOBALS['setting']);
} else {
    $GLOBALS['setting'] = $setting_backup;
}

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/bootstrap.php',
    ]);
}
