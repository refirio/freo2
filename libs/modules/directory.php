<?php

/*******************************************************************************

 Functions for Directory

*******************************************************************************/

/**
 * Get the directory information.
 *
 * @param string $dir
 *
 * @return int
 */
function directory_info($dir)
{
    if (!is_dir($dir)) {
        return 0;
    }

    $size = 0;

    if ($dh = opendir($dir)) {
        while (($entry = readdir($dh)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (is_dir($dir . $entry)) {
                $size += directory_info($dir . $entry . '/');
            } elseif (is_file($dir . $entry)) {
                $size += filesize($dir . $entry);
            }
        }
    } else {
        return 0;
    }

    return $size;
}

/**
 * Makes directory.
 *
 * @param string $path
 * @param int    $mode
 * @param bool   $recursive
 *
 * @return bool
 */
function directory_mkdir($path, $mode = 0707, $recursive = true)
{
    if (is_dir($path)) {
        return true;
    }

    if ($recursive) {
        $paths = explode('/', $path);
        $path  = '';

        foreach ($paths as $directory) {
            if ($directory === '') {
                continue;
            }

            if ($path !== '') {
                $path .= '/';
            }
            $path .= $directory;

            if (!is_dir($path)) {
                if (mkdir($path, $mode)) {
                    chmod($path, $mode);
                } else {
                    return false;
                }
            }
        }

        return true;
    } else {
        if (mkdir($path, $mode)) {
            chmod($path, $mode);

            return true;
        } else {
            return false;
        }
    }
}

/**
 * Removes directory
 *
 * @param string $path
 * @param bool   $recursive
 *
 * @return bool
 */
function directory_rmdir($path, $recursive = true)
{
    if (!is_dir($path)) {
        return true;
    }

    $flag = false;

    if ($dh = opendir($path)) {
        while (($entry = readdir($dh)) !== false) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (is_dir($path . $entry)) {
                if ($recursive && !directory_rmdir($path . $entry . '/')) {
                    return false;
                }

                $flag = true;
            } elseif (is_file($path . $entry)) {
                if (!unlink($path . $entry)) {
                    return false;
                }
            }
        }
    } else {
        return false;
    }

    if (!$recursive && $flag) {
        return true;
    }

    if (rmdir($path)) {
        return true;
    } else {
        return false;
    }
}
