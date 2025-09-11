<?php

import('libs/modules/mail.php');
import('libs/modules/string.php');

/**
 * メールの送信
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param array  $headers
 * @param string $parameters
 * @param array  $files
 *
 * @return bool
 */
function service_mail_send($to, $subject, $message, $headers = [], $parameters = '', $files = [])
{
    // メール本文が1行1000バイトを超えると文字化けするので、256文字で強制改行させる
    $message = string_wordwrap($message, 256);

    $result = false;

    // メールを送信
    if ($GLOBALS['config']['mail_send'] === true) {
        $result = mail_send($to, $subject, $message, $headers, $parameters);
        if (!$result) {
            return $result;
        }
    }

    // メールを記録
    if ($GLOBALS['config']['mail_log'] === true) {
        $directory = MAIN_APPLICATION_PATH . 'mail/' . localdate('Ymd') . '/';

        if (!is_dir($directory)) {
            if (mkdir($directory, 0707)) {
                chmod($directory, 0707);
            }
        }

        $text  = '――――――――――――――――――――' . "\n";
        $text .= 'to: ' . $to . "\n";
        $text .= '――――――――――――――――――――' . "\n";
        $text .= 'subject: ' . $subject . "\n";
        $text .= '――――――――――――――――――――' . "\n";
        $text .= $message;

        $result = file_put_contents($directory . localdate('His') . '_' . $to . '.txt', $text);
        if (!$result) {
            return $result;
        }
    }

    return $result;
}
