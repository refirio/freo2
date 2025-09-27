CREATE TABLE IF NOT EXISTS users(
    id            INT UNSIGNED        NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created       DATETIME            NOT NULL                COMMENT '作成日時',
    modified      DATETIME            NOT NULL                COMMENT '更新日時',
    deleted       DATETIME                                    COMMENT '削除日時',
    username      VARCHAR(80)         NOT NULL UNIQUE         COMMENT 'ユーザ名',
    password      VARCHAR(80)         NOT NULL                COMMENT 'パスワード',
    password_salt VARCHAR(80)         NOT NULL                COMMENT 'パスワードのソルト',
    authority_id  INT UNSIGNED        NOT NULL                COMMENT '外部キー 権限',
    enabled       TINYINT(1) UNSIGNED NOT NULL                COMMENT '有効',
    name          VARCHAR(255)                                COMMENT '名前',
    email         VARCHAR(255)        NOT NULL UNIQUE         COMMENT 'メールアドレス',
    loggedin      DATETIME                                    COMMENT '最終ログイン日時',
    failed        INT UNSIGNED                                COMMENT 'ログイン失敗回数',
    failed_last   DATETIME                                    COMMENT '最終ログイン失敗日時',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'ユーザ';

CREATE TABLE IF NOT EXISTS authorities(
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created  DATETIME     NOT NULL                COMMENT '作成日時',
    modified DATETIME     NOT NULL                COMMENT '更新日時',
    deleted  DATETIME                             COMMENT '削除日時',
    name     VARCHAR(255) NOT NULL                COMMENT '名前',
    power    INT UNSIGNED NOT NULL                COMMENT '権力',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '権限';

CREATE TABLE IF NOT EXISTS sessions(
    id       VARCHAR(255)        NOT NULL COMMENT 'セッションID',
    created  DATETIME            NOT NULL COMMENT '作成日時',
    modified DATETIME            NOT NULL COMMENT '更新日時',
    user_id  INT UNSIGNED        NOT NULL COMMENT '外部キー ユーザ',
    agent    VARCHAR(255)        NOT NULL COMMENT 'ユーザエージェント',
    keep     TINYINT(1) UNSIGNED NOT NULL COMMENT 'ログイン状態の保持',
    expire   DATETIME            NOT NULL COMMENT 'セッションの有効期限',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'セッション';

CREATE TABLE IF NOT EXISTS types(
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created  DATETIME     NOT NULL                COMMENT '作成日時',
    modified DATETIME     NOT NULL                COMMENT '更新日時',
    deleted  DATETIME                             COMMENT '削除日時',
    code     VARCHAR(255) NOT NULL                COMMENT 'コード',
    name     VARCHAR(255) NOT NULL                COMMENT '名前',
    sort     INT UNSIGNED NOT NULL                COMMENT '並び順',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '型';

CREATE TABLE IF NOT EXISTS entries(
    id           INT UNSIGNED        NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created      DATETIME            NOT NULL                COMMENT '作成日時',
    modified     DATETIME            NOT NULL                COMMENT '更新日時',
    deleted      DATETIME                                    COMMENT '削除日時',
    type_id      INT UNSIGNED        NOT NULL                COMMENT '外部キー 型',
    approved     TINYINT(1) UNSIGNED NOT NULL                COMMENT '承認',
    public       VARCHAR(20)         NOT NULL                COMMENT '公開',
    public_begin DATETIME                                    COMMENT '公開開始日時',
    public_end   DATETIME                                    COMMENT '公開終了日時',
    datetime     DATETIME            NOT NULL                COMMENT '日時',
    code         VARCHAR(255)        NOT NULL                COMMENT 'コード',
    title        VARCHAR(255)        NOT NULL                COMMENT 'タイトル',
    text         TEXT                                        COMMENT '本文',
    picture      VARCHAR(80)                                 COMMENT '画像',
    thumbnail    VARCHAR(80)                                 COMMENT 'サムネイル',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'エントリー';

CREATE TABLE IF NOT EXISTS categories(
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created  DATETIME     NOT NULL                COMMENT '作成日時',
    modified DATETIME     NOT NULL                COMMENT '更新日時',
    deleted  DATETIME                             COMMENT '削除日時',
    type_id INT UNSIGNED  NOT NULL                COMMENT '外部キー 型',
    code     VARCHAR(255) NOT NULL                COMMENT 'コード',
    name     VARCHAR(255) NOT NULL                COMMENT '名前',
    sort     INT UNSIGNED NOT NULL                COMMENT '並び順',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'カテゴリ';

CREATE TABLE IF NOT EXISTS category_sets(
    category_id INT UNSIGNED NOT NULL COMMENT '外部キー カテゴリ',
    entry_id    INT UNSIGNED NOT NULL COMMENT '外部キー 記事'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'カテゴリ ひも付け';

CREATE TABLE IF NOT EXISTS fields(
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created    DATETIME     NOT NULL                COMMENT '作成日時',
    modified   DATETIME     NOT NULL                COMMENT '更新日時',
    deleted    DATETIME                             COMMENT '削除日時',
    type_id    INT UNSIGNED NOT NULL                COMMENT '外部キー 型',
    name       VARCHAR(255) NOT NULL                COMMENT '名前',
    kind       VARCHAR(80)  NOT NULL                COMMENT '種類',
    validation VARCHAR(80)                          COMMENT 'バリデーション',
    text       TEXT                                 COMMENT 'テキスト',
    sort       INT UNSIGNED NOT NULL                COMMENT '並び順',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'フィールド';

CREATE TABLE IF NOT EXISTS field_sets(
    field_id INT UNSIGNED NOT NULL COMMENT '外部キー フィールド',
    entry_id INT UNSIGNED          COMMENT '外部キー 記事',
    text     TEXT                  COMMENT 'テキスト'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'フィールド ひも付け';

CREATE TABLE IF NOT EXISTS attributes(
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created  DATETIME     NOT NULL                COMMENT '作成日時',
    modified DATETIME     NOT NULL                COMMENT '更新日時',
    deleted  DATETIME                             COMMENT '削除日時',
    name     VARCHAR(255) NOT NULL                COMMENT '名前',
    sort     INT UNSIGNED NOT NULL                COMMENT '並び順',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '属性';

CREATE TABLE IF NOT EXISTS attribute_sets(
    attribute_id INT UNSIGNED NOT NULL COMMENT '外部キー 属性',
    user_id      INT UNSIGNED          COMMENT '外部キー ユーザ',
    entry_id     INT UNSIGNED          COMMENT '外部キー 記事'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '属性 ひも付け';

CREATE TABLE IF NOT EXISTS menus(
    id       INT UNSIGNED        NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created  DATETIME            NOT NULL                COMMENT '作成日時',
    modified DATETIME            NOT NULL                COMMENT '更新日時',
    deleted  DATETIME                                    COMMENT '削除日時',
    enabled  TINYINT(1) UNSIGNED NOT NULL                COMMENT '有効',
    title    VARCHAR(255)        NOT NULL                COMMENT 'タイトル',
    url      VARCHAR(255)        NOT NULL                COMMENT 'URL',
    sort     INT UNSIGNED        NOT NULL                COMMENT '並び順',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'メニュー';

CREATE TABLE IF NOT EXISTS widgets(
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created  DATETIME     NOT NULL                COMMENT '作成日時',
    modified DATETIME     NOT NULL                COMMENT '更新日時',
    deleted  DATETIME                             COMMENT '削除日時',
    code     VARCHAR(255) NOT NULL UNIQUE         COMMENT 'コード',
    title    VARCHAR(255) NOT NULL                COMMENT 'タイトル',
    text     TEXT                                 COMMENT 'テキスト',
    sort     INT UNSIGNED NOT NULL                COMMENT '並び順',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'ウィジェット';

CREATE TABLE IF NOT EXISTS contacts(
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created  DATETIME     NOT NULL                COMMENT '作成日時',
    modified DATETIME     NOT NULL                COMMENT '更新日時',
    deleted  DATETIME                             COMMENT '削除日時',
    name     VARCHAR(255) NOT NULL                COMMENT '名前',
    email    VARCHAR(255) NOT NULL                COMMENT 'メールアドレス',
    message  TEXT         NOT NULL                COMMENT 'お問い合わせ内容',
    memo     TEXT                                 COMMENT 'メモ',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'お問い合わせ';

CREATE TABLE IF NOT EXISTS settings(
    id       VARCHAR(255) NOT NULL COMMENT '設定ID',
    value    TEXT                  COMMENT '値',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '設定';

CREATE TABLE IF NOT EXISTS logs(
    id       INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created  DATETIME     NOT NULL                COMMENT '作成日時',
    modified DATETIME     NOT NULL                COMMENT '更新日時',
    deleted  DATETIME                             COMMENT '削除日時',
    user_id  INT UNSIGNED                         COMMENT '外部キー ユーザ',
    ip       VARCHAR(80)  NOT NULL                COMMENT 'IPアドレス',
    agent    VARCHAR(255)                         COMMENT 'ユーザエージェント',
    page     VARCHAR(255) NOT NULL                COMMENT 'ページ',
    message  VARCHAR(255)                         COMMENT 'メッセージ',
    model    VARCHAR(80)                          COMMENT '対象モデル',
    exec     VARCHAR(80)                          COMMENT '操作内容',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT '操作ログ';
