ALTER TABLE users ADD attribute_begin DATETIME COMMENT '属性適用開始日時' AFTER authority_id;
ALTER TABLE users ADD attribute_end DATETIME COMMENT '属性適用終了日時' AFTER attribute_begin;
