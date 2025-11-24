<?php

/*******************************************************************************

 Functions for Basis

*******************************************************************************/

if (!defined('MAIN_PATH')) {
    define('MAIN_PATH', '');
}

$_params = array();
$_db     = array();
$_view   = array();

/**
 * Load the given file.
 *
 * @param string $file
 * @param bool   $once
 * @param bool   $ignore
 *
 * @return void
 */
function import($file, $once = true, $ignore = false)
{
    global $_params, $_db, $_view;

    if (!empty($GLOBALS['_target'])) {
        foreach (array(MAIN_PATH . MAIN_APPLICATION_PATH, MAIN_PATH . MAIN_LIBRARY_PATH) as $dir) {
            if (is_file($dir . $GLOBALS['_target'] . '/' . $file)) {
                $file = $GLOBALS['_target'] . '/' . $file;

                break;
            }
        }
    }

    $flag = false;

    foreach (array(MAIN_PATH . MAIN_APPLICATION_PATH, MAIN_PATH . MAIN_LIBRARY_PATH) as $dir) {
        if (MAIN_PATH) {
            $target = $dir . $file;
        } else {
            $target = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $dir . $file;
        }

        if (is_file($target)) {
            if ($once) {
                require_once $target;
            } else {
                require $target;
            }

            $flag = true;

            break;
        }
    }

    if ($ignore === false && $flag === false) {
        if (LOGGING_MESSAGE) {
            logging('message', 'Import error: ' . $target);
        }

        error('Import error' . (DEBUG_LEVEL ? ': ' . $target: ''));
    }

    return;
}

/**
 * Bootstrap.
 *
 * @return void
 */
function bootstrap()
{
    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/bootstrap.php')) {
        import('app/bootstrap.php');
    }

    return;
}

/**
 * Start the session.
 *
 * @return void
 */
function session()
{
    if (php_sapi_name() === 'cli') {
        return;
    }

    if (regexp_match('[\\|\/]$', SESSION_PATH)) {
        $path = SESSION_PATH;
    } else {
        $path = SESSION_PATH . '/';
    }

    session_set_cookie_params(SESSION_LIFETIME, $path);
    session_cache_limiter(SESSION_CACHE);
    session_start();

    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/session.php')) {
        import('app/session.php');
    }

    return;
}

/**
 * Connect to the database.
 *
 * @return void
 */
function database()
{
    db_connect('default');

    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/database.php')) {
        import('app/database.php');
    }

    return;
}

/**
 * Normalize the superglobals.
 *
 * @return void
 */
function normalize()
{
    if (ini_get('magic_quotes_gpc')) {
        $_GET     = unescape($_GET);
        $_POST    = unescape($_POST);
        $_REQUEST = unescape($_REQUEST);
        $_SERVER  = unescape($_SERVER);
        $_COOKIE  = unescape($_COOKIE);
    }

    $_GET     = sanitize($_GET);
    $_POST    = sanitize($_POST);
    $_REQUEST = sanitize($_REQUEST);
    $_SERVER  = sanitize($_SERVER);
    $_COOKIE  = sanitize($_COOKIE);

    $_GET     = unify($_GET);
    $_POST    = unify($_POST);
    $_REQUEST = unify($_REQUEST);
    $_SERVER  = unify($_SERVER);
    $_COOKIE  = unify($_COOKIE);

    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/normalize.php')) {
        import('app/normalize.php');
    }

    return;
}

/**
 * Routing the url.
 *
 * @return void
 */
function routing()
{
    global $_params;

    if (!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
    }

    $request_uri = explode('/', strtok($_SERVER['REQUEST_URI'], '?'));
    $script_name = explode('/', $_SERVER['SCRIPT_NAME']);

    for ($i = 0; $i < sizeof($script_name); $i++) {
        if ($request_uri[$i] === $script_name[$i]) {
            unset($request_uri[$i]);
        }
    }
    $_params = array_values(array_map('urldecode', $request_uri));

    if (count($_params)) {
        if ($regexp = regexp_match('(.+)\.([_a-zA-Z0-9\-]*)$', $_params[count($_params) - 1])) {
            $_params[count($_params) - 1] = $regexp[1];
            $_params[count($_params)]     = $regexp[2];
        }
    }

    $_REQUEST = array(
        '_mode'  => isset($_POST['_mode'])  ? $_POST['_mode']  : (isset($_GET['_mode'])  ? $_GET['_mode']  : null),
        '_work'  => isset($_POST['_work'])  ? $_POST['_work']  : (isset($_GET['_work'])  ? $_GET['_work']  : null),
        '_type'  => isset($_POST['_type'])  ? $_POST['_type']  : (isset($_GET['_type'])  ? $_GET['_type']  : null),
        '_token' => isset($_POST['_token']) ? $_POST['_token'] : (isset($_GET['_token']) ? $_GET['_token'] : null),
        '_test'  => isset($_POST['_test'])  ? $_POST['_test']  : (isset($_GET['_test'])  ? $_GET['_test']  : null),
    );

    if (isset($_params[0]) && empty($_REQUEST['_mode'])) {
        $_REQUEST['_mode'] = $_params[0];
    }
    if (isset($_params[1]) && empty($_REQUEST['_work'])) {
        $_REQUEST['_work'] = $_params[1];
    }

    if ($_REQUEST['_mode'] === '' || !regexp_match('^[_a-zA-Z0-9\-]+$', $_REQUEST['_mode'])) {
        $_REQUEST['_mode'] = MAIN_DEFAULT_MODE;
    }
    if ($_REQUEST['_work'] === '' || !regexp_match('^[_a-zA-Z0-9\-]+$', $_REQUEST['_work'])) {
        $_REQUEST['_work'] = MAIN_DEFAULT_WORK;
    }

    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/routing.php')) {
        import('app/routing.php');
    }

    return;
}

/**
 * Load the service files.
 *
 * @param string|null $target
 *
 * @return void
 */
