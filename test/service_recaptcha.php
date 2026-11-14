<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
// データベースを使わないので、トランザクションもテーブルの初期化も行わない
service('recaptcha.php');

// 設定とセッションを退避（Googleに接続しない範囲だけを確認する）
$recaptcha_enable  = $GLOBALS['config']['recaptcha_enable'];
$session_recaptcha = isset($_SESSION['recaptcha']) ? $_SESSION['recaptcha'] : null;
$post_recaptcha    = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : null;

unset($_SESSION['recaptcha']);
unset($_POST['g-recaptcha-response']);

// reCAPTCHAが無効な場合テスト
{
    // データ
    $GLOBALS['config']['recaptcha_enable'] = false;

    // 結果（確認せずに通り、セッションにも記録しないこと）
    test_equals('recaptcha (disabled)', service_recaptcha_verify(), true);
    test_equals('recaptcha (disabled session)', isset($_SESSION['recaptcha']), false);
}

// reCAPTCHAが有効で、トークンが送信されなかった場合テスト
{
    // データ
    $GLOBALS['config']['recaptcha_enable'] = true;

    // 結果（通らないこと。トークンが無ければGoogleには接続しない）
    test_equals('recaptcha (no token)', service_recaptcha_verify(), false);
    test_equals('recaptcha (no token session)', isset($_SESSION['recaptcha']), false);
}

// reCAPTCHAが有効で、同じセッションで確認済みの場合テスト
{
    // データ
    $GLOBALS['config']['recaptcha_enable'] = true;
    $_SESSION['recaptcha'] = true;

    // 結果（確認せずに通ること）
    test_equals('recaptcha (verified)', service_recaptcha_verify(), true);
}

// 設定とセッションを元に戻す
$GLOBALS['config']['recaptcha_enable'] = $recaptcha_enable;

if ($session_recaptcha === null) {
    unset($_SESSION['recaptcha']);
} else {
    $_SESSION['recaptcha'] = $session_recaptcha;
}
if ($post_recaptcha !== null) {
    $_POST['g-recaptcha-response'] = $post_recaptcha;
}

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'app/services/recaptcha.php',
    ]);
}
