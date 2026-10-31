<?php

import('app/services/export.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正な操作が検出されました。送信内容を確認して再度実行してください。');
    }

    // アクセス元
    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/^' . preg_quote($GLOBALS['config']['http_url'], '/') . '/', $_SERVER['HTTP_REFERER'])) {
        error('不正なアクセスです。');
    }

    if (isset($_POST['exec']) && $_POST['exec'] === 'start') {
        // 出力する前の確認
        $check = service_export_check();
        if (!empty($check['errors'])) {
            warning($check['errors']);
        }

        // 出力するURLの一覧を作成
        $export = service_export_start();

        $_SESSION['export'] = [
            'token'    => $export['token'],
            'failures' => [],
        ];

        ok(null, [
            'token' => token('create'),
            'total' => count($export['urls']),
        ]);
    }

    if (empty($_SESSION['export']['token'])) {
        error('出力を開始していません。');
    }
    $token = $_SESSION['export']['token'];
    $urls  = service_export_load($token);
    if (empty($urls)) {
        error('出力するURLの一覧が見つかりません。最初からやり直してください。');
    }

    if (isset($_POST['exec']) && $_POST['exec'] === 'step') {
        // ページを取得して保存（数ページずつ）
        $offset   = isset($_POST['offset']) ? max(0, intval($_POST['offset'])) : 0;
        $replaces = service_export_replaces();

        foreach (array_slice($urls, $offset, $GLOBALS['config']['export_step']) as $url) {
            $response = service_export_fetch($token, $url['url']);

            // 404 のページだけは 404 で返るので、それも保存する
            if ($response === null || ($response['status'] !== 200 && !($url['file'] === '404.html' && $response['status'] === 404))) {
                $_SESSION['export']['failures'][] = $url['url'] . '（' . ($response === null ? '取得できません' : 'ステータス ' . $response['status']) . '）';
            } elseif (!service_export_save($token, $url['file'], service_export_convert($response['body'], $replaces))) {
                $_SESSION['export']['failures'][] = $url['url'] . '（保存できません）';
            }
        }

        ok(null, [
            'token' => token('create'),
            'done'  => min(count($urls), $offset + $GLOBALS['config']['export_step']),
            'total' => count($urls),
        ]);
    }

    if (isset($_POST['exec']) && $_POST['exec'] === 'finish') {
        // 静的なファイルを複製して、ZIP にまとめる
        if (!service_export_static($token)) {
            error('静的なファイルを複製できません。');
        }
        if (!service_export_zip($token)) {
            error('ZIP ファイルを作成できません。');
        }

        ok(null, [
            'token'    => token('create'),
            'failures' => $_SESSION['export']['failures'],
        ]);
    }

    error('不正なアクセスです。');
}

// 出力する前の確認
$check = service_export_check();

$_view['errors']  = $check['errors'];
$_view['notices'] = $check['notices'];

// タイトル
$_view['title'] = 'HTML出力';