function service($target = null)
{
    if (!AUTOLOAD_SERVICE && $target === null) {
        return;
    }

    $dir = 'app/services/';

    if (!file_exists(MAIN_PATH . MAIN_APPLICATION_PATH . $dir)) {
        return;
    }

    if ($dh = opendir(MAIN_PATH . MAIN_APPLICATION_PATH . $dir)) {
        while (($entry = readdir($dh)) !== false) {
            if (!is_file(MAIN_PATH . MAIN_APPLICATION_PATH . $dir . $entry)) {
                continue;
            }

            if ($target && $target !== $entry) {
                continue;
            }

            if ($regexp = regexp_match('^([_a-zA-Z0-9\-]+)\.php$', $entry)) {
                $name = $regexp[1];
            } else {
                continue;
            }

            import($dir . $entry);
        }
        closedir($dh);
    } else {
        if (LOGGING_MESSAGE) {
            logging('message', 'Opendir error: ' . $target);
        }

        error('Opendir error' . (DEBUG_LEVEL ? ': ' . $target: ''));
    }

    return;
}

/**
 * Load the model files or execute the model functions.
 *
 * @return mixed
 */
function model()
{
    $args = func_get_args();

    if (!empty($args) && !regexp_match('\.', $args[0])) {
        $target = array_shift($args);

        return $target(...$args);
    } else {
        if (!AUTOLOAD_MODEL && $args[0] === null) {
            return;
        }

        $dir = 'app/models/';
        $php = '';

        if ($dh = opendir(MAIN_PATH . MAIN_APPLICATION_PATH . $dir)) {
            while (($entry = readdir($dh)) !== false) {
                if (!is_file(MAIN_PATH . MAIN_APPLICATION_PATH . $dir . $entry)) {
                    continue;
                }

                if (!empty($args) && $args[0] !== $entry) {
                    continue;
                }

                if ($regexp = regexp_match('^([_a-zA-Z0-9\-]+)\.php$', $entry)) {
                    $name = $regexp[1];
                } else {
                    continue;
                }

                import($dir . $entry);

                if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/model.php')) {
                    import('app/model.php');

                    $model = '';
                    if ($fp = fopen(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/model.php', 'r')) {
                        while ($line = fgets($fp)) {
                            if ($model == '') {
                                $model .= "\n";
                            } else {
                                $model .= $line;
                            }
                        }
                        fclose($fp);
                    }
                    $model = str_replace('APP_MODEL', $name, $model);

                    eval($model);
                }

                if (!function_exists('select_' . $name)) {
                    $php .= 'function select_' . $name . '($queries)';
                    $php .= '{';
                    $php .= '    $queries["from"] = "' . DATABASE_PREFIX . $name . '";';
                    $php .= '    return db_select($queries);';
                    $php .= '}';
                }
                if (!function_exists('insert_' . $name)) {
                    $php .= 'function insert_' . $name . '($queries)';
                    $php .= '{';
                    $php .= '    $queries["insert_into"] = "' . DATABASE_PREFIX . $name . '";';
                    $php .= '    return db_insert($queries);';
                    $php .= '}';
                }
                if (!function_exists('update_' . $name)) {
                    $php .= 'function update_' . $name . '($queries)';
                    $php .= '{';
                    $php .= '    $queries["update"] = "' . DATABASE_PREFIX . $name . '";';
                    $php .= '    return db_update($queries);';
                    $php .= '}';
                }
                if (!function_exists('delete_' . $name)) {
                    $php .= 'function delete_' . $name . '($queries)';
                    $php .= '{';
                    $php .= '    $queries["delete_from"] = "' . DATABASE_PREFIX . $name . '";';
                    $php .= '    return db_delete($queries);';
                    $php .= '}';
                }
                if (!function_exists('normalize_' . $name)) {
                    $php .= 'function normalize_' . $name . '($queries)';
                    $php .= '{';
                    $php .= '    return $queries;';
                    $php .= '}';
                }
                if (!function_exists('validate_' . $name)) {
                    $php .= 'function validate_' . $name . '($queries)';
                    $php .= '{';
                    $php .= '    return array();';
                    $php .= '}';
                }
            }
            closedir($dh);
        } else {
            if (LOGGING_MESSAGE) {
                logging('message', 'Opendir error: ' . $args[0]);
            }

            error('Opendir error' . (DEBUG_LEVEL ? ': ' . $args[0] : ''));
        }

        eval($php);

        return;
    }
}

/**
 * Load the controller files.
 *
 * @param string|null $target
 *
 * @return void
 */
function controller($target = null)
{
    global $_params, $_db, $_view;

    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/before.php')) {
        import('app/controllers/before.php');
    }
    if (isset($_params[0]) && is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/before_' . $_params[0] . '.php')) {
        import('app/controllers/before_' . $_params[0] . '.php');
    }

    if (empty($GLOBALS['_routing'])) {
        $routing = '';
    } else {
        $routing = $GLOBALS['_routing'] . '/';
    }

    $dir  = 'app/controllers/' . $routing . $_REQUEST['_mode'] . '/';
    $file = $_REQUEST['_work'] . '.php';

    if ($target) {
        $GLOBALS['_import'] = 'target';

        import('app/controllers/' . $target);
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . $dir . $file)) {
        $GLOBALS['_import'] = 'normal';

        import($dir . $file);
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . $dir . MAIN_DEFAULT_WORK . '.php')) {
        $GLOBALS['_import'] = 'normal';

        import($dir . MAIN_DEFAULT_WORK . '.php');
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/' . PAGE_CONTROLLER . '.php')) {
        $GLOBALS['_import'] = 'page';

        import('app/controllers/' . PAGE_CONTROLLER . '.php');
    } else {
        $GLOBALS['_import'] = null;
    }

    if (isset($_params[0]) && is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/after_' . $_params[0] . '.php')) {
        import('app/controllers/after_' . $_params[0] . '.php');
    }
    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/after.php')) {
        import('app/controllers/after.php');
    }

    return;
}

/**
 * Load the view files.
 *
 * @param string|null $target
 * @param bool        $return
 *
 * @return void
 */
