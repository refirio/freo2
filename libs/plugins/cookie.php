<?php

/*******************************************************************************

 Functions for Cookie

*******************************************************************************/

/**
 * Set the cookie.
 *
 * @param string      $name
 * @param string      $value
 * @param int         $expire
 * @param string|null $path
 * @param string|null $domain
 * @param bool        $secure
 *
 * @return bool
 */
function cookie_set($name, $value, $expire = 0, $path = null, $domain = null, $secure = false)
{
    if ($path === null) {
        $path = dirname($_SERVER['SCRIPT_NAME']);
    }
    if ($domain === null && $_SERVER['SERVER_NAME'] !== 'localhost') {
        $domain = $_SERVER['SERVER_NAME'];
    }

    return setcookie($name, $value, $expire, $path, $domain, $secure);
}
