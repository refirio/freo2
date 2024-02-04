<?php

/*******************************************************************************

 levis: Configuration File

*******************************************************************************/

/********* Main ***************************************************************/

define('MAIN_FILE', $_SERVER['SCRIPT_NAME']);
define('MAIN_LIBRARY_PATH', '../levis/');
define('MAIN_APPLICATION_PATH', '../levis/');
define('MAIN_DEFAULT_MODE', 'home');
define('MAIN_DEFAULT_WORK', 'index');
define('MAIN_INTERNAL_ENCODING', 'UTF-8');
define('MAIN_CHARSET', 'utf-8');
define('MAIN_TIME', 0);

/********* Database ***********************************************************/

define('DATABASE_TYPE', 'pdo_mysql');
define('DATABASE_HOST', '');
define('DATABASE_PORT', '');
define('DATABASE_USERNAME', '');
define('DATABASE_PASSWORD', '');
define('DATABASE_NAME', '');
define('DATABASE_PREFIX', '');
define('DATABASE_CHARSET', 'UTF8');
define('DATABASE_CHARSET_INPUT_FROM', 'UTF-8');
define('DATABASE_CHARSET_INPUT_TO', 'UTF-8');
define('DATABASE_CHARSET_OUTPUT_FROM', 'UTF-8');
define('DATABASE_CHARSET_OUTPUT_TO', 'UTF-8');
define('DATABASE_MIGRATE_PATH', '../levis/migrate/');
define('DATABASE_SCAFFOLD_PATH', '../levis/scaffold/');
define('DATABASE_BACKUP_PATH', '../levis/backup/');
define('DATABASE_AUTOCONNECT', true);

/********* Session ************************************************************/

define('SESSION_LIFETIME', 0);
define('SESSION_PATH', dirname($_SERVER['SCRIPT_NAME']));
define('SESSION_CACHE', 'none');
define('SESSION_AUTOSTART', true);

/********* Token **************************************************************/

define('TOKEN_SPAN', 60 * 60);

/********* Regexp *************************************************************/

define('REGEXP_TYPE', 'preg');

/********* Autoload ***********************************************************/

define('AUTOLOAD_MODEL', true);
define('AUTOLOAD_SERVICE', false);

/********* Page ***************************************************************/

define('PAGE_PATH', '../levis/page/');
define('PAGE_CONTROLLER', '../levis/page');

/********* Permission *********************************************************/

define('PERMISSION_DIRECTORY', 0707);
define('PERMISSION_FILE', 0606);

/********* Test ***************************************************************/

define('TEST_PATH', '../levis/test/');

/********* Debug **************************************************************/

define('DEBUG_LEVEL', 1);
define('DEBUG_PASSWORD', '');
define('DEBUG_ADDR', '');

/********* Logging ************************************************************/

define('LOGGING_PATH', 'log/');
define('LOGGING_MESSAGE', false);
define('LOGGING_GET', false);
define('LOGGING_POST', false);
define('LOGGING_FILES', false);