function view($target = null, $return = false)
{
    global $_params, $_view;

    static $complete = false;

    if ($_REQUEST['_mode'] !== 'test_exec' && $complete) {
        return;
    }

    if (empty($GLOBALS['_routing'])) {
        $routing = '';
    } else {
        $routing = $GLOBALS['_routing'] . '/';
    }

    $dir  = 'app/views/' . $routing . $_REQUEST['_mode'] . '/';
    $file = $_REQUEST['_work'] . '.php';

    if ($return) {
        ob_start();
    }

    if ($target) {
        import('app/views/' . $target, false);
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . $dir . $file) && $GLOBALS['_import'] == 'normal') {
        import($dir . $file, false);
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . $dir . MAIN_DEFAULT_WORK . '.php') && $GLOBALS['_import'] == 'normal') {
        import($dir . MAIN_DEFAULT_WORK . '.php', false);
    } elseif (is_file(PAGE_PATH . implode('/', $_params) . '.php')) {
        import(str_replace(MAIN_APPLICATION_PATH, '', PAGE_PATH) . implode('/', $_params) . '.php', false);
    } elseif ($_params[count($_params) - 1] === '' && is_file(PAGE_PATH . implode('/', array_slice($_params, 0, count($_params) - 1)) . '/index.php')) {
        import(str_replace(MAIN_APPLICATION_PATH, '', PAGE_PATH) . implode('/', array_slice($_params, 0, count($_params) - 1)) . '/index.php', false);
    } elseif ($_REQUEST['_mode'] === MAIN_DEFAULT_MODE && $_REQUEST['_work'] === MAIN_DEFAULT_WORK) {
        about();
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/views/404.php')) {
        if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/404.php')) {
            import('app/controllers/404.php');
        } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/error.php')) {
            import('app/controllers/error.php');
        }

        header('HTTP/1.0 404 Not Found');
        import('app/views/404.php');
    } else {
        header('HTTP/1.0 404 Not Found');
        error('404 Not Found');
    }

    if ($return === false) {
        $complete = true;
    }

    if ($return) {
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    } else {
        return;
    }
}

/**
 * Get the unescaped data.
 *
 * @param string $data
 *
 * @return string
 */
function unescape($data)
{
    if (is_array($data)) {
        return array_map('unescape', $data);
    }

    return stripslashes($data);
}

/**
 * Get the sanitized data.
 *
 * @param string $data
 *
 * @return string
 */
function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }

    return str_replace("\0", '', $data);
}

/**
 * Get the unified data.
 *
 * @param string $data
 *
 * @return string
 */
function unify($data)
{
    if (is_array($data)) {
        return array_map('unify', $data);
    }

    $data = regexp_replace("\r?\n", "\r", $data);
    $data = regexp_replace("\r", "\n", $data);

    return $data;
}

/**
 * Get the converted data.
 *
 * @param mixed  $data
 * @param string $to_encoding
 * @param string $from_encoding
 *
 * @return mixed
 */
function convert($data, $to_encoding = 'UTF-8', $from_encoding = 'UTF-8,EUCJP-WIN,SJIS-WIN')
{
    if (mb_convert_variables($to_encoding, $from_encoding, $data)) {
        return $data;
    } else {
        return array();
    }
}

/**
 * Get the alternative data.
 *
 * @param mixed       $data
 * @param string      $to_encoding
 * @param string|null $from_encoding
 *
 * @return mixed
 */
function alt($data, $alternative, $pattern = null)
{
    if ($data === null || $data === '') {
        return $alternative;
    } elseif ($pattern !== null && regexp_match($pattern, $data)) {
        return $alternative;
    } else {
        return $data;
    }
}

/**
 * Get the truncated data.
 *
 * @param string $data
 * @param int    $width
 * @param string $trimmarker
 * @param string $encoding
 *
 * @return string
 */
function truncate($data, $width = 0, $trimmarker = '...', $encoding = 'UTF-8')
{
    if (is_null($data)) {
        $data = '';
    }

    if (mb_strlen($data, $encoding) > $width) {
        $data = mb_substr($data, 0, $width, $encoding) . $trimmarker;
    }

    return $data;
}

/**
 * Output the data.
 *
 * @param string $data
 * @param bool   $return
 *
 * @return void|string
 */
function e($data, $return = false)
{
    if ($return) {
        return $data;
    } else {
        echo $data;
    }
}

/**
 * Output the data with new line.
 *
 * @param string $data
 * @param bool   $return
 *
 * @return void|string
 */
function n($data, $return = false)
{
    if (version_compare(phpversion(), '5.3.0') >= 0) {
        $data = nl2br($data, false);
    } else {
        $data = nl2br($data);
    }

    if ($return) {
        return $data;
    } else {
        echo $data;
    }
}

/**
 * Output the data for text.
 *
 * @param string $data
 * @param bool   $return
 *
 * @return void|string
 */
function t($data, $return = false)
{
    if (is_null($data)) {
        $data = '';
    }

    $data = htmlspecialchars($data, ENT_QUOTES, MAIN_INTERNAL_ENCODING);

    if ($return) {
        return $data;
    } else {
        echo $data;
    }
}

/**
 * Output the data for html.
 *
 * @param string $data
 * @param bool   $return
 *
 * @return void|string
 */
function h($data, $return = false)
{
    if (is_null($data)) {
        $data = '';
    }

    $data = htmlspecialchars($data, ENT_QUOTES, MAIN_INTERNAL_ENCODING);
    $data = n($data, true);

    if ($return) {
        return $data;
    } else {
        echo $data;
    }
}

/**
 * Get a language data.
 *
 * @param string      $key
 * @param string|null $language
 *
 * @return string|null
 */
