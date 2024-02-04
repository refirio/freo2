<?php

/*******************************************************************************

 Functions for Test

*******************************************************************************/

/**
 * Output a index page for test.
 *
 */
function test_index()
{
    if (auth() === false) {
        return;
    }

    if (!file_exists(MAIN_PATH . TEST_PATH)) {
        error('test: ' . MAIN_PATH . TEST_PATH . ' is not found.');
    }

    $results = array();
    if (isset($_GET['_test'])) {
        if (!regexp_match('^[0-9\:\;]+$', $_GET['_test'])) {
            redirect('/?_mode=test_index');
        }

        $tests = explode(';', $_GET['_test']);

        foreach ($tests as $test) {
            if ($test === '') {
                continue;
            }

            list($index, $result) = explode(':', $test);

            $results[$index] = $result;
        }
    }

    $_view['ok'] = 0;
    $_view['ng'] = 0;
    $_view['targets'] = array();

    $i = 0;
    if ($dh = opendir(MAIN_PATH . TEST_PATH)) {
        while (($entry = readdir($dh)) !== false) {
            if (!is_file(MAIN_PATH . TEST_PATH . $entry)) {
                continue;
            }

            if ($regexp = regexp_match('^([_a-zA-Z0-9\-]+)\.php$', $entry)) {
                $name = $regexp[1];
            } else {
                continue;
            }

            $i++;

            $result = null;
            if (isset($results[$i])) {
                $result = $results[$i];
                if ($result) {
                    $result = 'OK';

                    $_view['ok']++;
                } else {
                    $result = 'NG';

                    $_view['ng']++;
                }
            }

            $_view['targets'][] = array(
                'name'   => $name,
                'file'   => $entry,
                'result' => $result,
            );
        }
        closedir($dh);
    } else {
        if (LOGGING_MESSAGE) {
            logging('message', 'test: Opendir error: ' . MAIN_PATH . TEST_PATH);
        }

        error('test: Opendir error' . (DEBUG_LEVEL ? ': ' . MAIN_PATH . TEST_PATH : ''));
    }

    if (php_sapi_name() === 'cli') {
        echo "levis: PHP Framework\n";
        echo "Test\n";
        echo "\n";
        $i = 0;
        foreach ($_view['targets'] as $target) {
            echo (++$i) . ". " . $target['name'] . "\n";
        }
        exit;
    }

    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
    echo "<title>Test</title>\n";

    style();

    echo "</head>\n";
    echo "<body>\n";
    echo "<h1>Test</h1>\n";
    echo "<p>Test Index.</p>\n";
    echo "<ul>\n";
    echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=test_exec&amp;_test=\">All Test.</a></li>\n";
    echo "</ul>\n";
    echo "<ol>\n";

    foreach ($_view['targets'] as $target) {
        echo "<li><a href=\"" . t(MAIN_FILE, true) . "/?_mode=test_exec&amp;target=" . t($target['name'], true) . "\">" . t($target['name'], true) . "</a>" . ($target['result'] ? h(' (' . $target['result'] . ')', true) : '') . "</li>\n";
    }

    echo "</ol>\n";

    if ($_view['ok'] || $_view['ng']) {
        if ($_view['ng']) {
            echo "<p>" . $_view['ng'] . " Test is NG!</p>\n";
        } else {
            echo "<p>All Test is OK!</p>\n";
        }
    }

    echo "</body>\n";
    echo "</html>\n";

    exit;
}

/**
 * Output a result page for test.
 *
 */
