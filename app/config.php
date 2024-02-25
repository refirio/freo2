<?php

/*******************************************************************************

 設定ファイル

*******************************************************************************/

/* 公開URL */
$GLOBALS['config']['http_url'] = '';

/* 設置ディレクトリ */
$GLOBALS['config']['http_path'] = dirname($_SERVER['SCRIPT_NAME']) . '/';

/* ハッシュ作成用ソルト */
$GLOBALS['config']['hash_salt'] = 'RKH7X92N4P';

/* 表示件数 */
$GLOBALS['config']['limits'] = [
    'entry'   => 10,
    'contact' => 10,
    'user'    => 10,
    'log'     => 20,
];

/* ページャーの幅 */
$GLOBALS['config']['pagers'] = [
    'entry'   => 5,
    'contact' => 5,
    'user'    => 5,
    'log'     => 5,
];

/* オプション項目 */
$GLOBALS['config']['options'] = [
    'entry' => [
        // 公開
        'publics' => [
            0 => '非公開',
            1 => '公開',
        ],
    ],
    'page' => [
        // 公開
        'publics' => [
            0 => '非公開',
            1 => '公開',
        ],
    ],
    'field' => [
        // 種類
        'types' => [
            'text'     => '一行入力',
            'number'   => '数字入力',
            'alphabet' => '英数字入力',
            'textarea' => '複数行入力',
            'select'   => 'セレクトボックス',
            'radio'    => 'ラジオボタン',
            'checkbox' => 'チェックボックス',
            'file'     => 'アップロード',
        ],
        // 対象
        'targets' => [
            'entry' => '記事',
            'page'  => 'ページ',
        ],
    ],
];

/* ストレージ */
$GLOBALS['config']['storage_type'] = 'file';
$GLOBALS['config']['storage_url'] = null;

/* ファイルアップロード先 */
$GLOBALS['config']['file_targets'] = [
    'entry' => 'files/entries/',
    'page'  => 'files/pages/',
];

/* ファイルアップロード許可 */
$GLOBALS['config']['file_permissions'] = [
    'image' => [
        'png' => [
            'name'   => 'PNG',
            'ext'    => 'png',
            'regexp' => '/\.png$/i',
            'mime'   => 'image/png',
        ],
        'jpeg' => [
            'name'   => 'JPEG',
            'ext'    => 'jpg',
            'regexp' => '/\.(jpeg|jpg|jpe)$/i',
            'mime'   => 'image/jpeg',
        ],
        'gif' => [
            'name'   => 'GIF',
            'ext'    => 'gif',
            'regexp' => '/\.gif$/i',
            'mime'   => 'image/gif',
        ],
    ],
];

/* 代替ファイル */
$GLOBALS['config']['file_alternatives'] = [
    'image' => null,
];

/* ダミー画像ファイル */
$GLOBALS['config']['file_dummies'] = [
    'image' => 'img/admin/no_file.png',
];

/* 画像リサイズ時のサイズ */
$GLOBALS['config']['resize_width']  = 100;
$GLOBALS['config']['resize_height'] = 80;

/* 画像リサイズ時のJpeg画質 */
$GLOBALS['config']['resize_quality'] = 85;

/* ログインの有効期限 */
$GLOBALS['config']['login_expire'] = 60 * 60;

/* Cookieの有効期限 */
$GLOBALS['config']['cookie_expire'] = 60 * 60 * 24 * 30;

/* Cookieを有効にするパス */
$GLOBALS['config']['cookie_path'] = null;

/* Cookieが有効なドメイン */
$GLOBALS['config']['cookie_domain'] = null;

/* CookieをHTTPS接続の場合のみ送信 */
$GLOBALS['config']['cookie_secure'] = false;

///* メールの送信先 */
//$GLOBALS['config']['mail_to'] = 'example@example.com';
//
///* メールの件名（管理者用） */
//$GLOBALS['config']['mail_subject_admin'] = 'ホームページからお問い合わせがありました';
//
///* メールの件名（自動返信） */
//$GLOBALS['config']['mail_subject_user'] = 'お問い合わせありがとうございます';
//
///* メールのヘッダ */
//$GLOBALS['config']['mail_headers'] = array(
//    'X-Mailer' => 'php',
//    'From'     => 'auto@example.com',
//);

/* メールの送信 */
$GLOBALS['config']['mail_send'] = true;

/* メールの記録 */
$GLOBALS['config']['mail_log'] = false;

/* プロキシの考慮 */
$GLOBALS['config']['proxy'] = false;

/* AWS */
$GLOBALS['config']['aws_credential_key'] = null;
$GLOBALS['config']['aws_credential_secret'] = null;
$GLOBALS['config']['aws_region'] = null;
$GLOBALS['config']['aws_version'] = null;
$GLOBALS['config']['aws_bucket'] = null;

/* reCAPTCHA */
$GLOBALS['config']['recaptcha_enable'] = false;
$GLOBALS['config']['recaptcha_site_key'] = null;
$GLOBALS['config']['recaptcha_secret_key'] = null;