function language($key, $language = null)
{
    static $default_language = null;

    if (empty($_SESSION['_language'])) {
        $accept_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

        $languages = array();
        foreach ($accept_languages as $accept_language) {
            if ($regexp = regexp_match('^(\w\w)[^;]*;q=([\d\.]+)$', $accept_language)) {
                if (empty($languages[$regexp[1]])) {
                    $languages[$regexp[1]] = $regexp[2];
                }
            } elseif ($regexp = regexp_match('^(\w\w)', $accept_language)) {
                $languages[$regexp[1]] = 1;
            }
        }

        arsort($languages);

        $language_keys = array_keys($languages);

        if (isset($language_keys[0])) {
            $_SESSION['_language'] = $language_keys[0];
        }
    }

    $dir = 'app/languages/';

    if ($language === null) {
        $language = $_SESSION['_language'];
    }

    if (file_exists(MAIN_PATH . MAIN_APPLICATION_PATH . $dir . 'default.php')) {
        import($dir . 'default.php');
    }
    if (!empty($_SESSION['_language']) && file_exists(MAIN_PATH . MAIN_APPLICATION_PATH . $dir . $language . '.php')) {
        import($dir . $language . '.php');
    }

    if ($default_language === null) {
        $default_languages = array_keys($GLOBALS['_language']);
        $default_language  = $default_languages[0];
    }

    if (isset($GLOBALS['_language'][$language][$key])) {
        return $GLOBALS['_language'][$language][$key];
    } elseif (isset($GLOBALS['_language'][$default_language][$key])) {
        return $GLOBALS['_language'][$default_language][$key];
    } else {
        return $key;
    }
}

/**
 * Format a local time/date.
 *
 * @param string|null $format
 * @param mixed|null  $timestamp
 *
 * @return mixed
 */
function localdate($format = null, $timestamp = null)
{
    static $time = 0;

    if ($time === 0) {
        $time = time() + MAIN_TIME;

        if (isset($GLOBALS['_time'])) {
            $time += $GLOBALS['_time'];
        }
    }

    if ($regexp = regexp_match('^(\d\d\d\d)\-(\d\d)\-(\d\d)', $timestamp)) {
        $year  = intval($regexp[1]);
        $month = intval($regexp[2]);
        $day   = intval($regexp[3]);

        if (!checkdate($month, $day, $year)) {
            return null;
        }
    }

    if ($timestamp === null) {
        $timestamp = $time;
    } elseif (!is_numeric($timestamp)) {
        $timestamp = strtotime($timestamp);
    }

    if ($format) {
        return date($format, $timestamp);
    } else {
        return $timestamp;
    }
}

/**
 * Get a client ip.
 *
 * @param bool $proxy
 *
 * @return string|null
 */
function clientip($proxy = false)
{
    if ($proxy) {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $addresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return $addresses[0];
        }
    }

    if (isset($_SERVER['REMOTE_ADDR'])) {
        return $_SERVER['REMOTE_ADDR'];
    } else {
        return null;
    }
}

/**
 * Determine if the request is secure.
 *
 * @param bool $proxy
 *
 * @return bool
 */
function ssl($proxy = false)
{
    if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) === 'on' || $_SERVER['HTTPS'] === 1)) {
        return true;
    } elseif ($proxy) {
        if (!empty($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] === 443) {
            return true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
    }

    return false;
}

/**
 * Get or check the token.
 *
 * @param string $type
 * @param string $name
 *
 * @return string|bool|null
 */
function token($type, $name = 'default')
{
    if ($type === 'check') {
        if ($_REQUEST['_token'] && isset($_SESSION['_token'][$name]['value']) && $_REQUEST['_token'] === $_SESSION['_token'][$name]['value']) {
            $flag = true;
        } else {
            $flag = false;
        }

        if (empty($_SESSION['_token'][$name]) || time() - $_SESSION['_token'][$name]['time'] > TOKEN_SPAN) {
            $_SESSION['_token'][$name] = array();
        }

        return $flag;
    } elseif ($type === 'drop') {
        $_SESSION['_token'][$name] = array();
    } elseif ($type === 'create') {
        if (empty($_SESSION['_token'][$name]) || time() - $_SESSION['_token'][$name]['time'] > TOKEN_SPAN) {
            $token = rand_string();

            $_SESSION['_token'][$name] = array(
                'value' => $token,
                'time'  => time(),
            );
        } else {
            $token = $_SESSION['_token'][$name]['value'];
        }

        return $token;
    } else {
        return null;
    }
}

/**
 * Redirect to the url.
 *
 * @param string $url
 *
 * @return void
 */
function redirect($url)
{
    if (!regexp_match('^https?\:\/\/', $url)) {
        $url = MAIN_FILE . $url;
    }

    header('Location: ' . $url);

    exit;
}

/**
 * Forward to the target.
 *
 * @param string|null $target
 *
 * @return string|null|void
 */
function forward($target = null)
{
    global $_params, $_view;

    static $forwarded = null;

    if ($target === null) {
        return $forwarded;
    } else {
        $forwarded = $target;

        if ($regexp = regexp_match('^\/[^\/]+\/.+$', $target)) {
            $_params = array_slice(explode('/', $target), 1);

            $_REQUEST['_mode'] = $_params[0];
            $_REQUEST['_work'] = $_params[1];

            if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/routing.php')) {
                import('app/routing.php', false);
            }

            controller($_REQUEST['_mode'] . '/' . $_REQUEST['_work'] . '.php');

            view($_REQUEST['_mode'] . '/' . $_REQUEST['_work'] . '.php');

            exit;
        } else {
            error('​Forward error' . (DEBUG_LEVEL ? ': ' . $target: ''));
        }
    }
}

/**
 * Output the data for debug.
 *
 * @param mixed $data
 * @param bool  $return
 *
 * @return void|mixed
 */
function debug($data, $return = false)
{
    if ($return) {
        return print_r($data, true);
    } else {
        print('<pre>');
        var_dump($data);
        print('</pre>');

        return;
    }
}

/**
 * Output the data for benchmark.
 *
 * @param string|null $label
 * @param bool        $forcing
 *
 * @return void
 */
function benchmark($label = null, $forcing = false)
{
    if ((DEBUG_LEVEL < 2 && $forcing === false) || php_sapi_name() === 'cli' || (isset($_REQUEST['_type']) && $_REQUEST['_type'] !== 'html')) {
        return;
    }

    static $now = 0, $start = 0, $count = 0;

    $prev = $now - $start;
    $now  = microtime(true);

    if ($start === 0) {
        $start = $now;

        return;
    }

    $count++;
    if ($label === null) {
        $label = sprintf('%03d', $count);
    }

    print('<pre>');
    printf('%s: ', $label);
    printf('total_time %s ms, ', number_format(($now - $start) * 1000, 5));
    if ($count !== 1) {
        printf('lap_time %s ms, ', number_format(($now - $start - $prev) * 1000, 5));
    }
    if (version_compare(phpversion(), '5.2.1') >= 0) {
        printf('memory_peak_usage %s kb, ', number_format(memory_get_peak_usage() / 1024));
    }
    printf('memory_usage %s kb', number_format(memory_get_usage() / 1024));
    print('</pre>');

    return;
}

