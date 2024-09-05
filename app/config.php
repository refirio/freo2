<?php

/*******************************************************************************

 設定ファイル

*******************************************************************************/

/* 公開URL */
$GLOBALS['config']['http_url'] = defined('APP_HTTP_URL') ? constant('APP_HTTP_URL') : '';

/* 設置ディレクトリ */
$GLOBALS['config']['http_path'] = defined('APP_HTTP_PATH') ? constant('APP_HTTP_PATH') : dirname($_SERVER['SCRIPT_NAME']) . '/';

/* ハッシュ作成用ソルト */
$GLOBALS['config']['hash_salt'] = defined('APP_HASH_SALT') ? constant('APP_HASH_SALT') : 'RKH7X92N4P';

/* 表示件数 */
$GLOBALS['config']['limits'] = defined('APP_LIMITS') ? constant('APP_LIMITS') : [
    'entry'   => 10,
    'contact' => 10,
    'user'    => 10,
    'log'     => 20,
];

/* ページャーの幅 */
$GLOBALS['config']['pagers'] = defined('APP_PAGERS') ? constant('APP_PAGERS') : [
    'entry'   => 5,
    'contact' => 5,
    'user'    => 5,
    'log'     => 5,
];

/* オプション項目 */
$GLOBALS['config']['options'] = defined('APP_OPTIONS') ? constant('APP_OPTIONS') : [
    'entry' => [
        // 公開
        'publics' => [
            0 => '非公開',
            1 => '公開',
        ],
    ],
    'field' => [
        // 種類
        'kinds' => [
            'text'     => '一行入力',
            'number'   => '数字入力',
            'alphabet' => '英数字入力',
            'textarea' => '複数行入力',
            'wysiwyg'  => 'WYSIWYGエディタ',
            'select'   => 'セレクトボックス',
            'radio'    => 'ラジオボタン',
            'checkbox' => 'チェックボックス',
            'image'    => '画像アップロード',
            'file'     => 'ファイルアップロード',
        ],
        // バリデーション
        'validations' => [
            'none'     => 'なし',
            'required' => '必須',
        ],
    ],
];

/* ストレージ */
$GLOBALS['config']['storage_type'] = defined('APP_STORAGE_TYPE') ? constant('APP_STORAGE_TYPE') : 'file';
$GLOBALS['config']['storage_url'] = defined('APP_STORAGE_URL') ? constant('APP_STORAGE_URL') : null;

/* ファイルアップロード先 */
$GLOBALS['config']['file_targets'] = defined('APP_FILE_TARGETS') ? constant('APP_FILE_TARGETS') : [
    'entry' => 'files/entries/',
    'field' => 'files/fields/',
];

/* ファイルアップロード許可 */
$GLOBALS['config']['file_permissions'] = defined('APP_FILE_PERMISSIONS') ? constant('APP_FILE_PERMISSIONS') : [
    'file'  => [
    ],
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
$GLOBALS['config']['file_alternatives'] = defined('APP_FILE_ALTERNATIVES') ? constant('APP_FILE_ALTERNATIVES') : [
    'file'  => 'img/admin/file.png',
    'image' => null,
];

/* ダミー画像ファイル */
$GLOBALS['config']['file_dummies'] = defined('APP_FILE_DUMMIES') ? constant('APP_FILE_DUMMIES') : [
    'file'  => 'img/admin/no_file.png',
    'image' => 'img/admin/no_file.png',
];

/* 画像リサイズ時のサイズ */
$GLOBALS['config']['resize_width'] = defined('APP_RESIZE_WIDTH') ? constant('APP_RESIZE_WIDTH') : 100;
$GLOBALS['config']['resize_height'] = defined('APP_RESIZE_HEIGHT') ? constant('APP_RESIZE_HEIGHT') : 80;

/* 画像リサイズ時のJpeg画質 */
$GLOBALS['config']['resize_quality'] = defined('APP_RESIZE_QUALITY') ? constant('APP_RESIZE_QUALITY') : 85;

/* ログインの有効期限 */
$GLOBALS['config']['login_expire'] = defined('APP_LOGIN_EXPIRE') ? constant('APP_LOGIN_EXPIRE') : 60 * 60;

/* Cookieの有効期限 */
$GLOBALS['config']['cookie_expire'] = defined('APP_COOKIE_EXPIRE') ? constant('APP_COOKIE_EXPIRE') : 60 * 60 * 24 * 30;

/* Cookieを有効にするパス */
$GLOBALS['config']['cookie_path'] = defined('APP_COOKIE_PATH') ? constant('APP_COOKIE_PATH') : null;

/* Cookieが有効なドメイン */
$GLOBALS['config']['cookie_domain'] = defined('APP_COOKIE_DOMAIN') ? constant('APP_COOKIE_DOMAIN') : null;

/* CookieをHTTPS接続の場合のみ送信 */
$GLOBALS['config']['cookie_secure'] = defined('APP_COOKIE_SECURE') ? constant('APP_COOKIE_SECURE') : false;

/* メールの送信 */
$GLOBALS['config']['mail_send'] = defined('APP_MAIL_SEND') ? constant('APP_MAIL_SEND') : true;

/* メールの記録 */
$GLOBALS['config']['mail_log'] = defined('APP_MAIL_LOG') ? constant('APP_MAIL_LOG') : false;

/* プロキシの考慮 */
$GLOBALS['config']['proxy'] = defined('APP_PROXY') ? constant('APP_PROXY') : false;

/* AWS */
$GLOBALS['config']['aws_credential_key'] = defined('APP_AWS_CREDENTIAL_KEY') ? constant('APP_AWS_CREDENTIAL_KEY') : null;
$GLOBALS['config']['aws_credential_secret'] = defined('APP_AWS_CREDENTIAL_SECRET') ? constant('APP_AWS_CREDENTIAL_SECRET') : null;
$GLOBALS['config']['aws_region'] = defined('APP_AWS_REGION') ? constant('APP_AWS_REGION') : null;
$GLOBALS['config']['aws_version'] = defined('APP_AWS_VERSION') ? constant('APP_AWS_VERSION') : null;
$GLOBALS['config']['aws_bucket'] = defined('APP_AWS_BUCKET') ? constant('APP_AWS_BUCKET') : null;

/* reCAPTCHA */
$GLOBALS['config']['recaptcha_enable'] = defined('APP_RECAPTCHA_ENABLE') ? constant('APP_RECAPTCHA_ENABLE') : false;
$GLOBALS['config']['recaptcha_site_key'] = defined('APP_RECAPTCHA_SITE_KEY') ? constant('APP_RECAPTCHA_SITE_KEY') : null;
$GLOBALS['config']['recaptcha_secret_key'] = defined('APP_RECAPTCHA_SECRET_KEY') ? constant('APP_RECAPTCHA_SECRET_KEY') : null;
