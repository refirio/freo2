<?php

/*******************************************************************************

 Functions for DB

*******************************************************************************/

/**
 * Connect to the database.
 *
 * @param mixed $info
 *
 * @return void
 */
function db_connect($info)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return;
    }

    if (is_array($info)) {
        foreach ($info as $key => $confg) {
            $_db['resource'][$key] = array(
                'config' => array(
                    'type'                => isset($confg['type'])                ? $confg['type']                : DATABASE_TYPE,
                    'host'                => isset($confg['host'])                ? $confg['host']                : DATABASE_HOST,
                    'port'                => isset($confg['port'])                ? $confg['port']                : DATABASE_PORT,
                    'username'            => isset($confg['username'])            ? $confg['username']            : DATABASE_USERNAME,
                    'password'            => isset($confg['password'])            ? $confg['password']            : DATABASE_PASSWORD,
                    'name'                => isset($confg['name'])                ? $confg['name']                : DATABASE_NAME,
                    'prefix'              => isset($confg['prefix'])              ? $confg['prefix']              : DATABASE_PREFIX,
                    'charset'             => isset($confg['charset'])             ? $confg['charset']             : DATABASE_CHARSET,
                    'charset_input_from'  => isset($confg['charset_input_from'])  ? $confg['charset_input_from']  : DATABASE_CHARSET_INPUT_FROM,
                    'charset_input_to'    => isset($confg['charset_input_to'])    ? $confg['charset_input_to']    : DATABASE_CHARSET_INPUT_TO,
                    'charset_output_from' => isset($confg['charset_output_from']) ? $confg['charset_output_from'] : DATABASE_CHARSET_OUTPUT_FROM,
                    'charset_output_to'   => isset($confg['charset_output_to'])   ? $confg['charset_output_to']   : DATABASE_CHARSET_OUTPUT_TO,
                ),
                'dbh' => null,
            );
        }
    } elseif ($info === 'default') {
        $_db['target'] = 'default';

        if (isset($_db['resource'][$_db['target']])) {
            return;
        } else {
            $_db['resource'][$_db['target']] = array(
                'config' => array(
                    'type'                => DATABASE_TYPE,
                    'host'                => DATABASE_HOST,
                    'port'                => DATABASE_PORT,
                    'username'            => DATABASE_USERNAME,
                    'password'            => DATABASE_PASSWORD,
                    'name'                => DATABASE_NAME,
                    'prefix'              => DATABASE_PREFIX,
                    'charset'             => DATABASE_CHARSET,
                    'charset_input_from'  => DATABASE_CHARSET_INPUT_FROM,
                    'charset_input_to'    => DATABASE_CHARSET_INPUT_TO,
                    'charset_output_from' => DATABASE_CHARSET_OUTPUT_FROM,
                    'charset_output_to'   => DATABASE_CHARSET_OUTPUT_TO,
                ),
                'dbh' => null,
            );
        }
    } else {
        $_db['target'] = $info;

        return;
    }

    foreach ($_db['resource'] as $key => $data) {
        $_db['target'] = $key;

        if (!empty($_db['resource'][$_db['target']]['dbh'])) {
            continue;
        }

        if ($data['config']['type'] === 'pdo_mysql' || $data['config']['type'] === 'pdo_pgsql' || $data['config']['type'] === 'pdo_sqlite' || $data['config']['type'] === 'pdo_sqlite2') {
            import('libs/cores/db_pdo.php');
        } elseif ($data['config']['type'] === 'mysql') {
            import('libs/cores/db_mysql.php');
        } elseif ($data['config']['type'] === 'pgsql') {
            import('libs/cores/db_pgsql.php');
        } elseif ($data['config']['type'] === 'sqlite') {
            import('libs/cores/db_sqlite.php');
        }

        db_driver_connect();

        if (($data['config']['type'] === 'pdo_mysql' || $data['config']['type'] === 'mysql' || $data['config']['type'] === 'pdo_pgsql' || $data['config']['type'] === 'pgsql') && $data['config']['charset']) {
            $resource = db_query('SET NAMES ' . db_escape($data['config']['charset']) . ';');
            if (!$resource) {
                if (LOGGING_MESSAGE) {
                    logging('message', 'db: Set names error: ' . db_error());
                }

                error('db: Set names error' . (DEBUG_LEVEL ? ': ' . db_error(): ''));
            }
        }
    }

    return;
}

/**
 * Query to the database.
 *
 * @param mixed $query
 * @param bool  $return
 * @param bool  $error
 *
 * @return mixed
 */
function db_query($query, $return = false, $error = true)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return false;
    }

    if (is_array($query)) {
        $queries = db_placeholder(array(
            'query' => $query,
        ));

        $query = $queries['query'];
    }

    if ($_db['resource'][$_db['target']]['config']['charset_input_to'] !== $_db['resource'][$_db['target']]['config']['charset_input_from']) {
        $query = convert($query, $_db['resource'][$_db['target']]['config']['charset_input_to'], $_db['resource'][$_db['target']]['config']['charset_input_from']);
    }

    if ($return) {
        return $query;
    } else {
        if (DEBUG_LEVEL === 3 && php_sapi_name() !== 'cli' && (!isset($_REQUEST['_type']) || $_REQUEST['_type'] === 'html')) {
            echo '<pre><code>' . $query . '</code></pre>';
        } elseif (DEBUG_LEVEL === 2) {
            $GLOBALS['_query'][] = $query;
        }

        $resource = db_driver_query($query);

        if (!$resource && $error) {
            if (LOGGING_MESSAGE) {
                logging('message', 'db: Query error: ' . db_error() . ': ' . $query);
            }

            error('db: Query error' . (DEBUG_LEVEL ? ': ' . db_error(): ''));
        }

        return $resource;
    }
}

/**
 * Get the result from the database.
 *
 * @param mixed $resource
 *
 * @return array
 */
function db_result($resource)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return array();
    }

    $results = db_driver_result($resource);

    if ($_db['resource'][$_db['target']]['config']['charset_output_to'] !== $_db['resource'][$_db['target']]['config']['charset_output_from']) {
        $results = convert($results, $_db['resource'][$_db['target']]['config']['charset_output_to'], $_db['resource'][$_db['target']]['config']['charset_output_from']);
    }

    return $results;
}

/**
 * Get the count from the database.
 *
 * @param mixed $resource
 *
 * @return int
 */
function db_count($resource)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return -1;
    }

    return db_driver_count($resource);
}

/**
 * Get the affected count from the database.
 *
 * @param mixed $resource
 *
 * @return int
 */
function db_affected_count($resource)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return -1;
    }

    return db_driver_affected_count($resource);
}

/**
 * Get the escaped data for database.
 *
 * @param string $data
 *
 * @return string
 */
function db_escape($data)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return $data;
    }

    if ($data === null) {
        return 'NULL';
    } elseif ($data === true && (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql')) {
        return 'TRUE';
    } elseif ($data === false && (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql')) {
        return 'FALSE';
    } elseif ($data === 0 || regexp_match('^[1-9]+[0-9]*$', $data)) {
        return $data;
    }

    return db_driver_escape($data);
}

/**
 * Get the unescaped data for database.
 *
 * @param string $data
 *
 * @return string
 */
function db_unescape($data)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return $data;
    }

    if ($data === null) {
        return 'NULL';
    } elseif ($data === true && (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql')) {
        return 'TRUE';
    } elseif ($data === false && (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql')) {
        return 'FALSE';
    } elseif ($data === 0 || regexp_match('^[1-9]+[0-9]*$', $data)) {
        return $data;
    }

    return db_driver_unescape($data);
}

/**
 * Get the placeholder data for database.
 *
 * @param array $data
 *
 * @return string
 */
function db_placeholder($data)
{
    $holder = rand_string();

    foreach ($data as $index => $query) {
        if (is_array($query) && count($query) === 2 && isset($query[1]) && is_array($query[1])) {
            list($query, $holders) = $query;

            $query = str_replace(':', $holder, $query);

            if (isset($holders[0])) {
                foreach ($holders as $key => $value) {
                    if (regexp_match($holder . '\?', $query)) {
                        $query = regexp_replace($holder . '\?', $value, $query, 1);
                    }
                }
            } else {
                array_multisort(array_map('strlen', array_keys($holders)), SORT_DESC, $holders);

                foreach ($holders as $key => $value) {
                    if (regexp_match(':', $key)) {
                        list($key, $option) = explode(':', $key, 2);
                    } else {
                        $option = null;
                    }

                    if (regexp_match($holder . $key, $query)) {
                        if ($option == 'bool') {
                            if ((DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql')) {
                                $value = $value ? 'TRUE' : 'FALSE';
                            } else {
                                $value = $value ? 1 : 0;
                            }
                        } elseif ($option == 'number') {
                            $value = intval($value);
                        } elseif ($option == 'string') {
                            $value = '\'' . $value . '\'';
                        } else {
                            $value = is_array($value) ? $value[0] : db_escape($value);
                        }
                        $query = regexp_replace($holder . $key, $value, $query);
                    }
                }
            }

            $query = str_replace($holder, ':', $query);

            $data[$index] = $query;
        }
    }

    return $data;
}

/**
 * Get the error from the database.
 *
 * @return string
 */
function db_error()
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return '';
    }

    return db_driver_error();
}

/**
 * Select the data from the database.
 *
 * @param array $queries
 * @param bool  $return
 * @param bool  $error
 *
 * @return mixed
 */
function db_select($queries, $return = false, $error = true)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return array();
    }

    $queries = db_placeholder($queries);

    if (isset($queries['select']) && $queries['select'] !== '') {
        $queries['select'] = 'SELECT ' . $queries['select'] . ' ';
    } else {
        $queries['select'] = 'SELECT * ';
    }
    if (isset($queries['from']) && $queries['from'] !== '') {
        $queries['from'] = 'FROM ' . $queries['from'] . ' ';
    } else {
        return array();
    }

    if (isset($queries['where']) && $queries['where'] !== '') {
        $queries['where'] = 'WHERE ' . $queries['where'] . ' ';
    } else {
        $queries['where'] = '';
    }
    if (isset($queries['group_by']) && $queries['group_by'] !== '') {
        $queries['group_by'] = 'GROUP BY ' . $queries['group_by'] . ' ';
    } else {
        $queries['group_by'] = '';
    }
    if (isset($queries['having']) && $queries['having'] !== '') {
        $queries['having'] = 'HAVING ' . $queries['having'] . ' ';
    } else {
        $queries['having'] = '';
    }
    if (isset($queries['order_by']) && $queries['order_by'] !== '') {
        $queries['order_by'] = 'ORDER BY ' . $queries['order_by'] . ' ';
    } else {
        $queries['order_by'] = '';
    }
    if (isset($queries['offset']) && $queries['offset'] !== '') {
        $queries['offset'] = 'OFFSET ' . $queries['offset'] . ' ';
    } else {
        $queries['offset'] = '';
    }
    if (isset($queries['limit']) && $queries['limit'] !== '') {
        $queries['limit'] = 'LIMIT ' . $queries['limit'] . ' ';
    } else {
        $queries['limit'] = '';
    }
    if (isset($queries['option']) && $queries['option'] !== '') {
        $queries['option'] = $queries['option'] . ' ';
    } else {
        $queries['option'] = '';
    }

    $query = trim($queries['select'] . $queries['from'] . $queries['where'] . $queries['group_by'] . $queries['having'] . $queries['order_by'] . $queries['offset'] . $queries['limit'] . $queries['option']) . ';';

    if ($return) {
        return $query;
    } else {
        $resource = db_query($query, false, $error);

        if ($resource) {
            return db_result($resource);
        } else {
            return $resource;
        }
    }
}

/**
 * Insert the data to the database.
 *
 * @param array $queries
 * @param bool  $return
 * @param bool  $error
 *
 * @return mixed
 */