/**
 * Output the debugbar.
 *
 * @return void
 */
function debugbar($label = null, $forcing = false)
{
    if (DEBUG_LEVEL !== 2 || empty($GLOBALS['_query']) || php_sapi_name() === 'cli' || (isset($_REQUEST['_type']) && $_REQUEST['_type'] !== 'html')) {
        return;
    }

    foreach ($GLOBALS['_query'] as $query) {
        print('<pre><code>' . $query . '</code></pre>');
    }

    return;
}

/**
 * Log the message to a logs.
 *
 * @param string      $type
 * @param string|null $message
 *
 * @return void
 */
function logging($type = 'message', $message = null)
{
    $log = clientip() . ' ' . clientip(true) . ' [' . localdate('Y-m-d H:i:s') . '] ' . $_SERVER['REQUEST_URI'];

    if ($type === 'get') {
        $file = LOGGING_PATH . 'get/' . localdate('Ymd') . '.log';

        if ($fp = fopen($file, 'a')) {
            chmod($file, PERMISSION_FILE);
            fwrite($fp, $log . "\n");
            fclose($fp);
        }
    } elseif ($type === 'post' || $type === 'files') {
        $dir  = LOGGING_PATH . $type . '/' . localdate('Ymd') . '/';
        $file = $dir . localdate('His') . '.log';

        if (!is_dir($dir)) {
            if (mkdir($dir, PERMISSION_DIRECTORY)) {
                chmod($dir, PERMISSION_DIRECTORY);
            }
        }

        if ($type === 'post') {
            $data = $_POST;
        } elseif ($type === 'files') {
            $data = $_FILES;
        }

        if ($fp = fopen($file, 'a')) {
            chmod($file, PERMISSION_FILE);
            fwrite($fp, $log . "\n" . print_r($data, true) . "\n");
            fclose($fp);
        }
    } else {
        $file = LOGGING_PATH . 'message/' . localdate('Ymd') . '.log';

        $message = regexp_replace("\r", '\r', $message);
        $message = regexp_replace("\n", '\n', $message);

        if ($message === null) {
            $message = '-';
        }

        if ($fp = fopen($file, 'a')) {
            chmod($file, PERMISSION_FILE);
            fwrite($fp, $log . ' ' . $message . "\n");
            fclose($fp);
        }
    }

    return;
}

/**
 * Check the authorization.
 *
 * @return bool
 */
function auth()
{
    if (DEBUG_LEVEL === 0) {
        if (DEBUG_PASSWORD && empty($_SESSION['_auth'])) {
            password();
        } elseif (DEBUG_ADDR && !in_array(clientip(), explode(',', DEBUG_ADDR))) {
            return false;
        } elseif (!DEBUG_PASSWORD && !DEBUG_ADDR) {
            return false;
        }
    }

    return true;
}

/**
 * Output the result.
 *
 * @param string|null $message
 * @param array       $values
 * @param string|null $type
 */
function ok($message = null, $values = array(), $type = null)
{
    if ($message === null) {
        $message = 'complete.';
    }
    if (empty($values)) {
        $values = array('token' => token('create'));
    }
    if ($type === null && isset($_REQUEST['_type'])) {
        $type = $_REQUEST['_type'];
    }

    db_commit();

    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/ok.php')) {
        import('app/controllers/ok.php');
    }

    if (php_sapi_name() === 'cli') {
        echo "OK: " . $message . "\n";
    } elseif ($type === 'json') {
        header('Content-Type: application/json; charset=' . MAIN_CHARSET);

        echo json_encode(array(
            'status'  => 'OK',
            'message' => $message,
            'values'  => $values,
        ));
    } elseif ($type === 'xml') {
        header('Content-Type: text/xml; charset=' . MAIN_CHARSET);

        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo "<response>\n";
        echo "<status>OK</status>\n";
        echo "<message>" . h($message, true) . "</message>\n";
        echo "<values>\n";

        foreach ($values as $key => $value) {
            echo "<" . $key . ">" . h($value, true) . "</" . $key . ">\n";
        }

        echo "</values>\n";
        echo "</response>\n";
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/views/ok_' . $_REQUEST['_mode'] . '.php')) {
        $_view['message'] = $message;
        $_view['values']  = $values;

        import('app/views/ok_' . $_REQUEST['_mode'] . '.php', false, true);
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/views/ok.php')) {
        $_view['message'] = $message;
        $_view['values']  = $values;

        import('app/views/ok.php', false, true);
    } else {
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
        echo "<title>OK</title>\n";

        style();

        echo "</head>\n";
        echo "<body>\n";
        echo "<h1>OK</h1>\n";
        echo "<p>" . h($message, true) . "</p>\n";
        echo "</body>\n";
        echo "</html>\n";
    }

    exit;
}

/**
 * Output the result for warning.
 *
 * @param string      $messages
 * @param array       $values
 * @param string|null $type
 */