function test_exec()
{
    global $_view;

    if (php_sapi_name() === 'cli') {
        if (isset($_SERVER['argv'][2])) {
            $_GET['target'] = $_SERVER['argv'][2];
        }
    }

    if (auth() === false) {
        return;
    }

    if (!file_exists(MAIN_PATH . TEST_PATH)) {
        error('test: ' . MAIN_PATH . TEST_PATH . ' is not found.');
    }

    $index  = 0;
    $result = 0;
    if (isset($_GET['_test'])) {
        if ($_GET['_test'] !== '' && !regexp_match('^[0-9\:\;]+$', $_GET['_test'])) {
            redirect('/?_mode=test_index');
        }

        $tests = explode(';', $_GET['_test']);
        $test  = $tests[count($tests) - 1];

        if ($test !== '') {
            list($index, $result) = explode(':', $test);
        }

        $i    = 0;
        $flag = false;
        if ($dh = opendir(MAIN_PATH . TEST_PATH)) {
            while (($entry = readdir($dh)) !== false) {
                if (!is_file(MAIN_PATH . TEST_PATH . $entry)) {
                    continue;
                }

                if ($regexp = regexp_match('^([_a-zA-Z0-9\-]+)\.php$', $entry)) {
                    $_GET['target'] = $regexp[1];
                } else {
                    continue;
                }

                if ($index == $i++) {
                    $index = $i;
                    $flag  = true;

                    break;
                }
            }
            closedir($dh);
        } else {
            if (LOGGING_MESSAGE) {
                logging('message', 'test: Opendir error: ' . MAIN_PATH . TEST_PATH);
            }

            error('test: Opendir error' . (DEBUG_LEVEL ? ': ' . MAIN_PATH . TEST_PATH: ''));
        }

        if ($flag === false) {
            redirect('/?_mode=test_index&_test=' . $_GET['_test']);
        }
    }

    if (empty($_GET['target'])) {
        error('test: Target is not defined.');
    } elseif (!regexp_match('^[_a-zA-Z0-9\-]+$', $_GET['target'])) {
        error('test: ' . $_GET['target'] . ' is not found.');
    }

    $_view['ok'] = 0;
    $_view['ng'] = 0;

    if (php_sapi_name() === 'cli') {
        echo "levis: PHP Framework\n";
        echo "Test\n";
        echo "\n";
    } else {
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        echo "<meta charset=\"" . t(MAIN_CHARSET, true) . "\">\n";
        echo "<title>Test</title>\n";

        style();

        echo "</head>\n";
        echo "<body>\n";
        echo "<h1>Test</h1>\n";
        echo "<pre>";
    }

    list($micro, $second) = explode(' ', microtime());
    $time_start = $micro + $second;

    test_import(MAIN_PATH . TEST_PATH . $_GET['target'] . '.php');

    list($micro, $second) = explode(' ', microtime());
    $time_end = $micro + $second;

    $_view['time'] = ceil(($time_end - $time_start) * 10000) / 10000;

    echo "\n";
    echo "OK: " . $_view['ok'] . "\n";
    echo "NG: " . $_view['ng'] . "\n";
    echo "Time: " . $_view['time'] . " sec.\n";

    if (php_sapi_name() !== 'cli') {
        echo "</pre>\n";
        echo "<p><a href=\"" . t(MAIN_FILE, true) . "/?_mode=test_index\">Back to Index</a></p>\n";

        if (isset($_GET['_test'])) {
            $_view['url'] = MAIN_FILE . "/?_mode=test_exec&_test=" . $_GET['_test'] . ';' . $index . ":" . ($_view['ng'] ? 0 : 1);

            echo "<script>\n";
            echo "setTimeout('window.location.href = \'" . $_view['url'] . "\'', 1000);\n";
            echo "</script>\n";
            echo "<noscript>\n";
            echo "<p><a href=\"" . t($_view['url'], true) . "\">next</a></p>\n";
            echo "</noscript>\n";
        }

        echo "</body>\n";
        echo "</html>\n";
    }

    exit;
}

/**
 * Output a result of the test.
 *
 * @param string $data
 * @param bool   $return
 *
 * @return void|string
 */
function test_result($title, $result)
{
    global $_view;

    if ($result === true) {
        $_view['ok']++;
    } else {
        $_view['ng']++;

        echo 'NG: ' . $title . "\n";
    }

    return;
}

/**
 * Load the test files.
 *
 * @param string|null $file
 *
 * @return void
 */
function test_import($file)
{
    global $_params, $_db, $_view;

    require_once MAIN_PATH . TEST_PATH . $_GET['target'] . '.php';

    return;
}

/**
 * Test if the actual data is equal to the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_equals($title, $actual, $expected)
{
    $result = false;

    if ($actual === $expected) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data is not equal to the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_not_equals($title, $actual, $expected)
{
    $result = false;

    if ($actual !== $expected) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data is greater than the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_greaterthan($title, $actual, $expected)
{
    $result = false;

    if ($actual > $expected) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data is greater than or equal to the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_greaterthanorequal($title, $actual, $expected)
{
    $result = false;

    if ($actual >= $expected) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data is less than the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_lessthan($title, $actual, $expected)
{
    $result = false;

    if ($actual < $expected) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data is less than or equal to the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_lessthanorequal($title, $actual, $expected)
{
    $result = false;

    if ($actual <= $expected) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data contains the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_contains($title, $actual, $expected)
{
    $result = false;

    if (regexp_match(preg_quote($expected, '/'), $actual)) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data not contains the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_not_contains($title, $actual, $expected)
{
    $result = false;

    if (!regexp_match(preg_quote($expected, '/'), $actual)) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data match the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_regexp($title, $actual, $expected)
{
    $result = false;

    if (regexp_match($expected, $actual)) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data not match the expected data.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_not_regexp($title, $actual, $expected)
{
    $result = false;

    if (!regexp_match($expected, $actual)) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data has the expected key.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_array_haskey($title, $actual, $expected)
{
    $result = false;

    if (array_key_exists($expected, $actual)) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data has not the expected key.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_array_not_haskey($title, $actual, $expected)
{
    $result = false;

    if (!array_key_exists($expected, $actual)) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data has the expected subset.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_array_subset($title, $actual, $expected, $strict = false)
{
    $result = false;

    if (in_array($expected, $actual, $strict)) {
        $result = true;
    }

    return test_result($title, $result);
}

/**
 * Test if the actual data has not the expected subset.
 *
 * @param string $title
 * @param mixed  $actual
 * @param mixed  $expected
 *
 * @return void
 */
function test_array_not_subset($title, $actual, $expected, $strict = false)
{
    $result = false;

    if (!in_array($expected, $actual, $strict)) {
        $result = true;
    }

    return test_result($title, $result);
}
