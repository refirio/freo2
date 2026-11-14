<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
model('comments.php');
model('logs.php');
model('users.php');
service('comment.php');

// リクエスト情報を用意
$_SERVER['REMOTE_ADDR']     = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'freo/2';

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'comments;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// 正常データ（公開側のコメント投稿を想定）
$data_comment = [
    'approved' => 1,
    'name'     => 'テスト太郎',
    'url'      => '',
    'message'  => 'コメントの内容です。',
    'memo'     => '',
];

// トランザクションを開始
db_transaction();

// 正常登録テスト
{
    // データ
    $test_comment = $data_comment;

    // 登録
    $warnings = model('validate_comments', $test_comment);
    if (empty($warnings)) {
        service_comment_insert([
            'values' => $test_comment,
        ]);
    } else {
        debug($warnings);
    }

    // 結果
    $comments = model('select_comments', [
        'select'   => 'name, message',
        'order_by' => 'id DESC',
        'limit'    => 10,
    ]);

    test_equals('insert comment', count($comments), 1);
    test_equals('insert comment message', $comments[0]['message'], 'コメントの内容です。');

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'where' => 'exec = ' . db_escape('insert'),
    ]);

    test_equals('insert comment log', count($logs), 1);
    test_equals('insert comment log model', $logs[0]['model'], 'comments');
    test_equals('insert comment log ip', $logs[0]['ip'], '127.0.0.1');
}

// 登録したコメントのIDを取得
$comments = model('select_comments', [
    'select'   => 'id',
    'order_by' => 'id DESC',
    'limit'    => 1,
]);
$inserted_id = intval($comments[0]['id']);

