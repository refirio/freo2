<?php e($GLOBALS['setting']['mail_body_begin']) ?>

ホームページから以下のお問い合わせがありました。

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

【お名前】
<?php e($_SESSION['post']['contact']['name']) ?><?php e("\n") ?>

【メールアドレス】
<?php e($_SESSION['post']['contact']['email']) ?><?php e("\n") ?>

【お問い合わせ件名】
<?php e($_SESSION['post']['contact']['subject']) ?><?php e("\n") ?>

【お問い合わせ内容】
<?php e($_SESSION['post']['contact']['message']) ?><?php e("\n") ?>

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

<?php e($GLOBALS['setting']['mail_body_end']) ?>