function warning($messages, $values = array(), $type = null)
{
    global $_view;

    if (!is_array($messages)) {
        $messages = array($messages);
    }
    if (empty($values)) {
        $values = array('token' => token('create'));
    }
    if ($type === null && isset($_REQUEST['_type'])) {
        $type = $_REQUEST['_type'];
    }

    db_rollback();

    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/warning.php')) {
        import('app/controllers/warning.php');
    }

    if (php_sapi_name() === 'cli') {
        echo "WARNING: " . implode(', ', $messages) . "\n";
    } elseif ($type === 'json') {
        header('Content-Type: application/json; charset=' . MAIN_CHARSET);

        echo json_encode(array(
            'status'   => 'WARNING',
            'messages' => $messages,
            'values'   => $values,
        ));
    } elseif ($type === 'xml') {
        header('Content-Type: text/xml; charset=' . MAIN_CHARSET);

        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo "<response>\n";
        echo "<status>WARNING</status>\n";
        echo "<messages>\n";

        foreach ($messages as $message) {
            echo "<message>" . h($message, true) . "</message>\n";
        }

        echo "</messages>\n";
        echo "<values>\n";

        foreach ($values as $key => $value) {
            echo "<" . $key . ">" . h($value, true) . "</" . $key . ">\n";
        }

        echo "</values>\n";
        echo "</response>\n";
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/views/warning_' . $_REQUEST['_mode'] . '.php')) {
        $_view['messages'] = $messages;
        $_view['values']   = $values;

        import('app/views/warning_' . $_REQUEST['_mode'] . '.php', false, true);
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/views/warning.php')) {
        $_view['messages'] = $messages;
        $_view['values']   = $values;

        import('app/views/warning.php', false, true);
    } else {
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
        echo "<title>WARNING</title>\n";

        style();

        echo "</head>\n";
        echo "<body>\n";
        echo "<h1>WARNING</h1>\n";
        echo "<ul>\n";

        foreach ($messages as $message) {
            echo "<li>" . h($message, true) . "</li>\n";
        }

        echo "</ul>\n";
        echo "</body>\n";
        echo "</html>\n";
    }

    exit;
}

/**
 * Output the result for error.
 *
 * @param string      $message
 * @param array       $values
 * @param string|null $type
 */
function error($message, $values = array(), $type = null)
{
    global $_view;

    if (DEBUG_LEVEL >= 2 || LOGGING_MESSAGE) {
        $backtraces = debug_backtrace();
        $backtrace  = 'Application error: ' . $backtraces[0]['function'] . '(): ' . $backtraces[0]['args'][0] . ' in ' . $backtraces[0]['file'] . ' on line ' . $backtraces[0]['line'];

        if (DEBUG_LEVEL >= 2) {
            echo '<pre><code>' . $backtrace . '</code></pre>';
        }
        if (LOGGING_MESSAGE) {
            logging('message', $backtrace);
        }
    }

    if (empty($values)) {
        $values = array('token' => token('create'));
    }
    if ($type === null && isset($_REQUEST['_type'])) {
        $type = $_REQUEST['_type'];
    }

    db_rollback();

    if (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/controllers/error.php')) {
        import('app/controllers/error.php');
    }

    if (php_sapi_name() === 'cli') {
        echo "ERROR: " . $message . "\n";
    } elseif ($type === 'json') {
        header('Content-Type: application/json; charset=' . MAIN_CHARSET);

        echo json_encode(array(
            'status'  => 'ERROR',
            'message' => $message,
            'values'  => $values,
        ));
    } elseif ($type === 'xml') {
        header('Content-Type: text/xml; charset=' . MAIN_CHARSET);

        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo "<response>\n";
        echo "<status>ERROR</status>\n";
        echo "<message>" . h($message, true) . "</message>\n";
        echo "<values>\n";

        foreach ($values as $key => $value) {
            echo "<" . $key . ">" . h($value, true) . "</" . $key . ">\n";
        }

        echo "</values>\n";
        echo "</response>\n";
    } elseif (isset($_REQUEST['_mode']) && is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/views/error_' . $_REQUEST['_mode'] . '.php')) {
        $_view['message'] = $message;
        $_view['values']  = $values;

        import('app/views/error_' . $_REQUEST['_mode'] . '.php', false, true);
    } elseif (is_file(MAIN_PATH . MAIN_APPLICATION_PATH . 'app/views/error.php')) {
        $_view['message'] = $message;
        $_view['values']  = $values;

        import('app/views/error.php', false, true);
    } else {
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
        echo "<title>ERROR</title>\n";

        style();

        echo "</head>\n";
        echo "<body>\n";
        echo "<h1>ERROR</h1>\n";
        echo "<p>" . h($message, true) . "</p>\n";
        echo "</body>\n";
        echo "</html>\n";
    }

    exit;
}

/**
 * Output a page for authorization.
 *
 */
function password()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['password'] === DEBUG_PASSWORD) {
            $_SESSION['_auth'] = true;

            redirect('/?_mode=' . $_REQUEST['_mode']);
        }

        $_view['message'] = 'Password is incorrect.';
    } else {
        $_view['message'] = '';
    }

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>Authorization</title>\n";

    style();

    echo "</head>\n";
    echo "<body>\n";
    echo "<h1>Authorization</h1>\n";

    if ($_view['message'] !== '') {
        echo "<ul>\n";
        echo "<li>" . $_view['message'] . "</li>\n";
        echo "</ul>\n";
    }

    echo "<form action=\"" . t(MAIN_FILE, true) . "/?_mode=" . t($_REQUEST['_mode'], true) . "\" method=\"post\">\n";
    echo "<fieldset>\n";
    echo "<legend>authorise</legend>\n";
    echo "<dl>\n";
    echo "<dt>password</dt>\n";
    echo "<dd><input type=\"password\" name=\"password\" size=\"20\" value=\"\"></dd>\n";
    echo "</dl>\n";
    echo "<p><input type=\"submit\" value=\"authorise\"></p>\n";
    echo "</fieldset>\n";
    echo "</form>\n";

    echo "</body>\n";
    echo "</html>\n";

    exit;
}

/**
 * Output a page for this framework.
 *
 */
