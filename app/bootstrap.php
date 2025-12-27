<?php

/**
 * Return the value for config.
 *
 * @param string $key
 * @param mixed  $default|null
 *
 * @return mixed|null
 */
function app_config($key, $default)
{
    return defined($key) ? constant($key) : $default;
}

/**
 * Return the dataset from association.
 *
 * @param string $get_key
 * @param array  $get_value
 * @param string $set_keykey
 * @param string $set_value|null
 *
 * @return array
 */
function app_dataset($get_key, $get_value, $set_key, $set_value)
{
    $get_keys = explode('.', $get_key);
    $set_keys = explode('.', $set_key);

    $get_key1 = $get_keys[0];
    $get_key2 = isset($get_keys[1]) ? $get_keys[1] : null;

    $set_key1 = $set_keys[0];
    $set_key2 = isset($set_keys[1]) ? $set_keys[1] : null;

    $data_sets = model('select_' . $get_key1, [
        'where' => $get_key1 . '.' . $get_key2 . ' IN(' . implode(',', array_map('db_escape', $get_value)) . ')',
    ], [
        'associate' => true,
    ]);

    $dataset = [];
    foreach ($data_sets as $data_set) {
        if ($set_value) {
            $data = $data_set[$set_value];
        } else {
            $data = $data_set;
        }
        if ($set_key2) {
            $dataset[$data_set[$set_key1]][$data_set[$set_key2]] = $data;
        } else {
            $dataset[$data_set[$set_key1]][] = $data;
        }
    }

    return $dataset;
}

/**
 * Return the class for badge.
 *
 * @param string $key
 * @param string $value
 *
 * @return string
 */
function app_badge($key, $value)
{
    $text_color = 'light';
    $bg_color   = 'secondary';

    if ($value === 1) {
        $bg_color = 'success';
    }
    if ($value === 0) {
        $text_color = 'dark';
        $bg_color   = 'warning';
    }

    if ($value === 'yes') {
        $bg_color = 'success';
    }
    if ($key === 'public') {
        if ($value === 'all' || $value === 'user' || $value === 'attribute' || $value === 'password') {
            $bg_color = 'success';
        }
    }
    if ($key === 'kind' || $key === 'authority_id') {
        $text_color = 'dark';
        $bg_color   = 'info';
    }
    if ($key === 'status') {
        if ($value === 'closed') {
            $bg_color = 'success';
        } else {
            $text_color = 'dark';
            $bg_color   = 'warning';
        }
    }
    if ($key === 'installed') {
        if ($value === 0) {
            $text_color = 'light';
            $bg_color   = 'secondary';
        }
    }

    return 'rounded-pill text-' . $text_color . ' bg-' . $bg_color;
}
