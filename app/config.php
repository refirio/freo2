<?php

/*******************************************************************************

 設定ファイル

*******************************************************************************/

/* 公開URL */
$GLOBALS['config']['http_url'] = app_config('APP_HTTP_URL', '');

/* 設置ディレクトリ */
$GLOBALS['config']['http_path'] = app_config('APP_HTTP_PATH', dirname($_SERVER['SCRIPT_NAME']) . '/');

/* ハッシュ作成用ペッパー（DBには保存しない秘密値。公開しない） */
$GLOBALS['config']['hash_pepper'] = app_config('APP_HASH_PEPPER', 'RKH7X92N4P');

/* パスワードのハッシュ方式（bcrypt / argon2 / md5）。argon2 は PHP が対応している環境のみ。md5 は古いPHP環境向けの互換用 */
$GLOBALS['config']['hash_algorithm'] = app_config('APP_HASH_ALGORITHM', 'bcrypt');

/* オプション項目 */
$GLOBALS['config']['option'] = app_config('APP_OPTION', [
    'user' => [
        // 有効
        'enabled' => [
            1 => '有効',
            0 => '無効',
        ],
        // 2段階認証
        'twostep' => [
            1 => '利用する',
            0 => '利用しない',
        ],
    ],
    'entry' => [
        // 承認
        'approved' => [
            1 => '承認済',
            0 => '未承認',
        ],
        // 公開
        'public' => [
            'all'       => '公開',
            'user'      => '登録ユーザーに公開',
            'attribute' => '指定の属性に公開',
            'password'  => 'パスワード認証で公開',
            'none'      => '非公開',
        ],
        // 本文形式
        'text_type' => [
            'none'     => 'なし',
            'textarea' => '複数行入力',
            'html'     => 'HTML直接入力',
            'wysiwyg'  => 'WYSIWYGエディタ',
        ],
        // コメントの受付
        'comment' => [
            'opened' => '受け付ける',
            'user'   => '登録ユーザーから受け付ける',
            'closed' => '受け付けない',
        ],
    ],
    'field' => [
        // 種類
        'kind' => [
            'text'     => '一行入力',
            'number'   => '数字入力',
            'alphabet' => '英数字入力',
            'textarea' => '複数行入力',
            'html'     => 'HTML直接入力',
            'wysiwyg'  => 'WYSIWYGエディタ',
            'select'   => 'セレクトボックス',
            'radio'    => 'ラジオボタン',
            'checkbox' => 'チェックボックス',
            'image'    => '画像アップロード',
            'file'     => 'ファイルアップロード',
        ],
        // バリデーション
        'validation' => [
            'none'     => 'なし',
            'required' => '必須',
        ],
    ],
    'attribute' => [
        // フィルター対象
        'filterable' => [
            1 => '対象',
            0 => '対象外',
        ],
    ],
    'menu' => [
        // 有効
        'enabled' => [
            1 => '有効',
            0 => '無効',
        ],
    ],
    'contact' => [
        // 状況
        'status' => [
            'opened'      => '未対応',
            'processing'  => '対応中',
            'closed'      => '完了',
            'unnecessary' => '対応不要',
        ],
    ],
    'comment' => [
        // 承認
        'approved' => [
            1 => '承認済',
            0 => '未承認',
        ],
    ],
]);

/* お問い合わせ管理で、状況を指定していないときに表示しない状況（対応の済んだもの） */
$GLOBALS['config']['contact_status_hidden'] = app_config('APP_CONTACT_STATUS_HIDDEN', ['closed', 'unnecessary']);

/* 操作ログの保存日数（これより古いログは、ログを記録するときに削除する。0 なら削除しない） */
$GLOBALS['config']['log_retention'] = app_config('APP_LOG_RETENTION', 180);

/* 暗証コード・2段階認証用コードを続けて間違えられる回数（達したらそのコードを無効にする） */
$GLOBALS['config']['code_failed_limit'] = app_config('APP_CODE_FAILED_LIMIT', 10);

/* ストレージ（テスト中は設定に関係なくローカルのファイルを使う。テスト用データベースのIDで、実際のストレージのファイルを消さないため） */
$GLOBALS['config']['storage_type'] = app_test() ? 'file' : app_config('APP_STORAGE_TYPE', 'file');
$GLOBALS['config']['storage_url'] = app_test() ? $GLOBALS['config']['http_url'] : app_config('APP_STORAGE_URL', $GLOBALS['config']['http_url']);

/* エントリーの画像・フィールドのファイルをプログラムを通して表示するか（有効にすると、エントリーの公開範囲で表示できるかを判定する。直接のURLは、Webサーバーやバケットの設定で別に拒否する） */
$GLOBALS['config']['storage_protect'] = app_config('APP_STORAGE_PROTECT', false);

