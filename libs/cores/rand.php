<?php

/*******************************************************************************

 Functions for Rand

*******************************************************************************/

/**
 * Get a random number.
 *
 * @param int $min
 * @param int $max
 *
 * @return int
 */
function rand_number($min = null, $max = null)
{
    if ($min === null) {
        $min = 0;
    }
    if ($max === null) {
        $max = mt_getrandmax();
    }

    return mt_rand($min, $max);
}

/**
 * Get a random string.
 *
 * @param int $max
 *
 * @return string
 */
function rand_string($max = 32)
{
    return substr(md5(uniqid(rand_number(), true)), 0, $max);
}
