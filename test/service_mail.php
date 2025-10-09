<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
service('mail.php');

// トランザクションを開始
db_transaction();

// メールの送信テスト
{
    // メールを送信
    $result = service_mail_send('example@example.com', '件名', '本文。');

    // テスト
    test_not_equals('send mail', $result, false);
}

// トランザクションを終了
db_rollback();

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/mail.php',
    ]);
}