function db_insert($queries, $return = false, $error = true)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return false;
    }

    $queries = db_placeholder($queries);

    if (isset($queries['insert_into']) && $queries['insert_into'] !== '') {
        $queries['insert_into'] = 'INSERT INTO ' . $queries['insert_into'] . ' ';
    } else {
        return false;
    }

    if (isset($queries['values']) && is_array($queries['values'])) {
        $keys   = array();
        $values = array();

        foreach ((array)$queries['values'] as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $values[] = $value[0];
            } elseif ($value === '' || $value === '\'\'') {
                $values[] = 'NULL';
            } else {
                $values[] = db_escape($value);
            }
        }

        $queries['values'] = '(' . implode(', ', $keys) . ') VALUES(' . implode(', ', $values) . ') ';
    } elseif (isset($queries['values']) && $queries['values'] !== '') {
        $queries['values'] = 'VALUES ' . $queries['values'] . ' ';
    } else {
        return false;
    }
    if (isset($queries['option']) && $queries['option'] !== '') {
        $queries['option'] = $queries['option'] . ' ';
    } else {
        $queries['option'] = '';
    }

    $query = trim($queries['insert_into'] . $queries['values'] . $queries['option']) . ';';

    if ($return) {
        return $query;
    } else {
        return db_query($query, false, $error);
    }
}

/**
 * Update the data to the database.
 *
 * @param array $queries
 * @param bool  $return
 * @param bool  $error
 *
 * @return mixed
 */
function db_update($queries, $return = false, $error = true)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return false;
    }

    $queries = db_placeholder($queries);

    if (isset($queries['update']) && $queries['update'] !== '') {
        $queries['update'] = 'UPDATE ' . $queries['update'] . ' ';
    } else {
        return false;
    }

    if (isset($queries['set']) && is_array($queries['set'])) {
        $sets = array();

        foreach ((array)$queries['set'] as $key => $value) {
            if (is_array($value)) {
                $sets[] = $key . ' = ' . $value[0];
            } elseif ($value === '' || $value === '\'\'') {
                $sets[] = $key . ' = NULL';
            } else {
                $sets[] = $key . ' = ' . db_escape($value);
            }
        }

        $queries['set'] = 'SET ' . implode(', ', $sets) . ' ';
    } elseif (isset($queries['set']) && $queries['set'] !== '') {
        $queries['set'] = 'SET ' . $queries['set'] . ' ';
    } else {
        return false;
    }

    if (isset($queries['where']) && $queries['where'] !== '') {
        $queries['where'] = 'WHERE ' . $queries['where'] . ' ';
    } else {
        $queries['where'] = '';
    }
    if (isset($queries['offset']) && $queries['offset'] !== '') {
        $queries['offset'] = 'OFFSET ' . $queries['offset'] . ' ';
    } else {
        $queries['offset'] = '';
    }
    if (isset($queries['limit']) && $queries['limit'] !== '') {
        $queries['limit'] = 'LIMIT ' . $queries['limit'] . ' ';
    } else {
        $queries['limit'] = '';
    }
    if (isset($queries['option']) && $queries['option'] !== '') {
        $queries['option'] = $queries['option'] . ' ';
    } else {
        $queries['option'] = '';
    }

    $query = trim($queries['update'] . $queries['set'] . $queries['where'] . $queries['offset'] . $queries['limit'] . $queries['option']) . ';';

    if ($return) {
        return $query;
    } else {
        return db_query($query, false, $error);
    }
}

/**
 * Delete the data to the database.
 *
 * @param array $queries
 * @param bool  $return
 * @param bool  $error
 *
 * @return mixed
 */
function db_delete($queries, $return = false, $error = true)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return false;
    }

    $queries = db_placeholder($queries);

    if (isset($queries['delete_from']) && $queries['delete_from'] !== '') {
        $queries['delete_from'] = 'DELETE FROM ' . $queries['delete_from'] . ' ';
    } else {
        return false;
    }

    if (isset($queries['where']) && $queries['where'] !== '') {
        $queries['where'] = 'WHERE ' . $queries['where'] . ' ';
    } else {
        $queries['where'] = '';
    }
    if (isset($queries['offset']) && $queries['offset'] !== '') {
        $queries['offset'] = 'OFFSET ' . $queries['offset'] . ' ';
    } else {
        $queries['offset'] = '';
    }
    if (isset($queries['limit']) && $queries['limit'] !== '') {
        $queries['limit'] = 'LIMIT ' . $queries['limit'] . ' ';
    } else {
        $queries['limit'] = '';
    }
    if (isset($queries['option']) && $queries['option'] !== '') {
        $queries['option'] = $queries['option'] . ' ';
    } else {
        $queries['option'] = '';
    }

    $query = trim($queries['delete_from'] . $queries['where'] . $queries['offset'] . $queries['limit'] . $queries['option']) . ';';

    if ($return) {
        return $query;
    } else {
        return db_query($query, false, $error);
    }
}

/**
 * Get the last insert id from the database.
 *
 * @param string|null $sequence
 *
 * @return string
 */
function db_last_insert_id($sequence = null)
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return -1;
    }

    return db_driver_last_insert_id($sequence);
}

/**
 * Start a transaction.
 *
 * @return mixed
 */
function db_transaction()
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return false;
    }

    $_db['transaction'][$_db['target']] = true;

    return db_driver_transaction();
}

/**
 * Commit a transaction.
 *
 * @return mixed
 */
function db_commit()
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return false;
    }

    if (isset($_db['transaction'][$_db['target']]) && $_db['transaction'][$_db['target']] === true) {
        $_db['transaction'][$_db['target']] = false;
    } else {
        return false;
    }

    return db_driver_commit();
}

/**
 * Rollback a transaction.
 *
 * @return mixed
 */
function db_rollback()
{
    global $_db;

    if (DATABASE_TYPE === '') {
        return false;
    }

    if (isset($_db['target']) && isset($_db['transaction'][$_db['target']]) && $_db['transaction'][$_db['target']] === true) {
        $_db['transaction'][$_db['target']] = false;
    } else {
        return false;
    }

    return db_driver_rollback();
}

/**
 * Output a admin page for database.
 *
 */
function db_admin()
{
    if (php_sapi_name() === 'cli') {
        echo "levis: PHP Framework\n";
        echo "Please access via browser.\n";
        exit;
    }

    if (DATABASE_TYPE === '') {
        return;
    }
    if (auth() === false) {
        return;
    }

    if ($_REQUEST['_work'] === 'import') {
        db_admin_import();
    } elseif ($_REQUEST['_work'] === 'export') {
        db_admin_export();
    } elseif ($_REQUEST['_work'] === 'backup') {
        db_admin_backup();
    } else {
        db_admin_sql();
    }

    exit;
}

/**
 * Output a import page for database.
 *
 */
function db_admin_import()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['means'] === 'upload') {
            if (is_uploaded_file($_FILES['target']['tmp_name'])) {
                $target = $_FILES['target']['tmp_name'];
            } else {
                error('db: Import file not found');
            }
        } else {
            $target = DATABASE_NAME . '.sql';

            if (!is_file($target)) {
                error('db: Import file not found');
            }
        }

        $count = db_import($target);

        $_view['message'] = $count . ' sql executed.';
    } else {
        $_view['message'] = '';
    }

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>DB</title>\n";

    style();

    echo "</head>\n";
    echo "<body>\n";
    echo "<h1><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin\">DB</a></h1>\n";

    echo "<h2>Menu</h2>\n";
    echo "<ul>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=sql\">SQL</a></li>\n";
    echo "<li>Import</li>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=export\">Export</a></li>\n";

    if (file_exists(DATABASE_BACKUP_PATH)) {
        echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=backup\">Backup</a></li>\n";
    }

    echo "</ul>\n";

    echo "<h2>Import</h2>\n";

    if ($_view['message']) {
        echo "<ul>\n";
        echo "<li>" . $_view['message'] . "</li>\n";
        echo "</ul>\n";
    } else {
        echo "<ul>\n";
        echo "<li>Import from SQL file.</li>\n";
        echo "</ul>\n";
    }

    echo "<form action=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=import\" method=\"post\" enctype=\"multipart/form-data\">\n";
    echo "<fieldset>\n";
    echo "<legend>import</legend>\n";
    echo "<dl>\n";
    echo "<dt><label><input type=\"radio\" name=\"means\" value=\"upload\" checked=\"checked\"> upload</label></dt>\n";
    echo "<dd><input type=\"file\" name=\"target\" size=\"30\"></dd>\n";
    echo "<dt><label><input type=\"radio\" name=\"means\" value=\"file\"> read</label></dt>\n";
    echo "<dd>\n";
    echo "<code title=\"" . dirname($_SERVER['SCRIPT_FILENAME']) . '/' . DATABASE_NAME . ".sql\">" . DATABASE_NAME . ".sql</code>\n";

    if (!is_file(DATABASE_NAME . '.sql')) {
        echo "(Not found.)\n";
    }

    echo "</dd>\n";
    echo "</dl>\n";
    echo "<p><input type=\"submit\" value=\"import\"></p>\n";
    echo "</fieldset>\n";
    echo "</form>\n";

    echo "</body>\n";
    echo "</html>\n";

    return;
}

/**
 * Output a export page for database.
 *
 */
function db_admin_export()
{
    $resource = db_query(db_sql('table_list'));
    $results  = db_result($resource);

    $_view['tables'] = array();
    foreach ($results as $result) {
        $_view['tables'][] = array_shift($result);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $table    = empty($_POST['table']) ? array() : $_POST['table'];
        $combined = $_POST['format'] === 'combined' ? true : false;

        if ($_POST['means'] === 'download') {
            db_export(null, $table, $combined);
        } else {
            db_export(DATABASE_NAME . '.sql', $table, $combined);

            $_view['message'] = 'Exported.';
        }
    }

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>DB</title>\n";

    style();

    echo "</head>\n";
    echo "<body>\n";
    echo "<h1><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin\">DB</a></h1>\n";

    echo "<h2>Menu</h2>\n";
    echo "<ul>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=sql\">SQL</a></li>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=import\">Import</a></li>\n";
    echo "<li>Export</li>\n";

    if (file_exists(DATABASE_BACKUP_PATH)) {
        echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=backup\">Backup</a></li>\n";
    }

    echo "</ul>\n";

    echo "<h2>Export</h2>\n";

    if (isset($_view['message'])) {
        echo "<ul>\n";
        echo "<li>" . $_view['message'] . "</li>\n";
        echo "</ul>\n";
    } else {
        echo "<ul>\n";
        echo "<li>Export to SQL file.</li>\n";
        echo "</ul>\n";
    }

    echo "<form action=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=export\" method=\"post\" enctype=\"multipart/form-data\">\n";
    echo "<fieldset>\n";
    echo "<legend>export</legend>\n";

    echo "<dl>\n";
    echo "<dt>tables</dt>\n";
    echo "<dd>\n";

    foreach ($_view['tables'] as $table) {
        echo "<label><input type=\"checkbox\" name=\"table[]\" value=\"" . $table . "\" checked=\"checked\"> " . $table . "</label><br>\n";
    }

    echo "</dd>\n";
    echo "<dt>format</dt>\n";
    echo "<dd>\n";
    echo "<label><input type=\"radio\" name=\"format\" value=\"combined\" checked=\"checked\"> combined</label><br>\n";
    echo "<label><input type=\"radio\" name=\"format\" value=\"separated\"> separated</label><br>\n";
    echo "</dd>\n";
    echo "<dt>means</dt>\n";
    echo "<dd>\n";
    echo "<label><input type=\"radio\" name=\"means\" value=\"download\" checked=\"checked\"> download</label><br>\n";
    echo "<label><input type=\"radio\" name=\"means\" value=\"write\"> write to <code title=\"" . dirname($_SERVER['SCRIPT_FILENAME']) . '/' . DATABASE_NAME . ".sql\">" . DATABASE_NAME . ".sql</code></label><br>\n";

    if (is_file(DATABASE_NAME . '.sql')) {
        echo "(Already exists.)\n";
    }

    echo "</dd>\n";
    echo "</dl>\n";
    echo "<p><input type=\"submit\" value=\"export\"></p>\n";
    echo "</fieldset>\n";
    echo "</form>\n";

    echo "</body>\n";
    echo "</html>\n";

    return;
}

/**
 * Output a backup page for database.
 *
 */
