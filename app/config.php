<?php

/*******************************************************************************

 設定ファイル

*******************************************************************************/

/* 公開URL */
$GLOBALS['config']['http_url'] = app_config('APP_HTTP_URL', '');

/* 設置ディレクトリ */
$GLOBALS['config']['http_path'] = app_config('APP_HTTP_PATH', dirname($_SERVER['SCRIPT_NAME']) . '/');

/* ハッシュ作成用ソルト */
$GLOBALS['config']['hash_salt'] = app_config('APP_HASH_SALT', 'RKH7X92N4P');

/* 表示件数 */
$GLOBALS['config']['limits'] = app_config('APP_LIMITS', [
    'entry'   => 10,
    'contact' => 10,
    'user'    => 10,
    'log'     => 20,
]);

/* ページャーの幅 */
$GLOBALS['config']['pagers'] = app_config('APP_PAGERS', [
    'entry'   => 5,
    'contact' => 5,
    'user'    => 5,
    'log'     => 5,
]);

/* オプション項目 */
$GLOBALS['config']['options'] = app_config('APP_OPTIONS', [
    'user' => [
        // 有効
        'enabled' => [
            1 => '有効',
            0 => '無効',
        ],
    ],
    'entry' => [
        // 承認
        'approved' => [
            1 => '承認済',
            0 => '未承認',
        ],
        // 公開
        'publics' => [
            'all'       => '全体に公開',
            'user'      => '登録ユーザに公開',
            'attribute' => '指定の属性に公開',
            'none'      => '非公開',
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
    'menu' => [
        // 有効
        'enabled' => [
            1 => '有効',
            0 => '無効',
        ],
    ],
]);

/* ストレージ */
$GLOBALS['config']['storage_type'] = app_config('APP_STORAGE_TYPE', 'file');
$GLOBALS['config']['storage_url'] = app_config('APP_STORAGE_URL', null);

/* ファイルアップロード先 */
$GLOBALS['config']['file_targets'] = app_config('APP_FILE_TARGETS', [
    'entry' => 'files/entries/',
    'field' => 'files/fields/',
]);

/* ファイルアップロード許可 */
$GLOBALS['config']['file_permissions'] = app_config('APP_FILE_PERMISSIONS', [
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
]);

/* 代替ファイル */
$GLOBALS['config']['file_alternatives'] = app_config('APP_FILE_ALTERNATIVES', [
    'file'  => 'img/admin/file.png',
    'image' => null,
]);

/* ダミー画像ファイル */
$GLOBALS['config']['file_dummies'] = app_config('APP_FILE_DUMMIES', [
    'file'  => 'img/admin/no_file.png',
    'image' => 'img/admin/no_file.png',
]);

/* 画像リサイズ時のサイズ */
$GLOBALS['config']['resize_width'] = app_config('APP_RESIZE_WIDTH', 100);
$GLOBALS['config']['resize_height'] = app_config('APP_RESIZE_HEIGHT', 80);

/* 画像リサイズ時のJpeg画質 */
$GLOBALS['config']['resize_quality'] = app_config('APP_RESIZE_QUALITY', 85);

/* ログインの有効期限 */
$GLOBALS['config']['login_expire'] = app_config('APP_LOGIN_EXPIRE', 60 * 60);

/* Cookieの有効期限 */
$GLOBALS['config']['cookie_expire'] = app_config('APP_COOKIE_EXPIRE', 60 * 60 * 24 * 30);

/* Cookieを有効にするパス */
$GLOBALS['config']['cookie_path'] = app_config('APP_COOKIE_PATH', null);

/* Cookieが有効なドメイン */
$GLOBALS['config']['cookie_domain'] = app_config('APP_COOKIE_DOMAIN', null);

/* CookieをHTTPS接続の場合のみ送信 */
$GLOBALS['config']['cookie_secure'] = app_config('APP_COOKIE_SECURE', false);

/* メールの送信 */
$GLOBALS['config']['mail_send'] = app_config('APP_MAIL_SEND', true);

/* メールの記録 */
$GLOBALS['config']['mail_log'] = app_config('APP_MAIL_LOG', false);

/* プロキシの考慮 */
$GLOBALS['config']['proxy'] = app_config('APP_PROXY', false);

/* AWS */
$GLOBALS['config']['aws_credential_key'] = app_config('APP_AWS_CREDENTIAL_KEY', null);
$GLOBALS['config']['aws_credential_secret'] = app_config('APP_AWS_CREDENTIAL_SECRET', null);
$GLOBALS['config']['aws_region'] = app_config('APP_AWS_REGION', null);
$GLOBALS['config']['aws_version'] = app_config('APP_AWS_VERSION', null);
$GLOBALS['config']['aws_bucket'] = app_config('APP_AWS_BUCKET', null);

/* reCAPTCHA */
$GLOBALS['config']['recaptcha_enable'] = app_config('APP_RECAPTCHA_ENABLE', false);
$GLOBALS['config']['recaptcha_site_key'] = app_config('APP_RECAPTCHA_SITE_KEY', null);
$GLOBALS['config']['recaptcha_secret_key'] = app_config('APP_RECAPTCHA_SECRET_KEY', null);
