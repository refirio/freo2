INSERT INTO users VALUES(NULL, NOW(), NOW(), NULL, 'admin', '7463f5f0110ccf10c3327984e9a64e37', 'fec4c34788722a9e7445a950818adac4', 1, '管理者', 'admin@example.com', NULL, NULL, NULL);

INSERT INTO authorities VALUES(NULL, NOW(), NOW(), NULL, '管理者', 3);
INSERT INTO authorities VALUES(NULL, NOW(), NOW(), NULL, '投稿者', 2);
INSERT INTO authorities VALUES(NULL, NOW(), NOW(), NULL, '閲覧者', 1);

INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, '読み込み開始', 'initial', NULL, 1);
INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, '読み込み完了', 'ready', NULL, 2);
INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, 'メニュー', 'menu', NULL, 3);
INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, 'ホームページ', 'home', NULL, 4);
INSERT INTO widgets VALUES(NULL, NOW(), NOW(), NULL, '下層ページ', 'page', NULL, 5);

INSERT INTO settings VALUES('title', 'Example');
INSERT INTO settings VALUES('description', 'サイトの概要。');
INSERT INTO settings VALUES('page_home_code', NULL);
INSERT INTO settings VALUES('page_url_omission', '');
INSERT INTO settings VALUES('mail_to', 'admin@example.com');
INSERT INTO settings VALUES('mail_subject_admin', 'Webサイトからお問い合わせがありました');
INSERT INTO settings VALUES('mail_subject_user', 'お問い合わせありがとうございます');
INSERT INTO settings VALUES('mail_from', 'auto@example.com');
