INSERT INTO widgets VALUES
(NULL, NOW(), NOW(), NULL, 'auth_initial', '認証ページ 読み込み開始', NULL, NULL, 6),
(NULL, NOW(), NOW(), NULL, 'auth_ready', '認証ページ 読み込み完了', NULL, NULL, 7),
(NULL, NOW(), NOW(), NULL, 'auth_menu', '認証ページ メニュー', NULL, NULL, 8),
(NULL, NOW(), NOW(), NULL, 'auth_home', '認証ページ ホームページ', NULL, NULL, 9),
(NULL, NOW(), NOW(), NULL, 'auth_page', '認証ページ 下層ページ', NULL, NULL, 10),
(NULL, NOW(), NOW(), NULL, 'admin_initial', '管理ページ 読み込み開始', NULL, NULL, 11),
(NULL, NOW(), NOW(), NULL, 'admin_ready', '管理ページ 読み込み完了', NULL, NULL, 12),
(NULL, NOW(), NOW(), NULL, 'admin_menu', '管理ページ メニュー', NULL, NULL, 13),
(NULL, NOW(), NOW(), NULL, 'admin_home', '管理ページ ホームページ', NULL, NULL, 14),
(NULL, NOW(), NOW(), NULL, 'admin_page', '管理ページ 下層ページ', NULL, NULL, 15);

UPDATE widgets SET code = 'public_initial', title = '公開ページ 読み込み開始' WHERE code = 'initial';
UPDATE widgets SET code = 'public_ready', title = '公開ページ 読み込み完了' WHERE code = 'ready';
UPDATE widgets SET code = 'public_menu', title = '公開ページ メニュー' WHERE code = 'menu';
UPDATE widgets SET code = 'public_home', title = '公開ページ ホームページ' WHERE code = 'home';
UPDATE widgets SET code = 'public_page', title = '公開ページ 下層ページ' WHERE code = 'page';
