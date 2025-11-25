<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ワンタイムトークン
    if (!token('check')) {
        error('不正なアクセスです。');
    }

    // reCAPTCHA
    if ($GLOBALS['config']['recaptcha_enable'] == true && empty($_SESSION['recaptcha'])) {
        $recaptcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : null;
        if (!$recaptcha){
            error('reCAPTCHAでの認証を行ってください。');
        }
        $response = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $GLOBALS['config']['recaptcha_secret_key'] . '&response=' . $recaptcha), true);
        if (intval($response['success']) === 1 && $response['score'] >= 0.5) {
            $_SESSION['recaptcha'] = true;
        } else {
            error('reCAPTCHAでの認証に失敗しました。');
        }
    }

    // 入力データを整理
    $post = array(
        'contact' => model('normalize_contacts', [
            'name'    => isset($_POST['name'])    ? $_POST['name']    : '',
            'email'   => isset($_POST['email'])   ? $_POST['email']   : '',
            'subject' => isset($_POST['subject']) ? $_POST['subject'] : '',
            'message' => isset($_POST['message']) ? $_POST['message'] : '',
        ]),
    );

    // 入力データを検証＆登録
    $warnings = model('validate_contacts', $post['contact']);
    if (isset($_POST['_type']) && $_POST['_type'] === 'json') {
        if (empty($warnings)) {
            ok();
        } else {
            warning($warnings);
        }
    } else {
        if (empty($warnings)) {
            $_SESSION['post']['contact'] = $post['contact'];

            // リダイレクト
            redirect('/contact/preview');
        } else {
            $_view['contact'] = $post['contact'];

            $_view['warnings'] = $warnings;
        }
    }
} elseif (isset($_GET['referer']) && $_GET['referer'] === 'preview') {
    // 入力データを復元
    $_view['contact'] = $_SESSION['post']['contact'];
} else {
    // 初期データを取得
    $_view['contact'] = model('default_contacts');

    if (!empty($_SESSION['auth']['user']['id'])) {
        // ユーザーを取得
        $users = model('select_users', [
            'where' => [
                'id = :id AND enabled = 1',
                [
                    'id' => $_SESSION['auth']['user']['id'],
                ],
            ],
        ]);

        $_view['contact']['name']  = $users[0]['name'];
        $_view['contact']['email'] = $users[0]['email'];
    }
}

// お問い合わせの表示用データ作成
$_view['contact'] = model('view_contacts', $_view['contact']);

// タイトル
$_view['title'] = 'お問い合わせ';
