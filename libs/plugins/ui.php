<?php

/*******************************************************************************

 Functions for UI

*******************************************************************************/

/**
 * Get a pager.
 *
 * @param array $option
 *
 * @return array
 */
function ui_pager($option = array())
{
    if (!isset($option['key'])) {
        $option['key'] = 'page';
    }
    if (!isset($option['now'])) {
        $option['now'] = $_GET[$option['key']];
    }
    if (!isset($option['count'])) {
        $option['count'] = 0;
    }
    if (!isset($option['size'])) {
        $option['size'] = 10;
    }
    if (!isset($option['width'])) {
        $option['width'] = 5;
    }
    if (!isset($option['query'])) {
        $option['query'] = '';
    }
    if (!isset($option['delimiter'])) {
        $option['delimiter'] = ' ';
    }

    if (!isset($option['label']['first'])) {
        $option['label']['first'] = '&lt;&lt;';
    }
    if (!isset($option['label']['last'])) {
        $option['label']['last'] = '&gt;&gt;';
    }
    if (!isset($option['label']['back'])) {
        $option['label']['back'] = '&lt;';
    }
    if (!isset($option['label']['next'])) {
        $option['label']['next'] = '&gt;';
    }

    if (isset($option['attribute']['link'])) {
        $option['attribute']['link'] = ' ' . $option['attribute']['link'];
    } else {
        $option['attribute']['link'] = '';
    }
    if (isset($option['attribute']['text'])) {
        $option['attribute']['text'] = ' ' . $option['attribute']['text'];
    } else {
        $option['attribute']['text'] = '';
    }

    if ($option['width'] % 2 === 0 || $option['width'] < 5) {
        error('ui_pager: Please specify the five or more odd to \'width\'');
    }

    $option = array(
        'key'       => $option['key'],
        'now'       => intval($option['now']),
        'count'     => intval($option['count']),
        'size'      => intval($option['size']),
        'width'     => intval($option['width']),
        'query'     => $option['query'],
        'delimiter' => $option['delimiter'],
        'label'     => $option['label'],
        'attribute' => $option['attribute'],
    );

    $pager = array(
        'first' => '',
        'last'  => '',
        'back'  => '',
        'next'  => '',
        'pages' => '',
        'all'   => '',
    );

    $all = ceil($option['count'] / $option['size']);

    if ($option['now'] > 1) {
        $pager['first'] = '<a href="' . $option['query'] . $option['key'] . '=1"' . $option['attribute']['link'] . '>' . $option['label']['first'] . '</a>';
    } else {
        $pager['first'] = $option['attribute']['text'] ? '<span' . $option['attribute']['text'] . '>' . $option['label']['first'] . '</span>' : $option['label']['first'];
    }

    if ($option['now'] < $all) {
        $pager['last'] = '<a href="' . $option['query'] . $option['key'] . '=' . $all . '"' . $option['attribute']['link'] . '>' . $option['label']['last'] . '</a>';
    } else {
        $pager['last'] = $option['attribute']['text'] ? '<span' . $option['attribute']['text'] . '>' . $option['label']['last'] . '</span>' : $option['label']['last'];
    }

    if ($option['now'] > 1) {
        $pager['back'] = '<a href="' . $option['query'] . $option['key'] . '=' . ($option['now'] - 1) . '"' . $option['attribute']['link'] . '>' . $option['label']['back'] . '</a>';
    } else {
        $pager['back'] = $option['attribute']['text'] ? '<span' . $option['attribute']['text'] . '>' . $option['label']['back'] . '</span>' : $option['label']['back'];
    }

    if ($option['now'] < $all) {
        $pager['next'] = '<a href="' . $option['query'] . $option['key'] . '=' . ($option['now'] + 1) . '"' . $option['attribute']['link'] . '>' . $option['label']['next'] . '</a>';
    } else {
        $pager['next'] = $option['attribute']['text'] ? '<span' . $option['attribute']['text'] . '>' . $option['label']['next'] . '</span>' : $option['label']['next'];
    }

    $pager['pages'] = array();

    $width = $option['width'];
    $side  = intval($option['width'] / 2) + 1;

    if ($all < $option['width']) {
        $from = 1;
        $to   = $all;
    } elseif ($option['now'] <= $side) {
        $from = 1;
        $to   = $from + $side + floor($side / 2);
    } elseif ($option['now'] > $all - $side) {
        $from = $all - $side - floor($side / 2);
        $to   = $all;
    } else {
        $from = $option['now'] - $side + floor($side / 2);
        $to   = $from + $side + floor($side / 2);
    }

    for ($i = $from; $i <= $to; $i++) {
        if ($option['now'] == $i) {
            $pager['pages'][] = $option['attribute']['text'] ? '<span' . $option['attribute']['text'] . '>' . $i . '</span>' : $i;
        } else {
            $pager['pages'][] = '<a href="' . $option['query'] . $option['key'] . '=' . $i . '"' . $option['attribute']['link'] . '>' . $i . '</a>';
        }
    }

    if (empty($pager['pages'])) {
        $pager['pages'][] = 1;
    }

    $pager['all'] = $pager['first'] . $option['delimiter'] . $pager['back'] . $option['delimiter'] . implode($option['delimiter'], $pager['pages']) . $option['delimiter'] . $pager['next'] . $option['delimiter'] . $pager['last'];

    return $pager;
}