function about()
{
    if (php_sapi_name() === 'cli') {
        echo "levis: PHP Framework\n";
        echo "Version => " . VERSION_NUMBER . "\n";
        echo "Update => " . VERSION_UPDATE . "\n";
        exit;
    }

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>levis: PHP Framework</title>\n";

    style();

    echo "</head>\n";
    echo "<body>\n";
    echo "<h1>levis: PHP Framework</h1>\n";
    echo "<p>Version " . VERSION_NUMBER . ' (' . VERSION_UPDATE . ")</p>\n";

    echo "<h2>Menu</h2>\n";
    echo "<ul>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=info_php\">phpinfo</a></li>\n";

    if (DATABASE_TYPE) {
        echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin\">database</a></li>\n";
    }
    if (file_exists(DATABASE_MIGRATE_PATH)) {
        $resource = db_query(db_sql('table_list'));
        $results  = db_result($resource);

        $flag = false;
        foreach ($results as $result) {
            $table = array_shift($result);

            if ($table === DATABASE_PREFIX . 'levis_migrations') {
                $flag = true;

                break;
            }
        }

        $succeeded = 0;
        if ($flag === true) {
            $resource = db_query('SELECT COUNT(*) as count FROM ' . DATABASE_PREFIX . 'levis_migrations WHERE status = ' . db_escape('success') . ';');
            $results  = db_result($resource);

            if ($results[0]['count']) {
                $succeeded = $results[0]['count'];
            }
        }

        $target = 0;
        if ($dh = opendir(DATABASE_MIGRATE_PATH)) {
            while (($entry = readdir($dh)) !== false) {
                if (!is_file(DATABASE_MIGRATE_PATH  . $entry)) {
                    continue;
                }

                if (!regexp_match('^([0-9\-]{14})-[_a-zA-Z0-9\-]+\.sql$', $entry)) {
                    continue;
                }

                $target++;
            }
            closedir($dh);
        }

        $unexecuted = $target - $succeeded;
        if ($unexecuted > 1) {
            $unexecuted = ' (<em>' . $unexecuted . '</em> / <a href="' . t(MAIN_FILE, true) . '/?_mode=db_migrate&amp;limit=1">single step execution</a>)';
        } elseif ($unexecuted > 0) {
            $unexecuted = ' (<em>' . $unexecuted . '</em>)';
        } else {
            $unexecuted = '';
        }

        echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_migrate\">migrate</a>" . $unexecuted . "</li>\n";
    }
    if (file_exists(DATABASE_SCAFFOLD_PATH)) {
        echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_scaffold\">scaffold</a></li>\n";
    }
    if (file_exists(TEST_PATH)) {
        echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=test_index\">test</a></li>\n";
    }

    echo "</ul>\n";

    echo "<h2>Configuration</h2>\n";

    echo "<h3>Main</h3>\n";
    echo "<dl>\n";
    echo "<dt>main file</dt>\n";
    echo "<dd><code>" . alt(MAIN_FILE, '-') . "</code></dd>\n";
    echo "<dt>path to libraries</dt>\n";
    echo "<dd><code>" . alt(MAIN_LIBRARY_PATH, '-') . "</code></dd>\n";
    echo "<dt>path to application</dt>\n";
    echo "<dd><code>" . alt(MAIN_APPLICATION_PATH, '-') . "</code></dd>\n";
    echo "<dt>default mode</dt>\n";
    echo "<dd><code>" . alt(MAIN_DEFAULT_MODE, '-') . "</code></dd>\n";
    echo "<dt>default work</dt>\n";
    echo "<dd><code>" . alt(MAIN_DEFAULT_WORK, '-') . "</code></dd>\n";
    echo "<dt>internal encoding</dt>\n";
    echo "<dd><code>" . alt(MAIN_INTERNAL_ENCODING, '-') . "</code></dd>\n";
    echo "<dt>charset</dt>\n";
    echo "<dd><code>" . alt(MAIN_CHARSET, '-') . "</code></dd>\n";
    echo "<dt>time</dt>\n";
    echo "<dd><code>" . MAIN_TIME . "</code></dd>\n";
    echo "</dl>\n";
    echo "<dl>\n";
    echo "<dt>path to models</dt>\n";
    echo "<dd><code>" . MAIN_APPLICATION_PATH . 'app/models/' . "</code></dd>\n";
    echo "<dt>path to views</dt>\n";
    echo "<dd><code>" . MAIN_APPLICATION_PATH . 'app/views/' . "</code></dd>\n";
    echo "<dt>path to controllers</dt>\n";
    echo "<dd><code>" . MAIN_APPLICATION_PATH . 'app/controllers/' . "</code></dd>\n";
    echo "<dt>path to core libraries</dt>\n";
    echo "<dd><code>" . MAIN_LIBRARY_PATH . 'libs/cores/' . "</code></dd>\n";
    echo "<dt>path to modules</dt>\n";
    echo "<dd><code>" . MAIN_LIBRARY_PATH . 'libs/modules/' . "</code></dd>\n";
    echo "</dl>\n";

    if (DATABASE_TYPE) {
        echo "<h3>Database</h3>\n";
        echo "<dl>\n";
        echo "<dt>type</dt>\n";
        echo "<dd><code>" . DATABASE_TYPE . "</code></dd>\n";
        echo "<dt>name</dt>\n";
        echo "<dd><code>" . alt(DATABASE_NAME, '-') . "</code></dd>\n";
        echo "<dt>prefix</dt>\n";
        echo "<dd><code>" . alt(DATABASE_PREFIX, '-') . "</code></dd>\n";
        echo "<dt>charset</dt>\n";
        echo "<dd><code>" . alt(DATABASE_CHARSET, '-') . "</code></dd>\n";
        echo "<dt>charset input from</dt>\n";
        echo "<dd><code>" . alt(DATABASE_CHARSET_INPUT_FROM, '-') . "</code></dd>\n";
        echo "<dt>charset input to</dt>\n";
        echo "<dd><code>" . alt(DATABASE_CHARSET_INPUT_TO, '-') . "</code></dd>\n";
        echo "<dt>charset output from</dt>\n";
        echo "<dd><code>" . alt(DATABASE_CHARSET_OUTPUT_FROM, '-') . "</code></dd>\n";
        echo "<dt>charset output to</dt>\n";
        echo "<dd><code>" . alt(DATABASE_CHARSET_OUTPUT_TO, '-') . "</code></dd>\n";
        echo "<dt>migrate path</dt>\n";
        echo "<dd><code>" . alt(DATABASE_MIGRATE_PATH, '-') . "</code></dd>\n";
        echo "<dt>scaffold path</dt>\n";
        echo "<dd><code>" . alt(DATABASE_SCAFFOLD_PATH, '-') . "</code></dd>\n";
        echo "<dt>backup path</dt>\n";
        echo "<dd><code>" . alt(DATABASE_BACKUP_PATH, '-') . "</code></dd>\n";
        echo "<dt>autostart</dt>\n";
        echo "<dd><code>" . (DATABASE_AUTOCONNECT ? 'true' : 'false') . "</code></dd>\n";
        echo "</dl>\n";
    }

    echo "<h3>Session</h3>\n";
    echo "<dl>\n";
    echo "<dt>lifetime</dt>\n";
    echo "<dd><code>" . SESSION_LIFETIME . "</code></dd>\n";
    echo "<dt>path</dt>\n";
    echo "<dd><code>" . alt(SESSION_PATH, '-') . "</code></dd>\n";
    echo "<dt>cache</dt>\n";
    echo "<dd><code>" . alt(SESSION_CACHE, '-') . "</code></dd>\n";
    echo "<dt>autostart</dt>\n";
    echo "<dd><code>" . (SESSION_AUTOSTART ? 'true' : 'false') . "</code></dd>\n";
    echo "</dl>\n";

    echo "<h3>Token</h3>\n";
    echo "<dl>\n";
    echo "<dt>span</dt>\n";
    echo "<dd><code>" . TOKEN_SPAN . "</code></dd>\n";
    echo "</dl>\n";

    echo "<h3>Regexp</h3>\n";
    echo "<dl>\n";
    echo "<dt>type</dt>\n";
    echo "<dd><code>" . alt(REGEXP_TYPE, '-') . "</code></dd>\n";
    echo "</dl>\n";

    echo "<h3>Autoload</h3>\n";
    echo "<dl>\n";
    echo "<dt>model</dt>\n";
    echo "<dd><code>" . (AUTOLOAD_MODEL ? 'true' : 'false') . "</code></dd>\n";
    echo "<dt>service</dt>\n";
    echo "<dd><code>" . (AUTOLOAD_SERVICE ? 'true' : 'false') . "</code></dd>\n";
    echo "</dl>\n";

    echo "<h3>Page</h3>\n";
    echo "<dl>\n";
    echo "<dt>path</dt>\n";
    echo "<dd><code>" . alt(PAGE_PATH, '-') . "</code></dd>\n";
    echo "<dt>controller</dt>\n";
    echo "<dd><code>" . alt(PAGE_CONTROLLER, '-') . "</code></dd>\n";
    echo "</dl>\n";

    echo "<h3>Permission</h3>\n";
    echo "<dl>\n";
    echo "<dt>directory</dt>\n";
    echo "<dd><code>" . alt('0' . decoct(PERMISSION_DIRECTORY), '-') . "</code></dd>\n";
    echo "<dt>file</dt>\n";
    echo "<dd><code>" . alt('0' . decoct(PERMISSION_FILE), '-') . "</code></dd>\n";
    echo "</dl>\n";

    echo "<h3>Test</h3>\n";
    echo "<dl>\n";
    echo "<dt>path</dt>\n";
    echo "<dd><code>" . alt(TEST_PATH, '-') . "</code></dd>\n";
    echo "</dl>\n";

    echo "<h3>Debug</h3>\n";
    echo "<dl>\n";
    echo "<dt>level</dt>\n";
    echo "<dd><code>" . DEBUG_LEVEL . "</code></dd>\n";
    echo "<dt>password</dt>\n";
    echo "<dd><code>" . alt(str_repeat('*', strlen(DEBUG_PASSWORD)), '-') . "</code></dd>\n";
    echo "<dt>addr</dt>\n";
    echo "<dd><code>" . alt(DEBUG_ADDR, '-') . "</code></dd>\n";
    echo "</dl>\n";

    echo "<h3>Logging</h3>\n";
    echo "<dl>\n";
    echo "<dt>path</dt>\n";
    echo "<dd><code>" . alt(LOGGING_PATH, '-') . "</code></dd>\n";
    echo "<dt>message</dt>\n";
    echo "<dd><code>" . (LOGGING_MESSAGE ? 'true' : 'false') . "</code></dd>\n";
    echo "<dt>get</dt>\n";
    echo "<dd><code>" . (LOGGING_GET ? 'true' : 'false') . "</code></dd>\n";
    echo "<dt>post</dt>\n";
    echo "<dd><code>" . (LOGGING_POST ? 'true' : 'false') . "</code></dd>\n";
    echo "<dt>files</dt>\n";
    echo "<dd><code>" . (LOGGING_FILES ? 'true' : 'false') . "</code></dd>\n";
    echo "</dl>\n";

    echo "</body>\n";
    echo "</html>\n";

    exit;
}

