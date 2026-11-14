<?php

// 設定ファイルを読み込み
import('app/config.php');

// コードカバレッジの記録を開始
if (!isset($_GET['_test'])) {
    service('coverage.php');
    service_coverage_start();
}

// ライブラリを読み込み
// 判定はデータベースを使わないので、トランザクションもテーブルの初期化も行わない
import('libs/modules/environment.php');

// パソコン（Windows + Chrome）テスト
{
    // 確認
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    // 結果（OSと ブラウザを「 + 」でつないで返すこと）
    test_equals('environment windows chrome', $environment, 'Windows 10 (or later) + Chrome 120');
    test_equals('environment windows chrome (browser)', $browser, 'Chrome 120');
    test_equals('environment windows chrome (os)', $os, 'Windows 10 (or later)');
}

// パソコン（Windows + Edge）テスト
{
    // 確認（Chromeの文字列も含むが、Edgeとして判定されること）
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0');

    // 結果
    test_equals('environment windows edge', $environment, 'Windows 10 (or later) + Edge 120');
}

// パソコン（Windows + Firefox）テスト
{
    // 確認
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0');

    // 結果
    test_equals('environment windows firefox', $environment, 'Windows 10 (or later) + Firefox 121');
}

// パソコン（macOS + Safari）テスト
{
    // 確認
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15');

    // 結果
    test_equals('environment macos safari', $environment, 'macOS 10.15 Catalina + Safari 17');
}

// スマートフォン（iPhone）テスト
{
    // 確認
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1');

    // 結果（端末の種類まで含めて判定すること）
    test_equals('environment iphone', $environment, 'iOS 17(iPhone) + Safari 17');
    test_equals('environment iphone (os)', $os, 'iOS 17(iPhone)');
}

// スマートフォン（Android）テスト
{
    // 確認
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36');

    // 結果（Mobileを含むのでタブレット扱いにならないこと）
    test_equals('environment android', $environment, 'Android 14 + Chrome 120');
    test_equals('environment android (os)', $os, 'Android 14');
}

// タブレット（Android）テスト
{
    // 確認（Mobileを含まない）
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (Linux; Android 14; SM-X200) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

    // 結果
    test_equals('environment android tablet (os)', $os, 'Android 14 (Tablet)');
}

// ロボットテスト
{
    // 確認
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

    // 結果（ロボット・ツールの類はOSを空にして、ブラウザ名だけを返すこと）
    test_equals('environment googlebot', $environment, 'Google (Robot)');
    test_equals('environment googlebot (browser)', $browser, 'Google (Robot)');
    test_equals('environment googlebot (os)', $os, '');
}

// ツールテスト
{
    // 確認
    list($environment_curl, $browser, $os) = environment_useragent('curl/8.5.0');
    list($environment_elb, $browser, $os)  = environment_useragent('ELB-HealthChecker/2.0');

    // 結果
    test_equals('environment curl', $environment_curl, 'curl (Tool)');
    test_equals('environment elb', $environment_elb, 'Load Balancer (Tool)');
}

// プレビューテスト
{
    // 確認（SNSがリンクの内容を取りに来た場合）
    list($environment, $browser, $os) = environment_useragent('Slackbot-LinkExpanding 1.0 (+https://api.slack.com/robots)');

    // 結果
    test_equals('environment slackbot', $environment, 'Slack (Preview)');
}

// ゲーム機テスト
{
    // 確認
    list($environment, $browser, $os) = environment_useragent('Mozilla/5.0 (PlayStation; PlayStation 5/2.26) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0 Safari/605.1.15');

    // 結果
    test_equals('environment playstation', $environment, 'PlayStation 5 (Game)');
}

// OSだけ判定できる場合テスト
{
    // 確認（ブラウザの名前を含まない）
    list($environment, $browser, $os) = environment_useragent('Windows NT 10.0');

    // 結果
    test_equals('environment os only', $environment, 'Windows 10 (or later)');
    test_equals('environment os only (browser)', $browser, null);
}

// 判定できない場合テスト
{
    // 確認
    list($environment, $browser, $os) = environment_useragent('');

    // 結果
    test_equals('environment unknown', $environment, 'Unknown');
    test_equals('environment unknown (browser)', $browser, null);
    test_equals('environment unknown (os)', $os, null);
}

// コードカバレッジの記録を終了
if (!isset($_GET['_test'])) {
    $coverages = service_coverage_end();

    service_coverage_output($coverages, [
        'libs/modules/environment.php',
    ]);
}
