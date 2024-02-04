<?php

/*********************************************************************

 Functions for Validator

*********************************************************************/

/**
 * 必須の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_required($data)
{
    if ($data === '') {
        return false;
    } else {
        return true;
    }
}

/**
 * 最小長の検証
 *
 * @param string $data
 * @param int    $min
 *
 * @return bool
 */
function validator_min_length($data, $min)
{
    if (mb_strlen($data, MAIN_INTERNAL_ENCODING) < $min) {
        return false;
    } else {
        return true;
    }
}

/**
 * 最大長の検証
 *
 * @param string $data
 * @param int    $max
 *
 * @return bool
 */
function validator_max_length($data, $max)
{
    if (mb_strlen($data, MAIN_INTERNAL_ENCODING) > $max) {
        return false;
    } else {
        return true;
    }
}

/**
 * 指定された範囲の長さの検証
 *
 * @param string $data
 * @param int    $min
 * @param int    $max
 *
 * @return bool
 */
function validator_between($data, $min, $max)
{
    if (mb_strlen($data, MAIN_INTERNAL_ENCODING) < $min || mb_strlen($data, MAIN_INTERNAL_ENCODING) > $max) {
        return false;
    } else {
        return true;
    }
}

/**
 * 英字の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_alpha($data)
{
    if (!preg_match('/^[_a-zA-Z]+$/', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 数字の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_numeric($data)
{
    if (!preg_match('/^\d+$/', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 数値（マイナスや少数も許可）の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_decimal($data)
{
    if (!is_numeric($data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 英数字の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_alpha_numeric($data)
{
    if (!preg_match('/^\w+$/', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 英数字・アンダーバー・ダッシュの検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_alpha_dash($data)
{
    if (!preg_match('/^[\w\-]+$/', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 等しい値の検証
 *
 * @param mixed $data
 * @param mixed $value
 *
 * @return bool
 */
function validator_equals($data, $value)
{
    if ($data !== $value) {
        return false;
    } else {
        return true;
    }
}

/**
 * 以上の検証
 *
 * @param int $data
 * @param int $min
 *
 * @return bool
 */
function validator_min($data, $min)
{
    if ($data < $min) {
        return false;
    } else {
        return true;
    }
}

/**
 * 以下の検証
 *
 * @param int $data
 * @param int $max
 *
 * @return bool
 */
function validator_max($data, $max)
{
    if ($data > $max) {
        return false;
    } else {
        return true;
    }
}

/**
 * 指定された範囲の数値の検証
 *
 * @param int $data
 * @param int $min
 * @param int $max
 *
 * @return bool
 */
function validator_range($data, $min, $max)
{
    if ($data < $min || $data > $max) {
        return false;
    } else {
        return true;
    }
}

/**
 * 未入力もしくはホワイトスペースの検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_blank($data)
{
    if (!preg_match('/^\s*$/', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * ブール値（true, false, 1, 0）の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_boolean($data)
{
    if (!is_bool($data) && !preg_match('/^(0|1)$/', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 指定されたリストの検証
 *
 * @param  string|array $data
 * @param  array        $list
 *
 * @return bool
 */
function validator_list($data, $list)
{
    if (!is_array($data)) {
        $data = array($data);
    }

    foreach ($data as $datum) {
        $flag = false;
        foreach ($list as $key => $value) {
            if ($datum == $key) {
                $flag = true;
            }
        }
        if ($flag === false) {
            return false;
        }
    }

    return true;
}

/**
 * カスタム正規表現での検証
 *
 * @param string $data
 * @param string $regexp
 *
 * @return bool
 */
function validator_regexp($data, $regexp)
{
    if (!preg_match('/' . $regexp . '/', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 日付の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_date($data)
{
    if (!preg_match('/^(\d\d\d\d)\-(\d\d)\-(\d\d)$/', $data, $matches)) {
        return false;
    } elseif (!checkdate($matches[2], $matches[3], $matches[1])) {
        return false;
    } else {
        return true;
    }
}

/**
 * 時間の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_time($data)
{
    if (!preg_match('/^[0-2][0-9]\:[0-5][0-9]\:[0-5][0-9]$/', $data)) {
        return false;
    } else {
        return true;
    }

    return true;
}

/**
 * 日時の検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_datetime($data)
{
    list($date, $time) = explode(' ', $data);

    if (!validator_date($date) || !validator_time($time)) {
        return false;
    } else {
        return true;
    }
}

/**
 * メールアドレスの検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_email($data)
{
    if (!preg_match('/^[^@\s]+@[^@\s]+$/', $data) || !validator_max_length($data, 256)) {
        return false;
    } else {
        return true;
    }
}

/**
 * URLの検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_url($data)
{
    if (!preg_match('/^https?\:\/\//', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * ひらがなの検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_hiragana($data)
{
    if (!preg_match('/^[ぁ-ん]+$/u', $data)) {
        return false;
    } else {
        return true;
    }
}

/**
 * カタカナの検証
 *
 * @param string $data
 *
 * @return bool
 */
function validator_katakana($data)
{
    if (!preg_match('/^[ァ-ヶー　]+$/u', $data)) {
        return false;
    } else {
        return true;
    }
}
