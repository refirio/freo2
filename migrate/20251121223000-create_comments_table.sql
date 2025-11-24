CREATE TABLE IF NOT EXISTS comments(
    id         INT UNSIGNED        NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created    DATETIME            NOT NULL                COMMENT '作成日時',
    modified   DATETIME            NOT NULL                COMMENT '更新日時',
    deleted    DATETIME                                    COMMENT '削除日時',
    user_id    INT UNSIGNED                                COMMENT '外部キー ユーザー',
    entry_id   INT UNSIGNED                                COMMENT '外部キー エントリー',
    contact_id INT UNSIGNED                                COMMENT '外部キー お問い合わせ',
    approved   TINYINT(1) UNSIGNED NOT NULL                COMMENT '承認',
    name       VARCHAR(255)                                COMMENT '名前',
    url        VARCHAR(255)                                COMMENT 'URL',
    message    TEXT                NOT NULL                COMMENT 'コメント内容',
    memo       TEXT                                        COMMENT 'メモ',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'コメント';

ALTER TABLE `entries` ADD comment VARCHAR(20) NOT NULL COMMENT 'コメントの受付' AFTER thumbnail;
UPDATE `entries` SET comment = 'closed';