/**
 * Output a style for this framework.
 *
 */
function style()
{
    echo "<style>\n";
    echo "html {\n";
    echo "    background-color: #EEEEEE;\n";
    echo "}\n";
    echo "body {\n";
    echo "    margin: 20px;\n";
    echo "    padding: 0 20px 20px 20px;\n";
    echo "    border: 1px solid #CCCCCC;\n";
    echo "    background-color: #FFFFFF;\n";
    echo "    color: #222222;\n";
    echo "}\n";
    echo "h1 {\n";
    echo "    padding-bottom: 10px;\n";
    echo "    border-bottom: 1px solid #CCCCCC;\n";
    echo "    font-size: 150%;\n";
    echo "}\n";
    echo "h2 {\n";
    echo "    font-size: 120%;\n";
    echo "}\n";
    echo "h3 {\n";
    echo "    font-size: 110%;\n";
    echo "}\n";
    echo "h4, h5, h6 {\n";
    echo "    font-size: 100%;\n";
    echo "}\n";
    echo "em {\n";
    echo "    font-style: normal;\n";
    echo "    font-weight: bold;\n";
    echo "}\n";
    echo "code {\n";
    echo "    color: #333366;\n";
    echo "}\n";
    echo "table tr th {\n";
    echo "    padding: 3px;\n";
    echo "    border: 1px solid #CCCCCC;\n";
    echo "    background-color: #EEEEEE;\n";
    echo "}\n";
    echo "table tr td {\n";
    echo "    padding: 3px;\n";
    echo "    border: 1px solid #CCCCCC;\n";
    echo "}\n";
    echo "fieldset {\n";
    echo "    border: 1px solid #CCCCCC;\n";
    echo "}\n";
    echo "a {\n";
    echo "    color: #003399;\n";
    echo "}\n";
    echo "</style>\n";
}
