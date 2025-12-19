ALTER TABLE fields ADD choices TEXT COMMENT '選択肢' AFTER validation;
ALTER TABLE fields ADD initial TEXT COMMENT '初期値' AFTER choices;
ALTER TABLE fields ADD explanation VARCHAR(255) COMMENT '説明' AFTER initial;

UPDATE fields SET choices = text;

ALTER TABLE fields DROP text;