/* プログラムを通して表示するとき、S3 の署名付きURLを使える時間（秒） */
$GLOBALS['config']['storage_signed_expire'] = app_config('APP_STORAGE_SIGNED_EXPIRE', 60 * 5);

/* ファイルアップロード先（テスト中は設定に関係なく tests の中を使う。テスト用データベースのIDで、実際のファイルを消さないため。scenario はシナリオテストのスクリーンショットの保存先） */
$GLOBALS['config']['file_target'] = app_test() ? [
    'entry'     => 'files/tests/entries/',
    'field'     => 'files/tests/fields/',
    'media'     => 'files/tests/medias/',
    'thumbnail' => 'files/tests/thumbnails/',
    'temp'      => 'files/temps/',
    'scenario'  => 'files/scenarios/',
] : app_config('APP_FILE_TARGET', [
    'entry'     => 'files/entries/',
    'field'     => 'files/fields/',
    'media'     => 'files/medias/',
    'thumbnail' => 'files/thumbnails/',
    'temp'      => 'files/temps/',
    'scenario'  => null,
]);

/* ファイルアップロード許可 */
$GLOBALS['config']['file_permission'] = app_config('APP_FILE_PERMISSION', [
    'file'  => [
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
        'txt' => [
            'name'   => 'TXT',
            'ext'    => 'txt',
            'regexp' => '/\.txt$/i',
            'mime'   => 'text/plain',
        ],
        'csv' => [
            'name'   => 'CSV',
            'ext'    => 'csv',
            'regexp' => '/\.csv$/i',
            'mime'   => 'text/csv',
        ],
        'pdf' => [
            'name'   => 'PDF',
            'ext'    => 'pdf',
            'regexp' => '/\.pdf$/i',
            'mime'   => 'application/pdf',
        ],
        'docx' => [
            'name'   => 'DOCX',
            'ext'    => 'docx',
            'regexp' => '/\.docx$/i',
            'mime'   => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'xlsx' => [
            'name'   => 'XLSX',
            'ext'    => 'xlsx',
            'regexp' => '/\.xlsx$/i',
            'mime'   => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'pptx' => [
            'name'   => 'PPTX',
            'ext'    => 'pptx',
            'regexp' => '/\.pptx$/i',
            'mime'   => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ],
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
$GLOBALS['config']['file_alternative'] = app_config('APP_FILE_ALTERNATIVE', [
    'file'  => 'img/admin/file.png',
    'image' => null,
]);

/* ダミー画像ファイル */
$GLOBALS['config']['file_dummy'] = app_config('APP_FILE_DUMMY', [
    'file'  => 'img/admin/no_file.png',
    'image' => 'img/admin/no_file.png',
]);

/* 一時ファイルを残す時間（秒。アップロードしたまま登録されなかった一時ファイルは、これより古くなったら削除する） */
$GLOBALS['config']['temp_retention'] = app_config('APP_TEMP_RETENTION', 60 * 60 * 8);

/* HTML出力でページを取得するURL（サーバーの中から公開URLを開けない環境で変える） */
$GLOBALS['config']['export_fetch_url'] = app_config('APP_EXPORT_FETCH_URL', $GLOBALS['config']['http_url']);

/* HTML出力で1回に取得するページ数 */
$GLOBALS['config']['export_step'] = app_config('APP_EXPORT_STEP', 5);

/* HTML出力で複製する静的なファイル（公開ディレクトリからの相対パス。アップロードしたファイルは、ストレージがファイルのときに別に複製する） */
$GLOBALS['config']['export_static'] = app_config('APP_EXPORT_STATIC', ['css/', 'js/', 'img/', 'themes/', 'plugins/']);

/* 画像リサイズ時のサイズ（メディアのサムネイルに使用。縦横比を保ってこの範囲に収める） */
$GLOBALS['config']['resize_width'] = app_config('APP_RESIZE_WIDTH', 400);
$GLOBALS['config']['resize_height'] = app_config('APP_RESIZE_HEIGHT', 400);

/* 画像リサイズ時のJpeg画質 */
$GLOBALS['config']['resize_quality'] = app_config('APP_RESIZE_QUALITY', 85);

/* アップロードできるメディア拡張子 */
$GLOBALS['config']['media_ext'] = app_config('APP_MEDIA_EXT', ['png', 'jpeg', 'jpg', 'jpe', 'gif', 'txt', 'csv', 'pdf', 'docx', 'xlsx', 'pptx']);

/* 投稿者がアップロードできるメディア領域 */
$GLOBALS['config']['media_author_dir'] = app_config('APP_MEDIA_AUTHOR_DIR', 'images');

/* 投稿者がアップロードできるメディア拡張子 */
$GLOBALS['config']['media_author_ext'] = app_config('APP_MEDIA_AUTHOR_EXT', ['png', 'jpeg', 'jpg', 'jpe', 'gif']);

/* ログインの有効期限 */
$GLOBALS['config']['login_expire'] = app_config('APP_LOGIN_EXPIRE', 60 * 60);

/* Cookieの有効期限 */
$GLOBALS['config']['cookie_expire'] = app_config('APP_COOKIE_EXPIRE', 60 * 60 * 24 * 30);

/* Cookieを有効にするパス */
$GLOBALS['config']['cookie_path'] = app_config('APP_COOKIE_PATH', null);

/* Cookieが有効なドメイン */
$GLOBALS['config']['cookie_domain'] = app_config('APP_COOKIE_DOMAIN', null);

/* CookieをHTTPS接続の場合のみ送信（既定では、HTTPSで開いているときに有効。プロキシの背後でも判定する） */
$GLOBALS['config']['cookie_secure'] = app_config('APP_COOKIE_SECURE', ssl(true));

/* メールの送信（null: 送信しない、sendmail: mail() で送信、smtp: mail_smtp の設定でSMTPサーバーから送信）。テスト中は設定に関係なく送信しない */
$GLOBALS['config']['mail_send'] = app_test() ? null : app_config('APP_MAIL_SEND', 'sendmail');

/* メールの記録（テスト中は設定に関係なく記録する。シナリオテストが記録からメールの内容を確かめるため） */
$GLOBALS['config']['mail_log'] = app_test() ? true : app_config('APP_MAIL_LOG', false);

/* メールのエンベロープFrom（Return-Path になるメールアドレス。sendmail なら -f で渡し、smtp なら MAIL FROM に使う。null なら指定しない） */
$GLOBALS['config']['mail_envelope'] = app_config('APP_MAIL_ENVELOPE', null);

/* SMTPサーバー（mail_send が smtp のときに使う。encryption は tls（STARTTLS）・ssl・none） */
$GLOBALS['config']['mail_smtp'] = app_config('APP_MAIL_SMTP', [
    'host'       => '',
    'port'       => 587,
    'encryption' => 'tls',
    'username'   => '',
    'password'   => '',
    'timeout'    => 10,
]);

/* プロキシの考慮 */
$GLOBALS['config']['proxy'] = app_config('APP_PROXY', 0);

/* セキュリティヘッダー（すべての画面で送るHTTPヘッダー。空の配列にすると送らない） */
$GLOBALS['config']['security_headers'] = app_config('APP_SECURITY_HEADERS', [
    'X-Content-Type-Options'  => 'nosniff',
    'X-Frame-Options'         => 'SAMEORIGIN',
    'Content-Security-Policy' => 'frame-ancestors \'self\'',
    'Referrer-Policy'         => 'strict-origin-when-cross-origin',
]);

/* HSTSの有効期間（秒。0なら送らない。HTTPSで開いているときだけ送る） */
$GLOBALS['config']['hsts_max_age'] = app_config('APP_HSTS_MAX_AGE', 0);

/* AWS */
$GLOBALS['config']['aws_credential_key'] = app_config('APP_AWS_CREDENTIAL_KEY', null);
$GLOBALS['config']['aws_credential_secret'] = app_config('APP_AWS_CREDENTIAL_SECRET', null);
$GLOBALS['config']['aws_region'] = app_config('APP_AWS_REGION', null);
$GLOBALS['config']['aws_version'] = app_config('APP_AWS_VERSION', null);
$GLOBALS['config']['aws_bucket'] = app_config('APP_AWS_BUCKET', null);

/* reCAPTCHA（テスト中は設定に関係なく使わない。シナリオテストのブラウザでは確認を通れないため） */
$GLOBALS['config']['recaptcha_enable'] = app_test() ? false : app_config('APP_RECAPTCHA_ENABLE', false);
$GLOBALS['config']['recaptcha_site_key'] = app_config('APP_RECAPTCHA_SITE_KEY', null);
$GLOBALS['config']['recaptcha_secret_key'] = app_config('APP_RECAPTCHA_SECRET_KEY', null);

/* テーマ格納ディレクトリ */
$GLOBALS['config']['theme_path'] = app_config('APP_THEME_PATH', MAIN_APPLICATION_PATH . 'themes/');

/* プラグイン格納ディレクトリ */
$GLOBALS['config']['plugin_path'] = app_config('APP_PLUGIN_PATH', MAIN_APPLICATION_PATH . 'plugins/');
