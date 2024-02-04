<?php

/*******************************************************************************

 Functions for DB Driver (PDO)

*******************************************************************************/

/**
 * Connect to the database.
 *
 * @return void
 */
function db_driver_connect()
{
    global $_db;

    if ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_mysql') {
        $dsn = 'mysql:dbname=' . $_db['resource'][$_db['target']]['config']['name'] . ';host=' . $_db['resource'][$_db['target']]['config']['host'] . ($_db['resource'][$_db['target']]['config']['port'] ? ';port=' . $_db['resource'][$_db['target']]['config']['port'] : '');
    } elseif ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_pgsql') {
        $dsn = 'pgsql:dbname=' . $_db['resource'][$_db['target']]['config']['name'] . ';host=' . $_db['resource'][$_db['target']]['config']['host'] . ($_db['resource'][$_db['target']]['config']['port'] ? ';port=' . $_db['resource'][$_db['target']]['config']['port'] : '');
    } elseif ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_sqlite') {
        $dsn = 'sqlite:' . $_db['resource'][$_db['target']]['config']['name'];
    } elseif ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_sqlite2') {
        $dsn = 'sqlite2:' . $_db['resource'][$_db['target']]['config']['name'];
    }

    if ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_mysql') {
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        );
    } elseif ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_pgsql' or $_db['resource'][$_db['target']]['config']['type'] === 'pdo_sqlite' or $_db['resource'][$_db['target']]['config']['type'] === 'pdo_sqlite2') {
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        );
    }

    try {
        $_db['resource'][$_db['target']]['dbh'] = new PDO($dsn, $_db['resource'][$_db['target']]['config']['username'], $_db['resource'][$_db['target']]['config']['password'], $options);
    } catch (PDOException $e) {
        if (LOGGING_MESSAGE) {
            logging('message', 'db: Connect error');
        }

        error('db: Connect error');
    }

    return;
}

/**
 * Query to the database.
 *
 * @param mixed $query
 *
 * @return mixed
 */
function db_driver_query($query)
{
    global $_db;

    return $_db['resource'][$_db['target']]['dbh']->query($query);
}

/**
 * Get the result from the database.
 *
 * @param mixed $resource
 *
 * @return array
 */
function db_driver_result($resource)
{
    global $_db;

    $results = array();
    while ($data = $resource->fetch(PDO::FETCH_ASSOC)) {
        $results[] = $data;
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
function db_driver_count($resource)
{
    global $_db;

    return $resource->rowCount();
}

/**
 * Get the affected count from the database.
 *
 * @param mixed $resource
 *
 * @return int
 */
function db_driver_affected_count($resource)
{
    global $_db;

    return $resource->rowCount();
}

/**
 * Get the escaped data for database.
 *
 * @param string $data
 *
 * @return string
 */
function db_driver_escape($data)
{
    global $_db;

    if ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_mysql') {
        return '\'' . addslashes($data) . '\'';
    } elseif ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_pgsql' or $_db['resource'][$_db['target']]['config']['type'] === 'pdo_sqlite' or $_db['resource'][$_db['target']]['config']['type'] === 'pdo_sqlite2') {
        return '\'' . str_replace('\'', '\'\'', $data) . '\'';
    }
}

/**
 * Get the unescaped data for database.
 *
 * @param string $data
 *
 * @return string
 */
function db_driver_unescape($data)
{
    global $_db;

    if ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_mysql') {
        $data = regexp_replace('(^\'|\'$)', '', $data);
        $data = stripslashes($data);

        return $data;
    } elseif ($_db['resource'][$_db['target']]['config']['type'] === 'pdo_pgsql' or $_db['resource'][$_db['target']]['config']['type'] === 'pdo_sqlite' or $_db['resource'][$_db['target']]['config']['type'] === 'pdo_sqlite2') {
        $data = regexp_replace('(^\'|\'$)', '', $data);
        $data = str_replace('\'\'', '\'', $data);

        return $data;
    }
}

/**
 * Get the error from the database.
 *
 * @return string
 */
function db_driver_error()
{
    global $_db;

    $info = $_db['resource'][$_db['target']]['dbh']->errorInfo();
    if (isset($info[2]) && $info[2] !== 'not an error') {
        $error = $info[2];
    }

    return $error;
}

/**
 * Get the last insert id from the database.
 *
 * @param string|null $sequence
 *
 * @return string
 */
function db_driver_last_insert_id($sequence = null)
{
    global $_db;

    return $_db['resource'][$_db['target']]['dbh']->lastInsertId($sequence);
}

/**
 * Start a transaction.
 *
 * @return mixed
 */
function db_driver_transaction()
{
    global $_db;

    return $_db['resource'][$_db['target']]['dbh']->beginTransaction();
}

/**
 * Commit a transaction.
 *
 * @return mixed
 */
function db_driver_commit()
{
    global $_db;

    return $_db['resource'][$_db['target']]['dbh']->commit();
}

/**
 * Rollback a transaction.
 *
 * @return mixed
 */
function db_driver_rollback()
{
    global $_db;

    return $_db['resource'][$_db['target']]['dbh']->rollBack();
}
