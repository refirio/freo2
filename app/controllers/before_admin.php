<?php

// ログイン確認
if (!preg_match('/^(index|logout)$/', $_REQUEST['_work'])) {
    if (empty($_SESSION['auth']['user']['id']) || localdate() - $_SESSION['auth']['user']['time'] > $GLOBALS['config']['login_expire']) {
        $referer = '/' . implode('/', $_params);

        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '') {
            $referer .= '?' . $_SERVER['QUERY_STRING'];
        }

        // リダイレクト
        redirect('/admin/logout?referer=' . rawurlencode($referer));
    } else {
        $_SESSION['auth']['user']['time'] = localdate();
    }
}
