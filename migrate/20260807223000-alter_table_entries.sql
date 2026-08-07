ALTER TABLE `entries` ADD text_type VARCHAR(20) NOT NULL COMMENT '本文形式' AFTER text;

UPDATE `entries` SET text_type = 'wysiwyg';
UPDATE `entries` JOIN `settings` ON `settings`.id = 'entry_text_type' SET `entries`.text_type = `settings`.value WHERE `entries`.type_id = 1;
UPDATE `entries` JOIN `settings` ON `settings`.id = 'page_text_type' SET `entries`.text_type = `settings`.value WHERE `entries`.type_id = 2;