function db_admin_backup()
{
    if (auth() === false) {
        return;
    }

    if (!file_exists(DATABASE_BACKUP_PATH)) {
        error('db: ' . DATABASE_BACKUP_PATH . ' is not found');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        db_export(DATABASE_BACKUP_PATH . localdate('YmdHis') . '.sql');

        $_view['message'] = 'completed.';
    }

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>DB</title>\n";

    style();

    echo "</head>\n";
    echo "<body>\n";
    echo "<h1><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin\">DB</a></h1>\n";

    echo "<h2>Menu</h2>\n";
    echo "<ul>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=sql\">SQL</a></li>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=import\">Import</a></li>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=export\">Export</a></li>\n";
    echo "<li>Backup</li>\n";
    echo "</ul>\n";

    echo "<h2>Backup</h2>\n";

    if (isset($_view['message'])) {
        echo "<ul>\n";
        echo "<li>" . $_view['message'] . "</li>\n";
        echo "</ul>\n";
    } else {
        echo "<ul>\n";
        echo "<li>Export to SQL file.</li>\n";
        echo "</ul>\n";
    }

    echo "<form action=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=backup\" method=\"post\">\n";
    echo "<fieldset>\n";
    echo "<legend>backup</legend>\n";
    echo "<p><input type=\"submit\" value=\"backup\"></p>\n";
    echo "</fieldset>\n";
    echo "</form>\n";

    echo "<h2>History</h2>\n";
    echo "<table summary=\"history\">\n";
    echo "<tr>\n";
    echo "<th>file</th>\n";
    echo "<th>datetime</th>\n";
    echo "</tr>\n";

    if ($dh = opendir(DATABASE_BACKUP_PATH)) {
        while (($entry = readdir($dh)) !== false) {
            if (!is_file(DATABASE_BACKUP_PATH . $entry)) {
                continue;
            }

            if ($regexp = regexp_match('^(\d\d\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)\.sql$', $entry)) {
                $datetime = $regexp[1] . '-' . $regexp[2] . '-' . $regexp[3] . ' ' . $regexp[4] . ':' . $regexp[5] . ':' . $regexp[6];
            } else {
                continue;
            }

            echo "<tr>\n";
            echo "<td><span style=\"font-family:monospace;\">" . h($entry, true) . "</span></td>\n";
            echo "<td><span style=\"font-family:monospace;\">" . h($datetime, true) . "</span></td>\n";
            echo "</tr>\n";
        }
        closedir($dh);
    } else {
        if (LOGGING_MESSAGE) {
            logging('message', 'Opendir error: ' . DATABASE_BACKUP_PATH);
        }

        error('Opendir error' . (DEBUG_LEVEL ? ': ' . DATABASE_BACKUP_PATH: ''));
    }

    echo "</table>\n";

    echo "</body>\n";
    echo "</html>\n";

    return;
}

/**
 * Output a sql page for database.
 *
 */
function db_admin_sql()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sql = $_POST['sql'];
    } else {
        $sql = db_sql('table_list');
    }

    list($micro, $second) = explode(' ', microtime());
    $time_start = $micro + $second;

    $resource = db_query($sql);

    list($micro, $second) = explode(' ', microtime());
    $time_end = $micro + $second;

    $_view['time'] = ceil(($time_end - $time_start) * 10000) / 10000;

    $_view['sql'] = $sql;

    if ($sql === db_sql('table_list')) {
        $head = '';
        $body = '';
        $bulk = '';

        $results = db_result($resource);

        $head .= '<tr>';

        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
            $head .= '<th><input type="checkbox" name="" value="" class="bulks" onchange="javascript:checkAll(this.checked);"></th>';
        }

        $head .= '<th>name</th>';

        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
            $head .= '<th>engine</th>';
            $head .= '<th>rows</th>';
            $head .= '<th>collation</th>';
            $head .= '<th>comment</th>';
        }

        $head .= '<th>create</th>';
        $head .= '<th>columns</th>';

        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
            $head .= '<th>alter</th>';
        }
        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql' || DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
            $head .= '<th>truncate</th>';
        }

        $head .= '<th>drop</th>';
        $head .= '<th>insert</th>';
        $head .= '<th>delete</th>';
        $head .= '<th>select</th>';
        $head .= '</tr>';

        foreach ($results as $result) {
            $table = array_shift($result);

            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $table_escaped = '`' . $table . '`';
            } else {
                $table_escaped = '&quot;' . $table . '&quot;';
            }

            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $create     = 'SHOW CREATE TABLE';
                $define     = 'SHOW COLUMNS';
            } elseif (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
                $create     = 'create';
                $define     = 'columns';
            } elseif (DATABASE_TYPE === 'pdo_sqlite' || DATABASE_TYPE === 'pdo_sqlite2' || DATABASE_TYPE === 'sqlite') {
                $create     = 'SELECT sql';
                $define     = 'PRAGMA TABLE_INFO';
            }
            $create_sql = db_sql('table_create', $table);
            $define_sql = db_sql('table_define', $table);

            $create_sql = preg_replace('/"/', '&quot;', $create_sql);
            $define_sql = preg_replace('/"/', '&quot;', $define_sql);

            $define_resource = db_query($define_sql);
            $define_results  = db_result($define_resource);

            $insert_keys   = array();
            $insert_values = array();

            foreach ($define_results as $define_result) {
                if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                    $insert_keys[]   = $define_result['Field'];
                    $insert_values[] = $define_result['Null'] === 'YES' ? 'NULL' : '\\\'\\\'';
                } elseif (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
                    $insert_keys[]   = $define_result['column_name'];
                    $insert_values[] = $define_result['is_nullable'] === 'YES' ? 'NULL' : '\\\'\\\'';
                } elseif (DATABASE_TYPE === 'pdo_sqlite' || DATABASE_TYPE === 'pdo_sqlite2' || DATABASE_TYPE === 'sqlite') {
                    $insert_keys[]   = $define_result['name'];
                    $insert_values[] = $define_result['notnull'] === 0 ? 'NULL' : '\\\'\\\'';
                }
            }

            $body .= '<tr>';

            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $body .= '<td><input type="checkbox" name="table" value="' . $table . '" class="table" onchange="javascript:check(this.checked);"></td>';
            }

            $body .= '<td><span style="font-family:monospace;">' . $table . '</span></td>';

            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $body .= '<td><span style="font-family:monospace;">' . $result['Engine'] . '</span></td>';
                $body .= '<td><span style="font-family:monospace;">' . $result['Rows'] . '</span></td>';
                $body .= '<td><span style="font-family:monospace;">' . $result['Collation'] . '</span></td>';
                $body .= '<td><span style="font-family:monospace;">' . $result['Comment'] . '</span></td>';
            }

            $body .= '<td><a href="javascript:insertSQL(\'' . str_replace('\'', '\\\'', $create_sql) . '\');">' . $create . '</a></td>';
            $body .= '<td><a href="javascript:insertSQL(\'' . str_replace('\'', '\\\'', $define_sql) . '\');">' . $define . '</a></td>';

            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $body .= '<td><a href="javascript:insertSQL(\'ALTER TABLE ' . $table_escaped . ' COMMENT \\\'\\\';\');">ALTER TABLE</a></td>';
            }
            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql' || DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
                $body .= '<td><a href="javascript:insertSQL(\'TRUNCATE TABLE ' . $table_escaped . ';\');">TRUNCATE TABLE</a></td>';
            }

            $body .= '<td><a href="javascript:insertSQL(\'DROP TABLE ' . $table_escaped . ';\');">DROP TABLE</a></td>';
            $body .= '<td><a href="javascript:insertSQL(\'INSERT INTO ' . $table_escaped . '(' . implode(',', $insert_keys) . ') VALUES(' . implode(',', $insert_values) . ');\');">INSERT</a></td>';
            $body .= '<td><a href="javascript:insertSQL(\'DELETE FROM ' . $table_escaped . ';\');">DELETE</a></td>';
            $body .= '<td><a href="javascript:insertSQL(\'SELECT * FROM ' . $table_escaped . ' LIMIT 100;\');">SELECT</a></td>';
            $body .= '</tr>';
        }

        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
            $bulk .= '<form action="" method="get">';
            $bulk .= '<fieldset style="padding: 0; border: 0;">';
            $bulk .= '<legend style="display: none;">bulk</legend>';
            $bulk .= '<p>';
            $bulk .= '<select name="" class="bulk" onchange="javascript:bulk(this.value);">';
            $bulk .= '<option value="">With selected:</option>';
            $bulk .= '<option value="truncate">TRUNCATE TABLE</option>';
            $bulk .= '<option value="drop">DROP TABLE</option>';
            $bulk .= '</select>';
            $bulk .= '</p>';
            $bulk .= '</fieldset>';
            $bulk .= '</form>';
        }

        $_view['result'] = '<table summary="result">' . $head . $body . '</table>' . $bulk;
        $_view['count']  = db_count($resource);
    } elseif (regexp_match('^(SELECT|SHOW|EXPLAIN|DESC|PRAGMA)', $sql)) {
        $head = '';
        $body = '';
        $flag = false;

        if ($regexp = regexp_match('^SELECT \* FROM [`"]([_a-zA-Z0-9\-]+)[`"]', $sql)) {
            $table = $regexp[1];
            $link  = true;
        } elseif ($regexp = regexp_match('^' . db_sql('table_define', '([_a-zA-Z0-9\-]+)'), $sql)) {
            $table = $regexp[1];
            $link  = false;
        } else {
            $table = null;
            $link  = false;
        }

        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
            $table_escaped = '`' . $table . '`';
        } else {
            $table_escaped = '&quot;' . $table . '&quot;';
        }

        $results = db_result($resource);

        foreach ($results as $result) {
            $first_key   = null;
            $first_value = null;

            $body .= '<tr>';

            foreach ($result as $key => $value) {
                if ($first_key === null) {
                    $first_key   = $key;
                    $first_value = $value;
                }

                if (is_string($key)) {
                    if ($value === null) {
                        $value_sql  = 'NULL';
                        $value_html = '<em>NULL</em>';
                    } elseif ($value === true && (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql')) {
                        $value_sql  = 'TRUE';
                        $value_html = '<em>TRUE</em>';
                    } elseif ($value === false && (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql')) {
                        $value_sql  = 'FALSE';
                        $value_html = '<em>FALSE</em>';
                    } else {
                        $value_sql = str_replace('\\', '\\\\\\\\', $value);
                        $value_sql = str_replace("\n", '\n', $value_sql);
                        $value_sql = str_replace('"', '&quot;', $value_sql);

                        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                            $value_sql = str_replace('\'', '\\\\\\\'', $value_sql);
                        } else {
                            $value_sql = str_replace('\'', '\\\'\\\'', $value_sql);
                        }

                        $value_sql  = '\\\'' . $value_sql . '\\\'';
                        $value_html = h($value, true);
                    }

                    if ($link === false) {
                        $value = $value_html;
                    } else {
                        $value = '<a href="javascript:insertSQL(\'UPDATE ' . $table_escaped . ' SET ' . $key . ' = ' . $value_sql . ' WHERE ' . $first_key . ' = \\\'' . $first_value . '\\\';\');">' . truncate($value_html, 100) . '</a>';
                    }

                    $body .= '<td><span style="font-family:monospace;">' . $value . '</span></td>';

                    if ($flag === false) {
                        $head .= '<th>' . h($key, true) . '</th>';
                    }
                }
            }

            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                if (regexp_match('^' . db_sql('table_define', '([_a-zA-Z0-9\-]+)'), $sql)) {
                    $add_value    = '<a href="javascript:insertSQL(\'ALTER TABLE ' . $table_escaped . ' ADD field INT(1) NOT NULL COMMENT \\\'\\\' AFTER ' . $result['Field'] . ';\');">ADD</a>';
                    $change_value = '<a href="javascript:insertSQL(\'ALTER TABLE ' . $table_escaped . ' CHANGE ' . $result['Field'] . ' ' . $result['Field'] . ' INT(1) NOT NULL COMMENT \\\'\\\';\');">CHANGE</a>';
                    $drop_value   = '<a href="javascript:insertSQL(\'ALTER TABLE ' . $table_escaped . ' DROP ' . $result['Field'] . ';\');">DROP</a>';

                    $body .= '<td><span style="font-family:monospace;">' . $add_value . ' ' . $change_value . ' ' . $drop_value . '</span></td>';

                    if ($flag === false) {
                        $head .= '<th>alter</th>';
                    }
                }
            }

            $body .= '</tr>';

            $flag = true;
        }

        $_view['result'] = '<table summary="result"><tr>' . $head . '</tr>' . $body . '</table>';
        $_view['count']  = db_count($resource);
    } else {
        $_view['result'] = '<p>OK</p>';
        $_view['count']  = db_affected_count($resource);
    }

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>DB</title>\n";

    style();

    echo "<script>\n";
    echo "function insertSQL(sql)\n";
    echo "{\n";
    echo "    document.getElementById('exec_form').sql.value = sql;\n";
    echo "}\n";

    if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
        echo "function checkAll(checked)\n";
        echo "{\n";
        echo "    var tables = document.getElementsByClassName('table');\n";
        echo "    var bulks  = document.getElementsByClassName('bulks');\n";
        echo "    var i;\n";
        echo "    for (i = 0; i < tables.length; i++) {\n";
        echo "        tables[i].checked = checked;\n";
        echo "    }\n";
        echo "    for (i = 0; i < bulks.length; i++) {\n";
        echo "        bulks[i].checked = checked;\n";
        echo "    }\n";
        echo "}\n";
        echo "function check(checked)\n";
        echo "{\n";
        echo "    var tables = document.getElementsByClassName('table');\n";
        echo "    var bulks  = document.getElementsByClassName('bulks');\n";
        echo "    var status = checked;\n";
        echo "    var all    = true;\n";
        echo "    var i;\n";
        echo "    for (i = 0; i < tables.length; i++) {\n";
        echo "        if (tables[i].checked == false) {\n";
        echo "            all = false;\n";
        echo "        }\n";
        echo "    }\n";
        echo "    for (i = 0; i < bulks.length; i++) {\n";
        echo "        if (all == true) {\n";
        echo "            bulks[i].checked = true;\n";
        echo "        } else {\n";
        echo "            bulks[i].checked = false;\n";
        echo "        }\n";
        echo "    }\n";
        echo "}\n";
        echo "function bulk(selected)\n";
        echo "{\n";
        echo "    var tables = document.getElementsByClassName('table');\n";
        echo "    var bulk   = document.getElementsByClassName('bulk');\n";
        echo "    var sql    = '';\n";
        echo "    var exec   = '';\n";
        echo "    if (selected == 'truncate') {\n";
        echo "        exec = 'TRUNCATE TABLE';\n";
        echo "    } else if (selected == 'drop') {\n";
        echo "        exec = 'DROP TABLE';\n";
        echo "    }\n";
        echo "    var i;\n";
        echo "    for (i = 0; i < tables.length; i++) {\n";
        echo "        if (tables[i].checked == true) {\n";
        echo "            sql += exec + ' ' + tables[i].value + ';\\n';\n";
        echo "        }\n";
        echo "    }\n";
        echo "    for (i = 0; i < bulk.length; i++) {\n";
        echo "        bulk[i].selectedIndex = 0;\n";
        echo "    }\n";
        echo "    insertSQL(sql);\n";
        echo "}\n";
    }

    echo "</script>\n";
    echo "</head>\n";
    echo "<body>\n";
    echo "<h1><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin\">DB</a></h1>\n";

    echo "<h2>Menu</h2>\n";
    echo "<ul>\n";
    echo "<li>SQL</li>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=import\">Import</a></li>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=export\">Export</a></li>\n";

    if (file_exists(DATABASE_BACKUP_PATH)) {
        echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin&amp;_work=backup\">Backup</a></li>\n";
    }

    echo "</ul>\n";

    echo "<h2>SQL</h2>\n";
    echo "<form action=\"" . t(MAIN_FILE, true) . "/?_mode=db_admin\" method=\"post\" id=\"exec_form\">\n";
    echo "<fieldset>\n";
    echo "<legend>execute</legend>\n";
    echo "<dl>\n";
    echo "<dt>SQL</dt>\n";
    echo "<dd><textarea name=\"sql\" cols=\"50\" rows=\"5\">" . t($_view['sql'], true) . "</textarea></dd>\n";
    echo "</dl>\n";
    echo "<p><input type=\"submit\" value=\"execute\"></p>\n";
    echo "</fieldset>\n";
    echo "</form>\n";

    if ($_view['result']) {
        echo "<h2>Result</h2>\n";
        echo $_view['result'];
    }

    echo "<pre><code>Rows: " . $_view['count'] . " rows.\n";
    echo "Time: " . $_view['time'] . " sec.</code></pre>\n";

    echo "</body>\n";
    echo "</html>\n";

    return;
}

