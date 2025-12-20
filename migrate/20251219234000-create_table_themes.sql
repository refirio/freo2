CREATE TABLE IF NOT EXISTS themes(
    id        INT UNSIGNED        NOT NULL AUTO_INCREMENT COMMENT '代理キー',
    created   DATETIME            NOT NULL                COMMENT '作成日時',
    modified  DATETIME            NOT NULL                COMMENT '更新日時',
    deleted   DATETIME                                    COMMENT '削除日時',
    code      VARCHAR(255)        NOT NULL UNIQUE         COMMENT 'コード',
    version   VARCHAR(20)         NOT NULL                COMMENT 'バージョン',
    enabled   TINYINT(1) UNSIGNED NOT NULL                COMMENT '有効',
    setting   TEXT                                        COMMENT '設定',
    PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'テーマ';