// 更新テスト
{
    // データ（管理画面では承認とメモを変更する）
    $test_comment = [
        'approved' => 0,
        'memo'     => '確認しました。',
    ];

    // 更新
    $warnings = model('validate_comments', $test_comment);
    if (empty($warnings)) {
        service_comment_update([
            'set'   => $test_comment,
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
    $comments = model('select_comments', [
        'select' => 'approved, memo',
        'where'  => 'id = ' . $inserted_id,
    ]);

    test_equals('update comment approved', intval($comments[0]['approved']), 0);
    test_equals('update comment memo', $comments[0]['memo'], '確認しました。');

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'where' => 'exec = ' . db_escape('update'),
    ]);

    test_equals('update comment log', count($logs), 1);
    test_equals('update comment log model', $logs[0]['model'], 'comments');
}

// 最終編集日時の確認テスト
{
    // 更新（編集開始後に更新されていないので、競合とは判定されない）
    service_comment_update([
        'set'   => [
            'memo' => '再確認しました。',
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
    $comments = model('select_comments', [
        'select' => 'memo',
        'where'  => 'id = ' . $inserted_id,
    ]);

    test_equals('update comment (modified check)', $comments[0]['memo'], '再確認しました。');
}

// 操作ログの重複抑止テスト
{
    // 結果（service_log_record() は同じ model と exec の組み合わせを1リクエストにつき1回しか記録しないため、更新を繰り返してもログは増えない）
    $logs = model('select_logs', [
        'where' => 'exec = ' . db_escape('update'),
    ]);

    test_equals('record comment log once', count($logs), 1);
}

// 削除テスト
{
    // 削除
    service_comment_delete([
        'where' => [
            'id = :id',
            [
                'id' => $inserted_id,
            ],
        ],
    ]);

    // 結果
    $comments = model('select_comments', [
        'where' => 'id = ' . $inserted_id,
    ]);

    test_equals('delete comment', count($comments), 0);

    // 結果（操作ログが記録されること）
    $logs = model('select_logs', [
        'where' => 'exec = ' . db_escape('delete'),
    ]);

    test_equals('delete comment log', count($logs), 1);
    test_equals('delete comment log model', $logs[0]['model'], 'comments');
}

// ログイン状態を退避（ブラウザから実行したときに、本物のログイン状態を変えないため）
$auth = isset($_SESSION['auth']) ? $_SESSION['auth'] : null;
unset($_SESSION['auth']);

// コメントの受付の確認テスト
{
    // 結果（受け付ける・受け付けない）
    test_equals('comment acceptable (opened)', service_comment_acceptable(['comment' => 'opened']), true);
    test_equals('comment acceptable (closed)', service_comment_acceptable(['comment' => 'closed']), false);

    // 結果（登録ユーザーのみ受け付ける場合は、ログインしていなければ受け付けない）
    test_equals('comment acceptable (user, guest)', service_comment_acceptable(['comment' => 'user']), false);

    $_SESSION['auth']['user']['id'] = 1;

    test_equals('comment acceptable (user, login)', service_comment_acceptable(['comment' => 'user']), true);
    test_equals('comment acceptable (closed, login)', service_comment_acceptable(['comment' => 'closed']), false);

    unset($_SESSION['auth']);
}

// 公開側の投稿データの整理テスト
{
    // データ（コメント先を書き換えて送信した場合）
    $test_data = [
        'entry_id' => 999,
        'name'     => 'テスト太郎',
        'url'      => 'https://example.com/',
        'message'  => 'コメントの内容です。',
    ];

    // 整理
    $comment = service_comment_normalize($test_data, ['id' => 3]);

    // 結果（コメント先は、送信された値ではなく表示しているエントリーになること）
    test_equals('comment normalize entry_id', $comment['entry_id'], 3);
    test_equals('comment normalize name', $comment['name'], 'テスト太郎');
    test_equals('comment normalize message', $comment['message'], 'コメントの内容です。');

    // 整理（項目が送信されなかった場合）
    $comment = service_comment_normalize([], ['id' => 3]);

    // 結果
    test_equals('comment normalize (empty) name', $comment['name'], '');
    test_equals('comment normalize (empty) message', $comment['message'], '');
}

// 公開側の入力フォームの初期値の取得テスト
{
    // 結果（ログインしていなければ空）
    $comment = service_comment_default();

    test_equals('comment default (guest) name', $comment['name'], model('default_comments')['name']);

    // 結果（ログインしていれば、ユーザーのお名前とURLが入ること）
    $users = model('select_users', [
        'select' => 'id, name, url',
        'where'  => 'enabled = 1',
        'limit'  => 1,
    ]);

    $_SESSION['auth']['user']['id'] = $users[0]['id'];

    $comment = service_comment_default();

    unset($_SESSION['auth']);

    test_equals('comment default (login) name', $comment['name'], $users[0]['name']);
    test_equals('comment default (login) url', $comment['url'], $users[0]['url']);
}

// ログイン状態を元に戻す
if ($auth !== null) {
    $_SESSION['auth'] = $auth;
}

// 承認済みコメントの取得テスト
{
    // データ（エントリー 1 に承認済み2件・未承認1件、エントリー 2 に承認済み1件）
    foreach ([[1, 1, 'コメント1'], [1, 0, '未承認'], [2, 1, '別のエントリー'], [1, 1, 'コメント2']] as $values) {
        model('insert_comments', [
            'values' => [
                'entry_id' => $values[0],
                'approved' => $values[1],
                'name'     => 'テスト太郎',
                'message'  => $values[2],
            ],
        ]);
    }

    // 取得
    $comments = service_comment_select_approved(1);

    // 結果（そのエントリーの承認済みのコメントだけが、登録順に取得できること）
    test_equals('comment select approved', array_column($comments, 'message'), ['コメント1', 'コメント2']);
}

// コメントの対象の取得テスト
{
    // データ（プラグインの型は、データの定義を持つ有効なプラグインとして用意する）
    $plugin_backup = isset($GLOBALS['plugin']) ? $GLOBALS['plugin'] : null;
    $GLOBALS['plugin'] = [
        'gallerytest' => [
            'data' => [
                'gallerytest' => [
                    'type' => 'gallerytest',
                    'name' => 'テストギャラリー',
                ],
            ],
        ],
    ];

    // 結果（本体の型）
    test_equals('target entry', service_comment_target(['entry_id' => 1, 'contact_id' => null, 'type_code' => 'entry', 'entry_code' => 'hello']), [
        'name' => 'エントリー',
        'url'  => '/entry/detail/hello',
    ]);
    test_equals('target page', service_comment_target(['entry_id' => 2, 'contact_id' => null, 'type_code' => 'page', 'entry_code' => 'about/company']), [
        'name' => 'ページ',
        'url'  => '/page/about/company',
    ]);

    // 結果（プラグインの型は、データの定義の名前）
    test_equals('target plugin type', service_comment_target(['entry_id' => 3, 'contact_id' => null, 'type_code' => 'gallerytest', 'entry_code' => 'work1']), [
        'name' => 'テストギャラリー',
        'url'  => '/gallerytest/detail/work1',
    ]);

    // 結果（データの定義に無い型は、型のコードだけでリンクしない）
    test_equals('target unknown type', service_comment_target(['entry_id' => 4, 'contact_id' => null, 'type_code' => 'disabledtest', 'entry_code' => 'x']), [
        'name' => 'disabledtest',
        'url'  => null,
    ]);

    // 結果（お問い合わせのやりとり・対象なし）
    test_equals('target contact', service_comment_target(['entry_id' => null, 'contact_id' => 5, 'type_code' => null, 'entry_code' => null]), [
        'name' => 'お問い合わせ',
        'url'  => '/admin/contact_view?id=5',
    ]);
    test_equals('target none', service_comment_target(['entry_id' => null, 'contact_id' => null, 'type_code' => null, 'entry_code' => null]), null);

    // プラグインを元に戻す
    if ($plugin_backup === null) {
        unset($GLOBALS['plugin']);
    } else {
        $GLOBALS['plugin'] = $plugin_backup;
    }
}

// トランザクションを終了
db_rollback();

// 既存データ削除
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'comments;');
db_query('TRUNCATE TABLE ' . DATABASE_PREFIX . 'logs;');

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/comment.php',
    ]);
}