/**
 * Output a migrate page for database.
 *
 */
function db_migrate()
{
    if (function_exists('my_db_migrate')) {
        eval('my_db_migrate();');

        return;
    }

    if (auth() === false) {
        return;
    }

    if (!file_exists(DATABASE_MIGRATE_PATH)) {
        error('db: ' . DATABASE_MIGRATE_PATH . ' is not found');
    }

    if (php_sapi_name() === 'cli') {
        $em_begin = '';
        $em_end   = '';
    } else {
        $em_begin = '<em>';
        $em_end   = '</em>';
    }

    // initialize
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

    if ($flag === false) {
        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
            db_query('
                CREATE TABLE ' . DATABASE_PREFIX . 'levis_migrations(
                    id          INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT \'id\',
                    version     VARCHAR(14)  NOT NULL UNIQUE         COMMENT \'version\',
                    description VARCHAR(255) NOT NULL                COMMENT \'description\',
                    status      VARCHAR(80)  NOT NULL                COMMENT \'status\',
                    installed   DATETIME                             COMMENT \'installed\',
                    PRIMARY KEY(id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT \'migration\';
            ');
        } elseif (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
            db_query('
                CREATE TABLE ' . DATABASE_PREFIX . 'levis_migrations(
                    id          SERIAL       NOT NULL,
                    version     VARCHAR(14)  NOT NULL UNIQUE,
                    description VARCHAR(255) NOT NULL,
                    status      VARCHAR(80)  NOT NULL,
                    installed   TIMESTAMP,
                    PRIMARY KEY(id)
                );
            ');
        } else {
            db_query('
                CREATE TABLE ' . DATABASE_PREFIX . 'levis_migrations(
                    id          INTEGER,
                    version     VARCHAR  NOT NULL UNIQUE,
                    description VARCHAR  NOT NULL,
                    status      VARCHAR  NOT NULL,
                    installed   DATETIME,
                    PRIMARY KEY(id)
                );
            ');
        }
    }

    // succeeded
    $resource = db_query('SELECT * FROM ' . DATABASE_PREFIX . 'levis_migrations WHERE status = ' . db_escape('success') . ';');
    $results  = db_result($resource);

    $succeeded = array();
    foreach ($results as $result) {
        $succeeded[$result['version']] = true;
    }

    // target
    $targets = array();
    if ($dh = opendir(DATABASE_MIGRATE_PATH)) {
        while (($entry = readdir($dh)) !== false) {
            if (!is_file(DATABASE_MIGRATE_PATH  . $entry)) {
                continue;
            }

            if ($regexp = regexp_match('^([0-9\-]{14})-[_a-zA-Z0-9\-]+\.sql$', $entry)) {
                $version = $regexp[1];
            } else {
                continue;
            }

            if (isset($succeeded[$version])) {
                continue;
            }

            $targets[] = $entry;
        }
        closedir($dh);
    } else {
        if (LOGGING_MESSAGE) {
            logging('message', 'db: Opendir error: ' . db_error());
        }

        error('db: Opendir error' . (DEBUG_LEVEL ? ': ' . db_error(): ''));
    }

    sort($targets, SORT_STRING);

    // limit
    if (isset($_GET['limit'])) {
        $limit = $_GET['limit'];

        array_splice($targets, $limit);
    } else {
        $limit = null;
    }

    // backup
    if (!empty($targets) && file_exists(DATABASE_BACKUP_PATH)) {
        db_export(DATABASE_BACKUP_PATH . localdate('YmdHis') . '.sql');
    }

    // migrate
    $resource = db_query('DELETE FROM ' . DATABASE_PREFIX . 'levis_migrations WHERE status = ' . db_escape('pending') . ';');
    if (!$resource) {
        if (LOGGING_MESSAGE) {
            logging('message', 'db: Query error: ' . db_error());
        }

        error('db: Query error' . (DEBUG_LEVEL ? ': ' . db_error(): ''));
    }

    $migrate = '';
    foreach ($targets as $target) {
        if ($regexp = regexp_match('^([0-9\-]{14})-([_a-zA-Z0-9\-]+)\.sql$', $target)) {
            $version     = $regexp[1];
            $description = $regexp[2];
        } else {
            continue;
        }

        $resource = db_query('INSERT INTO ' . DATABASE_PREFIX . 'levis_migrations(version, description, status) VALUES(' . db_escape($version) . ', ' . db_escape($description) . ', ' . db_escape('pending') . ');');
        if (!$resource) {
            if (LOGGING_MESSAGE) {
                logging('message', 'db: Query error: ' . db_error());
            }

            error('db: Query error' . (DEBUG_LEVEL ? ': ' . db_error(): ''));
        }

        $migrations = array();
        $error      = false;
        $i          = 0;
        if ($fp = fopen(DATABASE_MIGRATE_PATH . $target, 'r')) {
            $sql  = '';
            $flag = true;

            while ($line = fgets($fp)) {
                $line = str_replace("\r\n", "\n", $line);
                $line = str_replace("\r", "\n", $line);

                if ((substr_count($line, '\'') - substr_count($line, '\\\'')) % 2 !== 0) {
                    $flag = !$flag;
                }

                $sql .= $line;

                if (preg_match('/;$/', trim($line)) && $flag) {
                    $i++;

                    $migrations[$target][$i]['sql'] = trim($sql);

                    list($micro, $second) = explode(' ', microtime());
                    $time_start = $micro + $second;

                    $resource = db_query($sql, false, false);

                    list($micro, $second) = explode(' ', microtime());
                    $time_end = $micro + $second;

                    $migrations[$target][$i]['time'] = ceil(($time_end - $time_start) * 10000) / 10000;

                    if ($resource) {
                        $migrations[$target][$i]['result']   = 'OK';
                        $migrations[$target][$i]['affected'] = db_affected_count($resource);
                    } else {
                        $migrations[$target][$i]['result']   = 'NG';
                        $migrations[$target][$i]['affected'] = 0;

                        $error = true;


                        $migrate .= $em_begin . $target . $em_end . "\n";
                        foreach ($migrations[$target] as $migration) {
                            $migration['sql'] = regexp_replace("(\r|\n)", '', $migration['sql']);
                            $migration['sql'] = regexp_replace("\s+", ' ', $migration['sql']);

                            $migrate .= truncate($migration['sql'], 80, '') . ' ... ' . $em_begin . $migration['result'] . $em_end;
                            if ($migration['result'] == 'OK') {
                                $migrate .= ' (';
                                $migrate .= $migration['affected'] ? $migration['affected'] . ' rows affected. ' : '';
                                $migrate .= $migration['time'] . ' sec.)';
                            }
                            $migrate .= "\n";
                        }
                        $migrate .= "\n";
                        $migrate .= db_error() . "\n";
                        $migrate .= "\n";

                        break;
                    }

                    $sql = '';
                }
            }
            fclose($fp);

            if ($error === true) {
                break;
            }
        } else {
            if (LOGGING_MESSAGE) {
                logging('message', 'db: File can\'t read: ' . db_error());
            }

            error('db: File can\'t read' . (DEBUG_LEVEL ? ': ' . db_error(): ''));
        }

        if ($error === false) {
            $resource = db_query('UPDATE ' . DATABASE_PREFIX . 'levis_migrations SET status = ' . db_escape('success') . ', installed = ' . db_escape(localdate('Y-m-d H:i:s')) . ' WHERE version = \'' . $version . '\';');
            if (!$resource) {
                if (LOGGING_MESSAGE) {
                    logging('message', 'db: Query error: ' . db_error());
                }

                error('db: Query error' . (DEBUG_LEVEL ? ': ' . db_error(): ''));
            }

            $migrate .= $em_begin . $target . $em_end . "\n";
            foreach ($migrations[$target] as $migration) {
                $migration['sql'] = regexp_replace("\s+", ' ', $migration['sql']);
                $migration['sql'] = regexp_replace("(\r|\n)", '', $migration['sql']);

                $migrate .= truncate($migration['sql'], 80, '') . ' ... ' . $em_begin . $migration['result'] . $em_end;
                $migrate .= ' (';
                $migrate .= $migration['affected'] ? $migration['affected'] . ' rows affected. ' : '';
                $migrate .= $migration['time'] . ' sec.)';
                $migrate .= "\n";
            }
            $migrate .= "\n";
        }
    }

    $resource = db_query('SELECT version FROM ' . DATABASE_PREFIX . 'levis_migrations WHERE status = ' . db_escape('success') . ' ORDER BY version DESC LIMIT 1;');
    $results  = db_result($resource);

    if (empty($results)) {
        $version = '-';
    } else {
        $version = $results[0]['version'];
    }

    if (empty($migrate)) {
        $migrate .= $em_begin . "Nothing to migrate." . $em_end . "\n";
        $migrate .= "\n";
    }

    $migrate .= "Database: " . DATABASE_NAME . "\n";
    $migrate .= "Version: " . $version . "\n";

    if (php_sapi_name() === 'cli') {
        echo "levis: PHP Framework\n";
        echo "DB Migrate\n";
        echo "\n";
        echo $migrate;
        exit;
    }

    // history
    $resource   = db_query('SELECT * FROM ' . DATABASE_PREFIX . 'levis_migrations ORDER BY version;');
    $migrations = db_result($resource);

    // result
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>DB Migrate</title>\n";

    style();

    echo "</head>\n";
    echo "<body>\n";

    echo "<h1>DB Migrate</h1>\n";
    echo "<pre><code>" . e($migrate, true) . "</code></pre>\n";

    if ($limit) {
        echo "<p><a href=\"" . t(MAIN_FILE, true) . "/?_mode=db_migrate&amp;limit=1\">reload</a></p>\n";
    }

    echo "<table summary=\"migrations\">\n";
    echo "<tr>\n";
    echo "<th>version</th>\n";
    echo "<th>description</th>\n";
    echo "<th>status</th>\n";
    echo "<th>installed</th>\n";
    echo "</tr>\n";

    foreach ($migrations as $migration) {
        echo "<tr>\n";
        echo "<td><span style=\"font-family:monospace;\">" . h($migration['version'], true) . "</span></td>\n";
        echo "<td><span style=\"font-family:monospace;\">" . h($migration['description'], true) . "</span></td>\n";
        echo "<td><span style=\"font-family:monospace;\">" . h($migration['status'], true) . "</span></td>\n";
        echo "<td><span style=\"font-family:monospace;\">" . h($migration['installed'], true) . "</span></td>\n";
        echo "</tr>\n";
    }

    echo "</table>\n";
    echo "</body>\n";
    echo "</html>\n";

    exit;
}

/**
 * Output a scaffold page for database.
 *
 */
function db_scaffold()
{
    if (function_exists('my_db_scaffold')) {
        eval('my_db_scaffold();');

        return;
    }

    if (auth() === false) {
        return;
    }

    if (!file_exists(DATABASE_SCAFFOLD_PATH)) {
        error('db: ' . DATABASE_SCAFFOLD_PATH . ' is not found');
    }

    // initialize
    $app = 'app/';

    $models      = $app . 'models/';
    $views       = $app . 'views/';
    $controllers = $app . 'controllers/';

    $header_file = $views . 'header.php';
    $footer_file = $views . 'footer.php';

    $before_file = $app . 'controllers/before.php';
    $config_file = $app . 'config.php';

    $exclude_prefix = 'levis_';
    $primary_key    = 'id';

    $test = 'test/';

    // table
    $resource = db_query(db_sql('table_list'));
    $results  = db_result($resource);

    if (count($results) === 0) {
        error('db: Table not found');
    }

    $scaffold = '';
    $create   = 0;

    foreach ($results as $result) {
        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
            $table_comment = $result['Comment'];
        } else {
            $table_comment = null;
        }

        $table = array_shift($result);

        if (regexp_match('^' . DATABASE_PREFIX . $exclude_prefix, $table)) {
            continue;
        }

        // initialize
        $primary_flag = false;

        $model_file             = $models . $table . '.php';
        $view_index_file        = $views . $table . '/' . MAIN_DEFAULT_WORK . '.php';
        $view_post_file         = $views . $table . '/post.php';
        $controller_index_file  = $controllers . $table . '/' . MAIN_DEFAULT_WORK . '.php';
        $controller_post_file   = $controllers . $table . '/post.php';
        $controller_delete_file = $controllers . $table . '/delete.php';
        $test_model_file        = $test . 'model_' . $table . '.php';
        $test_view_file         = $test . 'view_' . $table . '.php';
        $test_controller_file   = $test . 'controller_' . $table . '.php';

        $define_sql = db_sql('table_define', $table);

        $define_resource = db_query($define_sql);
        $define_results  = db_result($define_resource);

        $model_validate = '';
        $model_default  = '';

        $view_head = '';
        $view_data = '';

        $view_form = '';

        $controller_validate = '';
        $controller_insert   = '';
        $controller_update   = '';

        $test_data = '';

        // indent
        $max_length = 0;
        foreach ($define_results as $define_result) {
            $field = '';
            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $field = $define_result['Field'];
            } elseif (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
                $field = $define_result['column_name'];
            } elseif (DATABASE_TYPE === 'pdo_sqlite' || DATABASE_TYPE === 'pdo_sqlite2' || DATABASE_TYPE === 'sqlite') {
                $field = $define_result['name'];
            }

            $max_length = strlen($field) > $max_length ? strlen($field) : $max_length;
        }

        foreach ($define_results as $define_result) {
            // define
            $field = '';
            $null  = false;
            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $field   = $define_result['Field'];
                $type    = $define_result['Type'];
                $null    = $define_result['Null'] === 'YES' ? true : false;
                $comment = $define_result['Comment'];
            } elseif (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
                $field   = $define_result['column_name'];
                $type    = $define_result['data_type'];
                $null    = $define_result['is_nullable'] === 'YES' ? true : false;
                $comment = null;
            } elseif (DATABASE_TYPE === 'pdo_sqlite' || DATABASE_TYPE === 'pdo_sqlite2' || DATABASE_TYPE === 'sqlite') {
                $field   = $define_result['name'];
                $type    = $define_result['type'];
                $null    = $define_result['notnull'] === 0 ? true : false;
                $comment = null;
            }

            if ($field === $primary_key) {
                $primary_flag = true;
            }

            // model
            $model_validate .= '    // ' . ($comment ? $comment : $field) . "\n";
            $model_validate .= '    if (isset($queries[\'' . $field . '\'])) {' . "\n";

            if ($field === $primary_key || !$null) {
                $model_validate .= '        if ($queries[\'' . $field . '\'] === \'\') {' . "\n";
                $model_validate .= '            $messages[] = \'The ' . ($comment ? $comment : $field) . ' is required.\';' . "\n";
                $model_validate .= '        }' . "\n";
            }

            $model_validate .= '    }' . "\n";
            $model_validate .= "\n";

            $space = str_repeat(' ', $max_length - strlen($field));

            if ($field === $primary_key) {
                $model_default .= '        \'' . $field . '\' ' . $space . '=> null,' . "\n";
            } elseif ($null) {
                $model_default .= '        \'' . $field . '\' ' . $space . '=> null,' . "\n";
            } elseif (regexp_match('(BLOB|TEXT|CHAR)', $type)) {
                $model_default .= '        \'' . $field . '\' ' . $space . '=> \'\',' . "\n";
            } else {
                $model_default .= '        \'' . $field . '\' ' . $space . '=> 0,' . "\n";
            }

            // view
            $view_head .= '                <th>' . ($comment ? $comment : $field) . '</th>' . "\n";

            if ($field === $primary_key) {
                $view_data .= '                <td><a href="<?php t(MAIN_FILE) ?>/' . $table . '/post?' . $primary_key . '=<?php t($data[\'' . $primary_key . '\']) ?>"><?php h($data[\'' . $field . '\']) ?></a></td>' . "\n";
            } else {
                $view_data .= '                <td><?php h($data[\'' . $field . '\']) ?></td>' . "\n";
            }

            if (regexp_match('(BLOB|TEXT)', $type)) {
                $input = '<textarea name="' . $field . '" rows="10" cols="50"><?php t($_view[\'data\'][\'' . $field . '\']) ?></textarea>';
            } elseif (regexp_match('(CHAR)', $type)) {
                $input = '<input type="text" name="' . $field . '" size="30" value="<?php t($_view[\'data\'][\'' . $field . '\']) ?>">';
            } else {
                $input = '<input type="text" name="' . $field . '" size="10" value="<?php t($_view[\'data\'][\'' . $field . '\']) ?>">';
            }

            if ($field === $primary_key) {
                $view_form .= '                    <dt>' . ($comment ? $comment : $field) . ($null ? '' : '(required)') . '</dt>' . "\n";
                $view_form .= '                        <dd>' . "\n";
                $view_form .= '                            <?php if (empty($_GET[\'' . $primary_key . '\'])) : ?>' . "\n";
                $view_form .= '                            ' . $input . "\n";
                $view_form .= '                            <?php else : ?>' . "\n";
                $view_form .= '                            <em><?php h($_view[\'data\'][\'' . $field . '\']) ?></em><input type="hidden" name="' . $field . '" value="<?php t($_view[\'data\'][\'' . $field . '\']) ?>">' . "\n";
                $view_form .= '                            <?php endif ?>' . "\n";
                $view_form .= '                        </dd>' . "\n";
            } else {
                $view_form .= '                    <dt>' . ($comment ? $comment : $field) . ($null ? '' : '(required)') . '</dt>' . "\n";
                $view_form .= '                        <dd>' . $input . '</dd>' . "\n";
            }

            // controller
            $controller_validate .= '        \'' . $field . '\' ' . $space . '=> $_POST[\'' . $field . '\'],' . "\n";
            $controller_insert   .= '                \'' . $field . '\' ' . $space . '=> $_POST[\'' . $field . '\'],' . "\n";

            if ($field !== $primary_key) {
                $controller_update .= '                \'' . $field . '\' ' . $space . '=> $_POST[\'' . $field . '\'],' . "\n";
            }

            // test
            if ($field === $primary_key) {
                $test_data .= '            \'' . $field . '\' ' . $space . '=> [N],' . "\n";
            } elseif ($null) {
                $test_data .= '            \'' . $field . '\' ' . $space . '=> null,' . "\n";
            } elseif (regexp_match('(BLOB|TEXT|CHAR)', $type)) {
                $test_data .= '            \'' . $field . '\' ' . $space . '=> \'TEST[N]\',' . "\n";
            } else {
                $test_data .= '            \'' . $field . '\' ' . $space . '=> [N],' . "\n";
            }
        }

        // heading
        $scaffold .= '[' . $table . ']' . "\n";

        // model
        $buffer  = '<?php' . "\n";
        $buffer .= "\n";

        $buffer .= '/**' . "\n";
        $buffer .= ' * Validate for ' . ($table_comment ? $table_comment : $table) . "\n";
        $buffer .= ' *' . "\n";
        $buffer .= ' * @param array $queries' . "\n";
        $buffer .= ' *' . "\n";
        $buffer .= ' * @return array' . "\n";
        $buffer .= ' */' . "\n";
        $buffer .= 'function validate_' . $table . '($queries)' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    $messages = array();' . "\n";
        $buffer .= '' . "\n";
        $buffer .= $model_validate;
        $buffer .= '    return $messages;' . "\n";
        $buffer .= '}' . "\n";
        $buffer .= "\n";

        $buffer .= '/**' . "\n";
        $buffer .= ' * Default for ' . ($table_comment ? $table_comment : $table) . "\n";
        $buffer .= ' *' . "\n";
        $buffer .= ' * @return array' . "\n";
        $buffer .= ' */' . "\n";
        $buffer .= 'function default_' . $table . '()' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    return array(' . "\n";
        $buffer .= $model_default;
        $buffer .= '    );' . "\n";
        $buffer .= '}' . "\n";

        $scaffold .= db_scaffold_output($model_file, $buffer);
        $create++;

        // view
        $buffer  = '<?php import(\'app/views/header.php\') ?>' . "\n";
        $buffer .= "\n";
        $buffer .= '        <h2>' . ($table_comment ? $table_comment : $table) . '</h2>' . "\n";
        $buffer .= '        <ul>' . "\n";
        $buffer .= '            <li><a href="<?php t(MAIN_FILE) ?>/' . $table . '/post">post</a></li>' . "\n";
        $buffer .= '        </ul>' . "\n";
        $buffer .= '        <table summary="' . ($table_comment ? $table_comment : $table) . '">' . "\n";
        $buffer .= '            <tr>' . "\n";
        $buffer .= $view_head;
        $buffer .= '            </tr>' . "\n";
        $buffer .= '            <?php foreach ($_view[\'' . $table . '\'] as $data) : ?>' . "\n";
        $buffer .= '            <tr>' . "\n";
        $buffer .= $view_data;
        $buffer .= '            </tr>' . "\n";
        $buffer .= '            <?php endforeach ?>' . "\n";
        $buffer .= '        </table>' . "\n";
        $buffer .= "\n";
        $buffer .= '<?php import(\'app/views/footer.php\') ?>' . "\n";

        $scaffold .= db_scaffold_output($view_index_file, $buffer);
        $create++;

        $buffer  = '<?php import(\'app/views/header.php\') ?>' . "\n";
        $buffer .= "\n";
        $buffer .= '        <h2>' . ($table_comment ? $table_comment : $table) . '</h2>' . "\n";
        $buffer .= '        <form action="<?php t(MAIN_FILE) ?>/' . $table . '/post' . ($primary_flag ? '<?php $_view[\'data\'][\'' . $primary_key . '\'] ? t(\'?' . $primary_key . '=\' . $_view[\'data\'][\'' . $primary_key . '\']) : \'\' ?>' : '') . '" method="post">' . "\n";
        $buffer .= '            <fieldset>' . "\n";
        $buffer .= '                <legend>' . ($table_comment ? $table_comment : $table) . '</legend>' . "\n";
        $buffer .= '                <dl>' . "\n";
        $buffer .= $view_form;
        $buffer .= '                </dl>' . "\n";
        $buffer .= '                <p><input type="submit" value="post"></p>' . "\n";
        $buffer .= '            </fieldset>' . "\n";
        $buffer .= '        </form>' . "\n";

        if ($primary_flag) {
            $buffer .= '        <?php if (!empty($_GET[\'' . $primary_key . '\'])) : ?>' . "\n";
            $buffer .= '        <h2>delete</h2>' . "\n";
            $buffer .= '        <form action="<?php t(MAIN_FILE) ?>/' . $table . '/delete?' . $primary_key . '=\' . t($_view[\'data\'][\'' . $primary_key . '\']) ?>" method="post">' . "\n";
            $buffer .= '            <fieldset>' . "\n";
            $buffer .= '                <legend>' . ($table_comment ? $table_comment : $table) . '</legend>' . "\n";
            $buffer .= '                <input type="hidden" name="' . $primary_key . '" value="<?php t($_view[\'data\'][\'' . $primary_key . '\']) ?>"></dd>' . "\n";
            $buffer .= '                <p><input type="submit" value="delete"></p>' . "\n";
            $buffer .= '            </fieldset>' . "\n";
            $buffer .= '        </form>' . "\n";
            $buffer .= '        <?php endif ?>' . "\n";
        }

        $buffer .= "\n";
        $buffer .= '<?php import(\'app/views/footer.php\') ?>' . "\n";

        $scaffold .= db_scaffold_output($view_post_file, $buffer);
        $create++;

        // controller
        $buffer  = '<?php' . "\n";
        $buffer .= "\n";
        $buffer .= '$_view[\'' . $table . '\'] = select_' . $table . '(array(' . "\n";
        $buffer .= '    \'limit\' => array(' . "\n";
        $buffer .= '        \':limit\',' . "\n";
        $buffer .= '        array(' . "\n";
        $buffer .= '            \'limit\' => $GLOBALS[\'config\'][\'limits\'][\'' . $table . '\'],' . "\n";
        $buffer .= '        ),' . "\n";
        $buffer .= '    ),' . "\n";
        $buffer .= '));' . "\n";

        $scaffold .= db_scaffold_output($controller_index_file, $buffer);
        $create++;

        $buffer  = '<?php' . "\n";
        $buffer .= "\n";
        $buffer .= 'if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {' . "\n";
        $buffer .= '    $warnings = validate_' . $table . '(normalize_' . $table . '(array(' . "\n";
        $buffer .= $controller_validate;
        $buffer .= '    )));' . "\n";
        $buffer .= '    if (!empty($warnings)) {' . "\n";
        $buffer .= '        warning($warnings);' . "\n";
        $buffer .= '    }' . "\n";
        $buffer .= '    if (isset($_GET[\'' . $primary_key . '\'])) {' . "\n";

        if ($primary_flag) {
            $buffer .= '        $resource = update_' . $table . '(array(' . "\n";
            $buffer .= '            \'set\' => array(' . "\n";
            $buffer .= $controller_update;
            $buffer .= '            ),' . "\n";
            $buffer .= '            \'where\' => array(' . "\n";
            $buffer .= '                \'' . $primary_key . ' = :' . $primary_key . '\',' . "\n";
            $buffer .= '                array(' . "\n";
            $buffer .= '                    \'' . $primary_key . '\' => $_POST[\'' . $primary_key . '\'],' . "\n";
            $buffer .= '                ),' . "\n";
            $buffer .= '            ),' . "\n";
            $buffer .= '        ));' . "\n";
            $buffer .= '        if (!$resource) {' . "\n";
            $buffer .= '            error(\'Update error.\');' . "\n";
            $buffer .= '        }' . "\n";
        }

        $buffer .= '    } else {' . "\n";
        $buffer .= '        $resource = insert_' . $table . '(array(' . "\n";
        $buffer .= '            \'values\' => array(' . "\n";
        $buffer .= $controller_insert;
        $buffer .= '            ),' . "\n";
        $buffer .= '        ));' . "\n";
        $buffer .= '        if (!$resource) {' . "\n";
        $buffer .= '            error(\'Insert error.\');' . "\n";
        $buffer .= '        }' . "\n";
        $buffer .= '    }' . "\n";
        $buffer .= "\n";
        $buffer .= '    redirect(\'/' . $table . '/' . MAIN_DEFAULT_WORK . '\');' . "\n";
        $buffer .= '} else {' . "\n";
        $buffer .= '    if (isset($_GET[\'' . $primary_key . '\'])) {' . "\n";

        if ($primary_flag) {
            $buffer .= '        $' . $table . ' = select_' . $table . '(array(' . "\n";
            $buffer .= '            \'where\' => array(' . "\n";
            $buffer .= '                \'' . $primary_key . ' = :' . $primary_key . '\',' . "\n";
            $buffer .= '                array(' . "\n";
            $buffer .= '                    \'' . $primary_key . '\' => $_GET[\'' . $primary_key . '\'],' . "\n";
            $buffer .= '                ),' . "\n";
            $buffer .= '            ),' . "\n";
            $buffer .= '        ));' . "\n";
            $buffer .= '        if (empty($' . $table . ')) {' . "\n";
            $buffer .= '            error(\'Data not found.\');' . "\n";
            $buffer .= '        } else {' . "\n";
            $buffer .= '            $_view[\'data\'] = $' . $table . '[0];' . "\n";
            $buffer .= '        }' . "\n";
        }

        $buffer .= '    } else {' . "\n";
        $buffer .= '        $_view[\'data\'] = default_' . $table . '();' . "\n";
        $buffer .= '    }' . "\n";
        $buffer .= '}' . "\n";

        $scaffold .= db_scaffold_output($controller_post_file, $buffer);
        $create++;

        if ($primary_flag) {
            $buffer  = '<?php' . "\n";
            $buffer .= "\n";
            $buffer .= 'if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\') {' . "\n";
            $buffer .= '    $resource = delete_' . $table . '(array(' . "\n";
            $buffer .= '        \'where\' => array(' . "\n";
            $buffer .= '            \'' . $primary_key . ' = :' . $primary_key . '\',' . "\n";
            $buffer .= '            array(' . "\n";
            $buffer .= '                \'' . $primary_key . '\' => $_POST[\'' . $primary_key . '\'],' . "\n";
            $buffer .= '            ),' . "\n";
            $buffer .= '        ),' . "\n";
            $buffer .= '    ));' . "\n";
            $buffer .= '    if (!$resource) {' . "\n";
            $buffer .= '        error(\'Delete error.\');' . "\n";
            $buffer .= '    }' . "\n";
            $buffer .= '} else {' . "\n";
            $buffer .= '    error(\'Method error.\');' . "\n";
            $buffer .= '}' . "\n";
            $buffer .= "\n";
            $buffer .= 'redirect(\'/' . $table . '/' . MAIN_DEFAULT_WORK . '\');' . "\n";

            $scaffold .= db_scaffold_output($controller_delete_file, $buffer);
            $create++;
        }

        // test data
        $test_insert  = '';
        $test_insert .= '    $insert_' . $table . ' = array(' . "\n";
        $test_insert .= '        1 => array(' . "\n";
        $test_insert .= str_replace('[N]', 1, $test_data);
        $test_insert .= '        ),' . "\n";
        $test_insert .= '        2 => array(' . "\n";
        $test_insert .= str_replace('[N]', 2, $test_data);
        $test_insert .= '        ),' . "\n";
        $test_insert .= '        3 => array(' . "\n";
        $test_insert .= str_replace('[N]', 3, $test_data);
        $test_insert .= '        ),' . "\n";
        $test_insert .= '    );' . "\n";

        $test_update = '';
        $test_update .= '    $update_' . $table . ' = array(' . "\n";
        $test_update .= '        3 => array(' . "\n";
        $test_update .= str_replace('[N]', 3, $test_data);
        $test_update .= '        ),' . "\n";
        $test_update .= '    );' . "\n";

        // test model
        $buffer  = '<?php' . "\n";
        $buffer .= "\n";
        $buffer .= 'model(\'' . $table . '.php\');' . "\n";
        $buffer .= "\n";
        $buffer .= 'db_transaction();' . "\n";
        $buffer .= "\n";
        $buffer .= '// insert' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    // data' . "\n";
        $buffer .= $test_insert;
        $buffer .= "\n";
        $buffer .= '    // insert' . "\n";
        $buffer .= '    foreach ($insert_' . $table . ' as $insert_data) {' . "\n";
        $buffer .= '        $warnings = validate_' . $table . '(normalize_' . $table . '($insert_data));' . "\n";
        $buffer .= '        if (empty($warnings)) {' . "\n";
        $buffer .= '            insert_' . $table . '(array(' . "\n";
        $buffer .= '                \'values\' => $insert_data,' . "\n";
        $buffer .= '            ));' . "\n";
        $buffer .= '        } else {' . "\n";
        $buffer .= '            debug($warnings);' . "\n";
        $buffer .= '        }' . "\n";
        $buffer .= '    }' . "\n";
        $buffer .= "\n";
        $buffer .= '    // test' . "\n";
        $buffer .= '    $' . $table . ' = select_' . $table . '(array(' . "\n";
        $buffer .= '        \'limit\' => 10,' . "\n";
        $buffer .= '    ));' . "\n";
        $buffer .= "\n";
        $buffer .= '    test_equals(\'count_' . $table . '\', count($' . $table . '), 3);' . "\n";
        $buffer .= "\n";
        $buffer .= '    for ($i = 1; $i <= 3; $i++) {' . "\n";
        $buffer .= '        $inserted_data = array_shift($' . $table . ');' . "\n";
        $buffer .= '        $test_data     = array(' . "\n";
        $buffer .= '            $i => $inserted_data,' . "\n";
        $buffer .= '        );' . "\n";
        $buffer .= '        test_array_subset(\'insert_' . $table . ' \' . $i, $test_data, $insert_' . $table . '[$i]);' . "\n";
        $buffer .= '    }' . "\n";
        $buffer .= '}' . "\n";
        $buffer .= "\n";
        $buffer .= '// update' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    // data' . "\n";
        $buffer .= $test_update;
        $buffer .= "\n";
        $buffer .= '    // update' . "\n";
        $buffer .= '    $warnings = validate_' . $table . '(normalize_' . $table . '($update_' . $table . '[3]));' . "\n";
        $buffer .= '    if (empty($warnings)) {' . "\n";
        $buffer .= '        update_' . $table . '(array(' . "\n";
        $buffer .= '            \'set\'   => $update_' . $table . '[3],' . "\n";
        $buffer .= '            \'where\' => \'id = 3\',' . "\n";
        $buffer .= '        ));' . "\n";
        $buffer .= '    } else {' . "\n";
        $buffer .= '        debug($warnings);' . "\n";
        $buffer .= '    }' . "\n";
        $buffer .= "\n";
        $buffer .= '    // test' . "\n";
        $buffer .= '    $' . $table . ' = select_' . $table . '(array(' . "\n";
        $buffer .= '        \'limit\' => 10,' . "\n";
        $buffer .= '    ));' . "\n";
        $buffer .= "\n";
        $buffer .= '    $updated_data = array_pop($' . $table . ');' . "\n";
        $buffer .= '    $test_data    = array(' . "\n";
        $buffer .= '        3 => $updated_data,' . "\n";
        $buffer .= '    );' . "\n";
        $buffer .= '    test_array_subset(\'update_' . $table . '\', $test_data, $update_' . $table . '[3]);' . "\n";
        $buffer .= '}' . "\n";
        $buffer .= "\n";
        $buffer .= '// delete' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    // delete' . "\n";
        $buffer .= '    delete_' . $table . '(array(' . "\n";
        $buffer .= '        \'where\' => \'id = 3\',' . "\n";
        $buffer .= '    ));' . "\n";
        $buffer .= "\n";
        $buffer .= '    // test' . "\n";
        $buffer .= '    $' . $table . ' = select_' . $table . '(array(' . "\n";
        $buffer .= '        \'limit\' => 10,' . "\n";
        $buffer .= '    ));' . "\n";
        $buffer .= "\n";
        $buffer .= '    test_equals(\'delete_' . $table . '\', count($' . $table . '), 2);' . "\n";
        $buffer .= '}' . "\n";
        $buffer .= "\n";
        $buffer .= 'db_rollback();' . "\n";

        $scaffold .= db_scaffold_output($test_model_file, $buffer);
        $create++;

        // test view
        $buffer  = '<?php' . "\n";
        $buffer .= "\n";
        $buffer .= '// index' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    // data' . "\n";
        $buffer .= $test_insert;
        $buffer .= "\n";
        $buffer .= '    // assign' . "\n";
        $buffer .= '    $_view[\'' . $table . '\'] = $insert_' . $table . ';' . "\n";
        $buffer .= "\n";
        $buffer .= '    // test' . "\n";
        $buffer .= '    $html = view(\'' . $table . '/index.php\', true);' . "\n";
        $buffer .= "\n";
        $buffer .= '    test_contains(\'' . $table . '/index 1\', $html, \'<td>\' . $insert_' . $table . '[1][\'' . $primary_key . '\'] . \'</td>\');' . "\n";
        $buffer .= '    test_contains(\'' . $table . '/index 2\', $html, \'<td>\' . $insert_' . $table . '[2][\'' . $primary_key . '\'] . \'</td>\');' . "\n";
        $buffer .= '    test_contains(\'' . $table . '/index 3\', $html, \'<td>\' . $insert_' . $table . '[3][\'' . $primary_key . '\'] . \'</td>\');' . "\n";
        $buffer .= '}' . "\n";
        $buffer .= "\n";
        $buffer .= '// post' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    // test' . "\n";
        $buffer .= '    $_view[\'data\'] = $insert_' . $table . '[1];' . "\n";
        $buffer .= "\n";
        $buffer .= '    $html = view(\'' . $table . '/post.php\', true);' . "\n";
        $buffer .= "\n";
        $buffer .= '    test_contains(\'' . $table . '/post\', $html, \'value="\' . $insert_' . $table . '[1][\'' . $primary_key . '\'] . \'"\');' . "\n";
        $buffer .= '}' . "\n";

        $scaffold .= db_scaffold_output($test_view_file, $buffer);
        $create++;

        // test controller
        $buffer  = '<?php' . "\n";
        $buffer .= "\n";
        $buffer .= 'model();' . "\n";
        $buffer .= "\n";
        $buffer .= 'db_transaction();' . "\n";
        $buffer .= "\n";
        $buffer .= '// index' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    // data' . "\n";
        $buffer .= $test_insert;
        $buffer .= "\n";
        $buffer .= '    // insert' . "\n";
        $buffer .= '    foreach ($insert_' . $table . ' as $insert_data) {' . "\n";
        $buffer .= '        $warnings = validate_' . $table . '(normalize_' . $table . '($insert_data));' . "\n";
        $buffer .= '        if (empty($warnings)) {' . "\n";
        $buffer .= '            insert_' . $table . '(array(' . "\n";
        $buffer .= '                \'values\' => $insert_data,' . "\n";
        $buffer .= '            ));' . "\n";
        $buffer .= '        } else {' . "\n";
        $buffer .= '            debug($warnings);' . "\n";
        $buffer .= '        }' . "\n";
        $buffer .= '    }' . "\n";
        $buffer .= "\n";
        $buffer .= '    // test' . "\n";
        $buffer .= '    $_params = array(\'' . $table . '\', \'index\');' . "\n";
        $buffer .= '    controller(\'' . $table . '/index.php\');' . "\n";
        $buffer .= '    $html = view(\'' . $table . '/index.php\', true);' . "\n";
        $buffer .= "\n";
        $buffer .= '    test_contains(\'' . $table . '/index 1\', $html, \'<td>\' . $insert_' . $table . '[1][\'' . $primary_key . '\'] . \'</td>\');' . "\n";
        $buffer .= '    test_contains(\'' . $table . '/index 2\', $html, \'<td>\' . $insert_' . $table . '[2][\'' . $primary_key . '\'] . \'</td>\');' . "\n";
        $buffer .= '    test_contains(\'' . $table . '/index 3\', $html, \'<td>\' . $insert_' . $table . '[3][\'' . $primary_key . '\'] . \'</td>\');' . "\n";
        $buffer .= '}' . "\n";
        $buffer .= "\n";
        $buffer .= '// post' . "\n";
        $buffer .= '{' . "\n";
        $buffer .= '    // test' . "\n";
        $buffer .= '    $_params = array(\'' . $table . '\', \'post\');' . "\n";
        $buffer .= '    $_GET[\'' . $primary_key . '\'] = 3;' . "\n";
        $buffer .= '    controller(\'' . $table . '/post.php\');' . "\n";
        $buffer .= '    $html = view(\'' . $table . '/post.php\', true);' . "\n";
        $buffer .= "\n";
        $buffer .= '    test_contains(\'' . $table . '/post\', $html, \'value="\' . $insert_' . $table . '[3][\'' . $primary_key . '\'] . \'"\');' . "\n";
        $buffer .= '}' . "\n";
        $buffer .= "\n";
        $buffer .= 'db_rollback();' . "\n";

        $scaffold .= db_scaffold_output($test_controller_file, $buffer);
        $create++;

        $scaffold .= "\n";
    }

    // home
    $view_home_file       = $views . MAIN_DEFAULT_MODE . '/' . MAIN_DEFAULT_WORK . '.php';
    $controller_home_file = $controllers . MAIN_DEFAULT_MODE . '/' . MAIN_DEFAULT_WORK . '.php';

    $scaffold .= '[index]' . "\n";

    $buffer  = '<?php import(\'app/views/header.php\') ?>' . "\n";
    $buffer .= "\n";
    $buffer .= '        <ul>' . "\n";

    foreach ($results as $result) {
        if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
            $table_comment = $result['Comment'];
        } else {
            $table_comment = null;
        }

        $table = array_shift($result);

        if (regexp_match('^' . DATABASE_PREFIX . $exclude_prefix, $table)) {
            continue;
        }

        $buffer .= '            <li><a href="<?php t(MAIN_FILE) ?>/' . $table . '">' . ($table_comment ? $table_comment : $table) . '</a></li>' . "\n";
    }

    $buffer .= '        </ul>' . "\n";

    $buffer .= "\n";
    $buffer .= '<?php import(\'app/views/footer.php\') ?>' . "\n";

    $scaffold .= db_scaffold_output($view_home_file, $buffer);
    $create++;

    $buffer  = '<?php' . "\n";

    $scaffold .= db_scaffold_output($controller_home_file, $buffer);
    $create++;

    // header
    $buffer  = '<!DOCTYPE html>' . "\n";
    $buffer .= '<html>' . "\n";
    $buffer .= '    <head>' . "\n";
    $buffer .= '        <meta charset="<?php t(MAIN_CHARSET) ?>">' . "\n";
    $buffer .= '        <title>scaffold</title>' . "\n";
    $buffer .= '    </head>' . "\n";
    $buffer .= '    <body>' . "\n";
    $buffer .= '        <h1>scaffold</h1>' . "\n";

    $scaffold .= db_scaffold_output($header_file, $buffer);
    $create++;

    // footer
    $buffer  = '    </body>' . "\n";
    $buffer .= '</html>' . "\n";

    $scaffold .= db_scaffold_output($footer_file, $buffer);
    $create++;

    // before
    $buffer  = '<?php' . "\n";
    $buffer .= "\n";
    $buffer .= 'import(\'' . $config_file . '\');' . "\n";

    $scaffold .= db_scaffold_output($before_file, $buffer);
    $create++;

    // config
    $buffer  = '<?php' . "\n";
    $buffer .= "\n";
    $buffer .= '$GLOBALS[\'config\'][\'limits\'] = array(' . "\n";

    $max_length = 0;
    foreach ($results as $result) {
        $table = array_shift($result);

        if (regexp_match('^' . DATABASE_PREFIX . $exclude_prefix, $table)) {
            continue;
        }

        $max_length = strlen($table) > $max_length ? strlen($table) : $max_length;
    }

    foreach ($results as $result) {
        $table = array_shift($result);

        if (regexp_match('^' . DATABASE_PREFIX . $exclude_prefix, $table)) {
            continue;
        }

        $space = str_repeat(' ', $max_length - strlen($table));;

        $buffer .= '    \'' . $table . '\' ' . $space . '=> 10,' . "\n";
    }

    $buffer .= ');' . "\n";

    $scaffold .= db_scaffold_output($config_file, $buffer);
    $create++;

    $scaffold .= "\n";
    $scaffold .= "Created: " . $create . " files\n";

    if (php_sapi_name() === 'cli') {
        echo "levis: PHP Framework\n";
        echo "DB Scaffold\n";
        echo "\n";
        echo $scaffold;
        exit;
    }

    // result
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>DB Scaffold</title>\n";

    style();

    echo "</head>\n";
    echo "<body>\n";
    echo "<h1>DB Scaffold</h1>\n";
    echo "<pre><code>" . t($scaffold, true) . "</code></pre>\n";
    echo "</body>\n";
    echo "</html>\n";

    exit;
}

/**
 * Output the scaffolded data from the database.
 *
 */
function db_scaffold_output($file, $data)
{
    if (function_exists('my_db_scaffold_output')) {
        eval('my_db_scaffold_output($file, $data);');

        return;
    }

    $file = DATABASE_SCAFFOLD_PATH . $file;
    $info = pathinfo($file);

    $result = '';

    if (!is_dir($info['dirname'])) {
        if (mkdir($info['dirname'], PERMISSION_DIRECTORY, true)) {
            $result .= 'OK: ' . $info['dirname'] . "\n";
        } else {
            $result .= 'NG: ' . $info['dirname'] . "\n";
        }
    }

    if ($fp = fopen($file, 'w')) {
        chmod($file, PERMISSION_FILE);
        fwrite($fp, $data);
        fclose($fp);

        $result .= 'OK: ' . $file . "\n";
    } else {
        $result .= 'NG: ' . $file . "\n";
    }

    return $result;
}

/**
 * Import SQL from the file.
 *
 * @param string $file
 *
 * @return int
 */
function db_import($file)
{
    if (function_exists('my_db_import')) {
        $count = null;

        eval('$count = my_db_import($file);');

        return $count;
    }

    if ($fp = fopen($file, 'r')) {
        $sql  = '';
        $i    = 0;
        $flag = true;

        while ($line = fgets($fp)) {
            $line = str_replace("\r\n", "\n", $line);
            $line = str_replace("\r", "\n", $line);

            if ((substr_count($line, '\'') - substr_count($line, '\\\'')) % 2 !== 0) {
                $flag = !$flag;
            }

            $sql .= $line;

            if (preg_match('/;$/', trim($line)) && $flag) {
                $resource = db_query($sql);
                if (!$resource) {
                    if (LOGGING_MESSAGE) {
                        logging('message', 'db: Query error: ' . db_error());
                    }

                    error('db: Query error' . (DEBUG_LEVEL ? ': ' . db_error(): ''));
                }

                $sql = '';
                $i++;
            }
        }
        fclose($fp);
    } else {
        error('db: Can\'t open the import file');
    }

    return $i;
}

/**
 * Export SQL to the file.
 *
 * @param string|null $file
 * @param array       $target
 * @param bool        $combined
 */
function db_export($file = null, $target = array(), $combined = true)
{
    if (function_exists('my_db_export')) {
        eval('my_db_export($file, $target, $combined);');

        return;
    }

    $resource = db_query(db_sql('table_list'));
    $results  = db_result($resource);

    $tables = array();
    foreach ($results as $result) {
        $tables[] = array_shift($result);
    }

    $text  = '-- Database: ' . DATABASE_NAME . ' (' . DATABASE_TYPE . ")\n";
    $text .= '-- Datetime: ' . localdate('Y-m-d H:i:s') . "\n";
    $text .= '-- Host: ' . gethostbyaddr(clientip()) . "\n";
    $text .= "\n";

    foreach ($tables as $table) {
        if (in_array($table, $target)) {
            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $table_escaped = '`' . $table . '`';
            } else {
                $table_escaped = '"' . $table . '"';
            }

            $resource = db_query(db_sql('table_create', $table));
            $results  = db_result($resource);

            if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
                $text .= "DROP TABLE IF EXISTS " . $table_escaped . ";\n";
                $text .= $results[0]['Create Table'] . ";\n";
                $text .= "\n";
            } elseif (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
                $text .= "DROP TABLE IF EXISTS " . $table_escaped . ";\n";
                $text .= $results[0]['case'] . ";\n";
                $text .= "\n";
            } elseif (DATABASE_TYPE === 'pdo_sqlite' || DATABASE_TYPE === 'pdo_sqlite2' || DATABASE_TYPE === 'sqlite') {
                $text .= "DROP TABLE IF EXISTS " . $table_escaped . ";\n";
                $text .= $results[0]['sql'] . ";\n";
                $text .= "\n";
            }

            $resource = db_query('SELECT * FROM ' . $table_escaped . ';');
            $results  = db_result($resource);

            $values = array();
            $i      = 0;
            foreach ($results as $result) {
                $inserts = array();
                foreach ($result as $data) {
                    if ($data === null) {
                        $inserts[] = 'NULL';
                    } else {
                        $inserts[] = db_escape($data);
                    }
                }

                if ($combined === true) {
                    $values[intval($i / 50)][] = '(' . implode(', ', $inserts) . ')';
                } else {
                    $text .= "INSERT INTO " . $table_escaped . " VALUES(" . implode(', ', $inserts) . ");\n";
                }

                $i++;
            }

            if ($combined === true && !empty($values)) {
                foreach ($values as $value) {
                    $text .= "INSERT INTO " . $table_escaped . " VALUES\n";
                    $text .= implode(",\n", $value);
                    $text .= ";\n";
                }
            }

            $text .= "\n";
        }
    }

    $text .= "-- Export Completed.\n";

    if ($file === null) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . DATABASE_NAME . '.sql"');
        echo $text;
        exit;
    } else {
        if (file_put_contents($file, $text) === false) {
            error('db: Can\'t output to the export file');
        }
        chmod($file, PERMISSION_FILE);
    }

    return;
}

