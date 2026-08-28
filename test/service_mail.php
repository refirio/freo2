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

// 実際にメールを送信しないよう、テスト中は「送信なし・記録あり」に固定する
$GLOBALS['config']['mail_send'] = false;
$GLOBALS['config']['mail_log']  = true;

// 記録先ディレクトリ
$directory = MAIN_APPLICATION_PATH . 'mail/' . localdate('Ymd') . '/';

// 後始末のため、テスト開始時にディレクトリがあったかを控えておく
$directory_exists = is_dir($directory);

// 宛先（記録先のファイル名が「時刻_宛先.txt」になるため、テストごとに宛先を変えて衝突を防ぐ）
$data_recipients = [
    'send'     => 'test1@example.com',
    'wordwrap' => 'test2@example.com',
    'boundary' => 'test3@example.com',
    'disabled' => 'test4@example.com',
];

// 前回の実行で残ったファイルを削除
foreach ($data_recipients as $to) {
    foreach (test_mail_records($directory, $to) as $file) {
        unlink($file);
    }
}

// メールの送信テスト
{
    // データ
    $to      = $data_recipients['send'];
    $subject = '件名';
    $message = '本文。';

    // 送信
    $result = service_mail_send($to, $subject, $message);

    // 結果
    test_not_equals('send mail', $result, false);

    $records = test_mail_records($directory, $to);

    test_equals('send mail record', count($records), 1);

    // 結果（記録された内容）
    $content = file_get_contents($records[0]);

    test_contains('send mail record to', $content, 'to: ' . $to);
    test_contains('send mail record subject', $content, 'subject: ' . $subject);
    test_contains('send mail record message', $content, $message);
}

// 本文の強制改行テスト
{
    // データ（1行1000バイトを超えると文字化けするため、256文字で改行される）
    $to      = $data_recipients['wordwrap'];
    $subject = '件名';
    $message = str_repeat('あ', 300);

    // 送信
    $result = service_mail_send($to, $subject, $message);

    // 結果
    test_not_equals('send mail (wordwrap)', $result, false);

    $records = test_mail_records($directory, $to);
    $content = file_get_contents($records[0]);
    $lines   = explode("\n", $content);

    // 結果（本文は6行目から始まる。バイト数ではなく文字数で数えること）
    test_equals('send mail wordwrap first line', mb_strlen($lines[5]), 256);
    test_equals('send mail wordwrap second line', mb_strlen($lines[6]), 44);
    test_not_contains('send mail wordwrap over', $content, str_repeat('あ', 257));
}

// 本文の強制改行（境界値）テスト
{
    // データ（上限ちょうどのため分割されない）
    $to      = $data_recipients['boundary'];
    $subject = '件名';
    $message = str_repeat('あ', 256);

    // 送信
    service_mail_send($to, $subject, $message);

    // 結果
    $records = test_mail_records($directory, $to);
    $content = file_get_contents($records[0]);
    $lines   = explode("\n", $content);

    test_equals('send mail wordwrap boundary', mb_strlen($lines[5]), 256);
    test_equals('send mail wordwrap boundary rest', $lines[6], '');
}

// 送信も記録もしない設定のテスト
{
    // 設定
    $GLOBALS['config']['mail_send'] = false;
    $GLOBALS['config']['mail_log']  = false;

    // 送信
    $to     = $data_recipients['disabled'];
    $result = service_mail_send($to, '件名', '本文。');

    // 結果（何も行われないため、初期値の false が返る）
    test_equals('send mail disabled', $result, false);
    test_equals('send mail disabled record', count(test_mail_records($directory, $to)), 0);

    // 設定を戻す
    $GLOBALS['config']['mail_log'] = true;
}

// テストで作成したファイルを削除
foreach ($data_recipients as $to) {
    foreach (test_mail_records($directory, $to) as $file) {
        unlink($file);
    }
}
if ($directory_exists === false && is_dir($directory)) {
    rmdir($directory);
}

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/mail.php',
    ]);
}

/**
 * 記録されたメールのファイルを取得
 *
 * @param string $directory
 * @param string $to
 *
 * @return array
 */
function test_mail_records($directory, $to)
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = glob($directory . '*_' . $to . '.txt');

    return $files === false ? [] : $files;
}
