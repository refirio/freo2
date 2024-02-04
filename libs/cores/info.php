<?php

/*******************************************************************************

 Functions for Info

*******************************************************************************/

/**
 * Output a information page for php.
 *
 */
function info_php()
{
    if (auth() === false) {
        return;
    }

    phpinfo();

    exit;
}

/**
 * Output a information page for framework.
 *
 */
function info_levis()
{
    if (auth() === false) {
        return;
    }

    about();

    exit;
}
