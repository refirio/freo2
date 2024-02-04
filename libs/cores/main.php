<?php

/*******************************************************************************

 Main File

*******************************************************************************/

benchmark();

import('libs/cores/version.php');
import('libs/cores/rand.php');
import('libs/cores/regexp.php');
import('libs/cores/info.php');
import('libs/cores/db.php');
import('libs/cores/test.php');

bootstrap();

if (SESSION_AUTOSTART === true) {
    session();
}
if (DATABASE_AUTOCONNECT === true) {
    database();
}

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

if (php_sapi_name() === 'cli') {
    if (isset($_SERVER['argv'][1])) {
        $_REQUEST['_mode'] = $_SERVER['argv'][1];
    } else {
        $_REQUEST['_mode'] = 'info_levis';
    }
}

switch ($_REQUEST['_mode']) {
    case 'info_php':
        info_php();
        break;
    case 'info_levis':
        info_levis();
        break;
    case 'db_admin':
        db_admin();
        break;
    case 'db_migrate':
        db_migrate();
        break;
    case 'db_scaffold':
        db_scaffold();
        break;
    case 'test_index':
        test_index();
        break;
    case 'test_exec':
        test_exec();
        break;
}

if (php_sapi_name() === 'cli') {
    exit;
}

service();

model();

controller();

view();

benchmark('Complete');

debugbar();

exit;
