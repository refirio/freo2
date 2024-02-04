<?php

/*******************************************************************************

 Loader File

*******************************************************************************/

require_once MAIN_PATH . 'config.php';
require_once MAIN_PATH . MAIN_LIBRARY_PATH . 'libs/cores/basis.php';

benchmark();

import('libs/cores/version.php');
import('libs/cores/rand.php');
import('libs/cores/regexp.php');
import('libs/cores/db.php');

bootstrap();

session();

database();

normalize();

routing();

if (LOGGING_GET) {
    logging('get');
}
if (LOGGING_POST && !empty($_POST)) {
    logging('post');
}
if (LOGGING_FILES && !empty($_FILES)) {
    logging('files');
}
