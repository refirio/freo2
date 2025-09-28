<?php

// ログイン確認
if (empty($_SESSION['auth']['user']['id'])) {
    $referer = '/' . implode('/', $_params);

    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
        $referer .= '?' . $_SERVER['QUERY_STRING'];
    }

    // リダイレクト
    redirect('/auth/logout?referer=' . rawurlencode($referer));
} else {
    $_SESSION['auth']['user']['time'] = localdate();
}
