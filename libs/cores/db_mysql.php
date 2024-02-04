<?php

/*******************************************************************************

 Functions for DB Driver (mysql)

*******************************************************************************/

/**
 * Connect to the database.
 *
 * @return void
 */
function db_driver_connect()
{
    global $_db;

    $_db['resource'][$_db['target']]['dbh'] = mysql_connect($_db['resource'][$_db['target']]['config']['host'] . ($_db['resource'][$_db['target']]['config']['port'] ? ':' . $_db['resource'][$_db['target']]['config']['port'] : ''), $_db['resource'][$_db['target']]['config']['username'], $_db['resource'][$_db['target']]['config']['password'], true);
    if (!$_db['resource'][$_db['target']]['dbh']) {
        if (LOGGING_MESSAGE) {
            logging('message', 'db: Connect error');
        }

        error('db: Connect error');
    }

    $resource = mysql_select_db($_db['resource'][$_db['target']]['config']['name'], $_db['resource'][$_db['target']]['dbh']);
    if (!$resource) {
        if (LOGGING_MESSAGE) {
            logging('message', 'db: Connect error');
        }

        error('db: Select DB error');
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

    return mysql_query($query, $_db['resource'][$_db['target']]['dbh']);
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
    while ($data = mysql_fetch_array($resource, MYSQL_ASSOC)) {
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

    return mysql_num_rows($resource);
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

    return mysql_affected_rows();
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

    return '\'' . addslashes($data) . '\'';
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

    $data = regexp_replace('(^\'|\'$)', '', $data);
    $data = stripslashes($data);

    return $data;
}

/**
 * Get the error from the database.
 *
 * @return string
 */
function db_driver_error()
{
    global $_db;

    return mysql_error($_db['resource'][$_db['target']]['dbh']);
}

/**
 * Start a transaction.
 *
 * @return mixed
 */
function db_driver_transaction()
{
    global $_db;

    return mysql_query('START TRANSACTION', $_db['resource'][$_db['target']]['dbh']);
}

/**
 * Commit a transaction.
 *
 * @return mixed
 */
function db_driver_commit()
{
    global $_db;

    return mysql_query('COMMIT', $_db['resource'][$_db['target']]['dbh']);
}

/**
 * Rollback a transaction.
 *
 * @return mixed
 */
function db_driver_rollback()
{
    global $_db;

    return mysql_query('ROLLBACK', $_db['resource'][$_db['target']]['dbh']);
}