/**
 * Get a form parts for time/date.
 *
 * @param mixed  $timestamp
 * @param string $type
 * @param array  $option
 *
 * @return string
 */
function ui_datetime($timestamp, $type = '', $option = array())
{
    if (!isset($option['format'])) {
        $option['format'] = '%01d';
    }
    if (!isset($option['prefix'])) {
        $option['prefix'] = '';
    }
    if (!isset($option['suffix'])) {
        $option['suffix'] = '';
    }
    if (!isset($option['from'])) {
        $option['from'] = 0;
    }
    if (!isset($option['to'])) {
        $option['to'] = 0;
    }
    if (!isset($option['step'])) {
        $option['step'] = 1;
    }

    switch ($type) {
        case 'year':
            $value = $timestamp      ? intval(localdate('Y', $timestamp)) : null;
            $from  = $option['from'] ? $option['from']                    : date('Y') - 10;
            $to    = $option['to']   ? $option['to']                      : date('Y') + 10;
            break;
        case 'month':
            $value = $timestamp      ? intval(localdate('m', $timestamp)) : null;
            $from  = $option['from'] ? $option['from']                    : 1;
            $to    = $option['to']   ? $option['to']                      : 12;
            break;
        case 'day':
            $value = $timestamp      ? intval(localdate('d', $timestamp)) : null;
            $from  = $option['from'] ? $option['from']                    : 1;
            $to    = $option['to']   ? $option['to']                      : 31;
            break;
        case 'hour':
            $value = $timestamp      ? intval(localdate('H', $timestamp)) : null;
            $from  = $option['from'] ? $option['from']                    : 0;
            $to    = $option['to']   ? $option['to']                      : 23;
            break;
        case 'minute':
            $value = $timestamp      ? intval(localdate('i', $timestamp)) : null;
            $from  = $option['from'] ? $option['from']                    : 0;
            $to    = $option['to']   ? $option['to']                      : 59;
            break;
        case 'second':
            $value = $timestamp      ? intval(localdate('s', $timestamp)) : null;
            $from  = $option['from'] ? $option['from']                    : 0;
            $to    = $option['to']   ? $option['to']                      : 59;
            break;
        default:
            return '<option value="">ui_datetime: Incorrect value was specified.</option>';
    }

    if ($regexp = regexp_match('^(\d\d\d\d)\-(\d\d)\-(\d\d)', $timestamp)) {
        $year  = intval($regexp[1]);
        $month = intval($regexp[2]);
        $day   = intval($regexp[3]);

        if (!checkdate($month, $day, $year)) {
            if ($regexp = regexp_match('(\d\d)\:(\d\d)\:(\d\d)$', $timestamp)) {
                $hour   = intval($regexp[1]);
                $minute = intval($regexp[2]);
                $second = intval($regexp[3]);
            } else {
                $hour   = 0;
                $minute = 0;
                $second = 0;
            }

            switch ($type) {
                case 'year':
                    $value = $year;
                    break;
                case 'month':
                    $value = $month;
                    break;
                case 'day':
                    $value = $day;
                    break;
                case 'hour':
                    $value = $hour;
                    break;
                case 'minute':
                    $value = $minute;
                    break;
                case 'second':
                    $value = $second;
                    break;
                default:
            }
        }
    }

    $datetime = '';
    for ($i = $from; $i <= $to; $i += $option['step']) {
        $datetime .= '<option value="' . sprintf('%02d', $i) . '"' . (($value !== null && $i == $value) ? ' selected="selected"' : '') . '>' . $option['prefix'] . sprintf($option['format'], $i) . $option['suffix'] . '</option>';
    }

    return $datetime;
}
