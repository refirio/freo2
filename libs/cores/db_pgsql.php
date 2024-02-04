<?php

/*******************************************************************************

 Functions for DB Driver (pgsql)

*******************************************************************************/

/**
 * Connect to the database.
 *
 * @return void
 */
function db_driver_connect()
{
    global $_db;

    $_db['resource'][$_db['target']]['dbh'] = pg_connect('host=' . $_db['resource'][$_db['target']]['config']['host'] . ($_db['resource'][$_db['target']]['config']['port'] ? ' port=' . $_db['resource'][$_db['target']]['config']['port'] : '') . ' dbname=' . $_db['resource'][$_db['target']]['config']['name'] . ' user=' . $_db['resource'][$_db['target']]['config']['username'] . ' password=' . $_db['resource'][$_db['target']]['config']['password'], true);
    if (!$_db['resource'][$_db['target']]['dbh']) {
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

    return pg_query($_db['resource'][$_db['target']]['dbh'], $query);
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
    while ($data = pg_fetch_array($resource, null, PGSQL_ASSOC)) {
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

    return pg_num_rows($resource);
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

    return pg_affected_rows($resource);
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

    return '\'' . str_replace('\'', '\'\'', $data) . '\'';
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
    $data = str_replace('\'\'', '\'', $data);

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

    return pg_result_error($_db['resource'][$_db['target']]['dbh']);
}

/**
 * Start a transaction.
 *
 * @return mixed
 */
function db_driver_transaction()
{
    global $_db;

    return pg_query($_db['resource'][$_db['target']]['dbh'], 'START TRANSACTION');
}

/**
 * Commit a transaction.
 *
 * @return mixed
 */
function db_driver_commit()
{
    global $_db;

    return pg_query($_db['resource'][$_db['target']]['dbh'], 'COMMIT');
}

/**
 * Rollback a transaction.
 *
 * @return mixed
 */
function db_driver_rollback()
{
    global $_db;

    return pg_query($_db['resource'][$_db['target']]['dbh'], 'ROLLBACK');
}
