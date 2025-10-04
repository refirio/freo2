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
