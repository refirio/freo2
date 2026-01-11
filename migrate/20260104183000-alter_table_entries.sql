ALTER TABLE `entries` CHANGE picture pictures TEXT COMMENT '画像';

UPDATE `settings` SET id = 'entry_use_pictures' WHERE id = 'entry_use_picture';
UPDATE `settings` SET id = 'page_use_pictures' WHERE id = 'page_use_picture';
