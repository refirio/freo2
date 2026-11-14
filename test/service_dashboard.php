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
model('contacts.php');
model('users.php');
service('dashboard.php');

// プラグインと設定を退避（テストの中で書き換える）
$plugin_backup  = isset($GLOBALS['plugin']) ? $GLOBALS['plugin'] : null;
$setting_backup = isset($GLOBALS['setting']) ? $GLOBALS['setting'] : null;

// ボックスの整形テスト
{
    // 確認
    $box = service_dashboard_normalize([
        'name' => 'テスト',
    ]);

    // 結果（省略した項目は既定値）
    test_equals('normalize group default', $box['group'], 'content');
    test_equals('normalize count default', $box['count'], 0);
    test_equals('normalize list default', $box['list'], null);
    test_equals('normalize label default', $box['label'], null);
    test_equals('normalize other default', $box['other'], null);

    // 結果（名前の無いボックス・配列でないボックスは null）
    test_equals('normalize no name', service_dashboard_normalize(['count' => 1]), null);
    test_equals('normalize empty name', service_dashboard_normalize(['name' => '']), null);
    test_equals('normalize not array', service_dashboard_normalize('not array'), null);
}

// 本体のボックスのテスト
{
    // データ（プラグインは無し）
    unset($GLOBALS['plugin']);
    $GLOBALS['setting'] = [
        'entry_use_approve' => 1,
        'page_use_approve'  => 0,
    ];

    // 確認
    $dashboard = service_dashboard_boxes(3);
    $contents  = array_column($dashboard['content']['boxes'], null, 'name');

    // 結果（段の並びと名前）
    test_equals('body groups', array_keys($dashboard), ['content', 'communication', 'system']);
    test_equals('body group name', $dashboard['communication']['name'], 'コミュニケーション');

    // 結果（コンテンツは型ごと。承認を使う型は未承認、使わない型は非公開）
    test_equals('body content entry list', $contents['エントリー']['list'], '/admin/entry');
    test_equals('body content entry label', $contents['エントリー']['label'], '未承認');
    test_equals('body content page label', $contents['ページ']['label'], '非公開');

    // 結果（件数はテーブルの件数と同じ）
    $count = db_select([
        'select' => 'COUNT(*) AS count',
        'from'   => DATABASE_PREFIX . 'contacts',
        'where'  => 'deleted IS NULL',
    ]);
    test_equals('body contact count', intval($dashboard['communication']['boxes'][0]['count']), intval($count[0]['count']));
    test_equals('body contact label', $dashboard['communication']['boxes'][0]['label'], '未対応');

    $count = db_select([
        'select' => 'COUNT(*) AS count',
        'from'   => DATABASE_PREFIX . 'users',
        'where'  => 'deleted IS NULL',
    ]);
    test_equals('body user count', intval($dashboard['system']['boxes'][0]['count']), intval($count[0]['count']));
    test_equals('body user list', $dashboard['system']['boxes'][0]['list'], '/admin/user');
}

// 権限ごとの段のテスト
{
    // 結果（投稿者はシステムを見られない、閲覧者はコミュニケーションだけ）
    test_equals('power 2 groups', array_keys(service_dashboard_boxes(2)), ['content', 'communication']);
    test_equals('power 1 groups', array_keys(service_dashboard_boxes(1)), ['communication']);
    test_equals('power 0 groups', array_keys(service_dashboard_boxes(0)), []);
}

// プラグインのボックスのテスト
{
    // データ
    $GLOBALS['plugin'] = [
        'dashtest'    => [
            'code'      => 'dashtest',
            'dashboard' => 'test_dashboard_boxes',
        ],
        'missingtest' => [
            'code'      => 'missingtest',
            'dashboard' => 'test_dashboard_missing',
        ],
        'brokentest'  => [
            'code'      => 'brokentest',
            'dashboard' => 'test_dashboard_broken',
        ],
        'nodashboard' => [
            'code' => 'nodashboard',
        ],
    ];

    // 確認
    $dashboard = service_dashboard_boxes(3);
    $contents  = array_column($dashboard['content']['boxes'], 'name');
    $communications = array_column($dashboard['communication']['boxes'], 'name');

    // 結果（本体のボックスのあとに並ぶ。段を省略したボックスはコンテンツ）
    test_equals('plugin content last', end($contents), 'テストの注文');
    test_equals('plugin content after body', array_search('テストの注文', $contents) > array_search('ページ', $contents), true);
    test_equals('plugin communication', $communications, ['お問い合わせ', 'テストの問い合わせ']);

    // 結果（名前の無いボックス・知らない段は出さない。関数が無い・配列を返さないプラグインは何も足さない）
    test_equals('plugin box without name', in_array('', $contents, true), false);
    test_array_not_haskey('plugin unknown group', $dashboard, 'unknown');
    test_equals('plugin box count', count($contents), count($GLOBALS['config']['data']) + 1);

    // 結果（ボックスの値）
    $box = $dashboard['content']['boxes'][count($contents) - 1];
    test_equals('plugin box count value', $box['count'], 18);
    test_equals('plugin box list', $box['list'], '/admin/dashtest');
    test_equals('plugin box label', $box['label'], '未完了');
    test_equals('plugin box other', $box['other'], 3);

    // 結果（閲覧者には、コンテンツの段のボックスは出ない）
    test_equals('plugin power 1', array_keys(service_dashboard_boxes(1)), ['communication']);
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
        'app/services/dashboard.php',
    ]);
}

/**
 * テスト用のボックス
 *
 * @return array
 */
function test_dashboard_boxes()
{
    return [
        [
            'name'  => 'テストの注文',
            'count' => 18,
            'list'  => '/admin/dashtest',
            'label' => '未完了',
            'other' => 3,
        ],
        [
            'group' => 'communication',
            'name'  => 'テストの問い合わせ',
        ],
        [
            'count' => 1,
        ],
        [
            'group' => 'unknown',
            'name'  => '知らない段',
        ],
    ];
}

/**
 * 配列を返さないテスト用の関数
 *
 * @return string
 */
function test_dashboard_broken()
{
    return 'not array';
}
