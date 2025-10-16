INSERT INTO users VALUES(NULL, NOW(), NOW(), NULL, 'admin', '7463f5f0110ccf10c3327984e9a64e37', 'fec4c34788722a9e7445a950818adac4', 1, 1, '管理者', 'admin@example.com', 1, NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO authorities VALUES(NULL, NOW(), NOW(), NULL, '管理者', 3);
INSERT INTO authorities VALUES(NULL, NOW(), NOW(), NULL, '投稿者', 2);
INSERT INTO authorities VALUES(NULL, NOW(), NOW(), NULL, '閲覧者', 1);
INSERT INTO authorities VALUES(NULL, NOW(), NOW(), NULL, 'ゲスト', 0);

INSERT INTO types VALUES(NULL, NOW(), NOW(), NULL, 'entry', '記事', 1);
INSERT INTO types VALUES(NULL, NOW(), NOW(), NULL, 'page', 'ページ', 2);

INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, 'initial', '読み込み開始', NULL, 1);
INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, 'ready', '読み込み完了', NULL, 2);
INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, 'menu', 'メニュー', NULL, 3);
INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, 'home', 'ホームページ', NULL, 4);
INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, 'page', '下層ページ', NULL, 5);

INSERT INTO settings VALUES('title', 'Example');
INSERT INTO settings VALUES('description', 'サイトの概要。');
INSERT INTO settings VALUES('entry_use_approve', 0);
INSERT INTO settings VALUES('entry_use_text', 1);
INSERT INTO settings VALUES('entry_use_picture', 1);
INSERT INTO settings VALUES('entry_use_thumbnail', 1);
INSERT INTO settings VALUES('entry_default_code', 'YmdHis');
INSERT INTO settings VALUES('page_use_approve', 0);
INSERT INTO settings VALUES('page_use_text', 1);
INSERT INTO settings VALUES('page_use_picture', 1);
INSERT INTO settings VALUES('page_use_thumbnail', 1);
INSERT INTO settings VALUES('page_default_code', NULL);
INSERT INTO settings VALUES('page_home_code', NULL);
INSERT INTO settings VALUES('page_url_omission', NULL);
INSERT INTO settings VALUES('restricted_password_title', '要認証: ');
INSERT INTO settings VALUES('restricted_password_text', '<p>パスワード認証により公開されます。</p>');
INSERT INTO settings VALUES('mail_from', 'auto@example.com');
INSERT INTO settings VALUES('mail_to', 'admin@example.com');
INSERT INTO settings VALUES('mail_body_begin', '※このメールは送信専用アドレスから配信しています。
　返信いただいても返答はできません。
');
INSERT INTO settings VALUES('mail_body_end', '━━━━━━━━━━━━━━━━━━━━━━━━━
Example
E-mail : admin@example.com
━━━━━━━━━━━━━━━━━━━━━━━━━');
INSERT INTO settings VALUES('mail_verify_subject', 'メールアドレス存在確認');
INSERT INTO settings VALUES('mail_password_subject', 'パスワード再設定');
INSERT INTO settings VALUES('mail_contact_subject', 'お問い合わせありがとうございます');
INSERT INTO settings VALUES('mail_contact_subject_admin', 'お問い合わせがありました');
