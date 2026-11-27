<?php

import('app/services/user.php');

// ログアウト
if (isset($_SESSION['auth']['user']['id'])) {
    service_user_logout($_COOKIE['auth']['session'], $_SESSION['auth']['user']['id']);

    // 入力中の内容の一時保存は、次に表示する画面で削除する（ログインしていない状態で管理画面を開いたときもここを通るが、その場合はユーザーが無いので残る）
    $_SESSION['draft_clear'] = app_draft_key();
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
