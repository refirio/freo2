<?php

/*******************************************************************************

 Functions for Hash

*******************************************************************************/

import('libs/cores/rand.php');

/**
 * Get a hash salt.
 *
 * @return string
 */
function hash_salt()
{
    return rand_string();
}

/**
 * Get a hash.
 *
 * @param string $data
 * @param string $salt
 * @param int    $count
 *
 * @return string
 */
function hash_crypt($data, $salt, $count = 10000)
{
    for ($i = 0; $i < $count; $i++) {
        $data = md5($data . ':' . $salt);
    }
    return $data;
}
