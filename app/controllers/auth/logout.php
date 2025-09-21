<?php

import('app/services/user.php');

// ログアウト
if (isset($_SESSION['auth']['user']['id'])) {
    service_user_logout($_COOKIE['auth']['session'], $_SESSION['auth']['user']['id']);
}

unset($_SESSION['auth']['user']);

// リファラ
if (isset($_GET['referer'])) {
    $referer = '?referer=' . rawurlencode($_GET['referer']);
} else {
    $referer = '';
}

// リダイレクト
redirect('/auth/' . $referer);
