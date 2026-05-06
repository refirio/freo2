<?php /** @var array $_view */ ?>
<?php e($GLOBALS['setting']['mail_body_begin']) ?>

認証コード
<?php e($_view['token_code']) ?><?php e("\n") ?>

<?php e($GLOBALS['setting']['mail_body_end']) ?>