/**
 * Get the sql data for database.
 *
 * @param string      $type
 * @param string|null $table
 *
 * @return string
 */
function db_sql($type, $table = null)
{
    if (function_exists('my_db_sql')) {
        eval('my_db_sql($type, $table);');

        return;
    }

    if (DATABASE_TYPE === 'pdo_mysql' || DATABASE_TYPE === 'mysql') {
        if ($type === 'table_list') {
            $sql = '
                SHOW TABLE STATUS;
            ';
        } elseif ($type === 'table_create') {
            $sql = '
                SHOW CREATE TABLE `' . $table . '`;
            ';
        } elseif ($type === 'table_define') {
            $sql = '
                SHOW FULL COLUMNS FROM `' . $table . '`;
            ';
        }
    } elseif (DATABASE_TYPE === 'pdo_pgsql' || DATABASE_TYPE === 'pgsql') {
        if ($type === 'table_list') {
            $sql = '
                SELECT
                    pg_class.relname AS relname
                FROM
                    pg_class INNER JOIN pg_namespace ON pg_class.relnamespace = pg_namespace.oid
                WHERE
                    pg_class.relkind = \'r\' AND pg_namespace.nspname = \'public\';
            ';
        } elseif ($type === 'table_create') {
            $sql = '
                SELECT
                    CASE
                        WHEN tb.relkind = \'r\' THEN(
                            SELECT \'CREATE TABLE "' . $table . '"(\' || chr(10) || array_to_string(
                                ARRAY(
                                    SELECT \' "\' || "Column" || \'" \'|| "Type" || "Modifiers" || "Index" FROM(
                                        /* Column */
                                        SELECT
                                            at.attnum, ns.nspname AS schema, tb.relname AS table, at.attname AS "Column",
                                            /* Type */
                                            CASE
                                                WHEN at.attinhcount <> 0 OR at.attisdropped THEN null
                                                ELSE
                                                    CASE
                                                        WHEN tp.typname = \'int2\'   THEN \'SMALLINT\'
                                                        WHEN tp.typname = \'int4\'   THEN \'INTEGER\'
                                                        WHEN tp.typname = \'int8\'   THEN \'BIGINT\'
                                                        WHEN tp.typname = \'float4\' THEN \'REAL\'
                                                        WHEN tp.typname = \'float8\' THEN \'DOUBLE PRECISION\'
                                                        WHEN tp.typname = \'bpchar\' THEN \'CHAR\'
                                                        ELSE UPPER(tp.typname)
                                                    END ||
                                                    CASE
                                                        WHEN at.attlen >= 0             THEN \'\'
                                                        WHEN at.atttypmod < 4           THEN \'\'
                                                        WHEN tp.typname <> \'numeric\'  THEN \'(\' || at.atttypmod - 4 || \')\'
                                                        WHEN (at.atttypmod & 65535) = 4 THEN \'(\' || (at.atttypmod >> 16) || \')\'
                                                        ELSE \'(\' || (at.atttypmod >> 16) || \',\' || (at.atttypmod & 65535) - 4 || \')\'
                                                    END
                                            END AS "Type",
                                            /* Modifiers */
                                            CASE
                                                WHEN at.attnotnull THEN \' NOT NULL\'
                                                ELSE \'\'
                                            END ||
                                            CASE
                                                WHEN ad.adbin IS NULL THEN \'\'
                                                ELSE \' DEFAULT \' || UPPER(pg_get_expr(ad.adbin, tb.oid))
                                            END AS "Modifiers",
                                            /* one-column Index */
                                            CASE
                                                WHEN ix.indexrelid IS NULL THEN \'\'
                                                ELSE
                                                    CASE
                                                        WHEN ix.indisprimary THEN \' PRIMARY KEY\'
                                                        WHEN ix.indisunique  THEN \' UNIQUE\'
                                                        ELSE \'\'
                                                    END
                                            END AS "Index"
                                        FROM
                                            pg_attribute at
                                            INNER JOIN pg_type tp ON at.atttypid = tp.oid
                                            LEFT OUTER JOIN pg_attrdef ad ON ad.adrelid = tb.oid AND ad.adnum = at.attnum
                                            LEFT OUTER JOIN pg_index ix ON ix.indrelid = tb.oid AND ix.indnatts = 1 AND at.attnum = ix.indkey[0]
                                            LEFT OUTER JOIN pg_class ic ON ix.indexrelid = ic.oid
                                            LEFT OUTER JOIN pg_am    am ON ic.relam = am.oid
                                        WHERE
                                            tb.oid = at.attrelid AND at.attnum >= 1
                                    ) AS columns ORDER BY attnum
                                ), \',\' || chr(10)
                            )
                            ||
                            (
                                SELECT
                                    CASE
                                        WHEN COUNT(*) = 0 THEN \'\'
                                        ELSE \',\' || chr(10) || \' \' || array_to_string(
                                            ARRAY(
                                                SELECT
                                                    CASE
                                                        WHEN indisprimary THEN \'PRIMARY KEY \'
                                                        ELSE \'UNIQUE \'
                                                    END
                                                    || substr(indexdef, strpos(indexdef, \'(\'), strpos(indexdef, \')\') - strpos(indexdef, \'(\') + 1) || \' /* \'||index||\' */\'
                                                FROM
                                                (
                                                    SELECT
                                                        ic.relname AS index, ns.nspname AS schema, tb.relname AS table, ix.indnatts, ix.indisunique, ix.indisprimary, am.amname, ix.indkey, pg_get_indexdef(ic.oid) AS indexdef
                                                    FROM
                                                        pg_index ix
                                                        INNER JOIN pg_class ic ON ix.indexrelid = ic.oid
                                                        INNER JOIN pg_am    am ON ic.relam = am.oid
                                                    WHERE
                                                        ix.indrelid = tb.oid AND ix.indnatts > 1 AND (ix.indisprimary OR ix.indisunique)
                                                ) AS def ORDER BY indisprimary desc, index
                                            ), \',\'||chr(10)
                                        )
                                    END
                                FROM
                                    pg_index ix
                                WHERE
                                    ix.indrelid = tb.oid AND ix.indnatts > 1 AND (ix.indisprimary OR ix.indisunique)
                            ) || chr(10) || \');\'
                        )
                        END
                FROM
                    pg_class tb
                    INNER JOIN pg_namespace ns ON tb.relnamespace = ns.oid
                WHERE
                    tb.relname = \'' . $table . '\';
            ';
        } elseif ($type === 'table_define') {
            $sql = '
                SELECT
                    column_name, data_type, is_nullable
                FROM
                    information_schema.columns
                WHERE
                    table_schema = \'public\' AND table_name = \'' . $table . '\';
            ';
        }
    } elseif (DATABASE_TYPE === 'pdo_sqlite' || DATABASE_TYPE === 'pdo_sqlite2' || DATABASE_TYPE === 'sqlite') {
        if ($type === 'table_list') {
            $sql = '
                SELECT
                    name
                FROM
                    sqlite_master
                WHERE
                    type = \'table\';
            ';
        } elseif ($type === 'table_create') {
            $sql = '
                SELECT
                    sql
                FROM
                    sqlite_master
                WHERE
                    tbl_name = \'' . $table . '\';
            ';
        } elseif ($type === 'table_define') {
            $sql = '
                PRAGMA TABLE_INFO(\'' . $table . '\');
            ';
        }
    }

    $sql = preg_replace('/\s+/', ' ', $sql);
    $sql = preg_replace('/^\s+/', '', $sql);
    $sql = preg_replace('/\s+$/', '', $sql);

    return $sql;
}
