<?php

/*******************************************************************************

 Functions for Regexp

*******************************************************************************/

/**
 * Perform a regular expression match.
 *
 * @param string $pattern
 * @param string $subject
 *
 * @return mixed
 */
function regexp_match($pattern, $subject)
{
    if (is_null($subject)) {
        $subject = '';
    }

    $regexp = false;

    if (REGEXP_TYPE === 'ereg') {
        if (eregi($pattern, $subject, $matches)) {
            $regexp = true;
        }
    } else {
        if (preg_match('/' . $pattern . '/i', $subject, $matches)) {
            $regexp = true;
        }
    }

    if (!empty($matches)) {
        $regexp = $matches;
    }

    return $regexp;
}

/**
 * Perform a regular expression search and replace.
 *
 * @param string $pattern
 * @param string $replacement
 * @param string $subject
 * @param int    $limit
 *
 * @return mixed
 */
function regexp_replace($pattern, $replacement, $subject, $limit = -1)
{
    if (REGEXP_TYPE === 'ereg') {
        if ($limit !== -1) {
            error('regexp: REGEXP_TYPE [ereg] is not support $limit.');
        }

        $subject = eregi_replace($pattern, $replacement, $subject);
    } else {
        $subject = preg_replace('/' . $pattern . '/i', $replacement, $subject, $limit);
    }

    return $subject;
}
