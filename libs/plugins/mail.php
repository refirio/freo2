<?php

/*******************************************************************************

 Functions for Mail

*******************************************************************************/

import('libs/plugins/file.php');

/**
 * Send encoded mail.
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
function mail_send($to, $subject, $message, $headers = array(), $parameters = '', $files = array())
{
    $subject = mb_convert_kana(unify($subject), 'KV', MAIN_INTERNAL_ENCODING);
    $message = mb_convert_kana(unify($message), 'KV', MAIN_INTERNAL_ENCODING);

    $subject = mb_convert_encoding($subject, 'JIS', MAIN_INTERNAL_ENCODING);
    $message = mb_convert_encoding($message, 'JIS', MAIN_INTERNAL_ENCODING);

    $subject = '=?iso-2022-jp?B?' . base64_encode($subject) . '?=';

    if (empty($files)) {
        $boundary = null;
    } else {
        $boundary = rand_string();
    }

    if (empty($files)) {
        $body = $message;
    } else {
        $body  = "--$boundary\n";
        $body .= "Content-Type: text/plain; charset=\"iso-2022-jp\"\n";
        $body .= "Content-Transfer-Encoding: 7bit\n";
        $body .= "\n";
        $body .= "$message\n";

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $filename = basename($file);

            $body .= "\n";
            $body .= "--$boundary\n";
            $body .= "Content-Type: " . file_mimetype($file) . "; name=\"$filename\"\n";
            $body .= "Content-Disposition: attachment; filename=\"$filename\"\n";
            $body .= "Content-Transfer-Encoding: base64\n";
            $body .= "\n";
            $body .= chunk_split(base64_encode(file_get_contents($file))) . "\n";
        }

        $body .= '--' . $boundary . '--';
    }

    if (!isset($headers['X-Mailer'])) {
        $headers['X-Mailer'] = 'PHP';
    }
    if (!isset($headers['From'])) {
        $headers['From'] = '"From" <from@example.com>';
    }
    if (!isset($headers['MIME-Version'])) {
        $headers['MIME-Version'] = '1.0';
    }
    if (!isset($headers['Content-Type'])) {
        if (empty($files)) {
            $headers['Content-Type'] = 'text/plain; charset="iso-2022-jp"';
        } else {
            $headers['Content-Type'] = 'multipart/mixed; boundary="' . $boundary . '"';
        }
    }
    if (!isset($headers['Content-Transfer-Encoding'])) {
        $headers['Content-Transfer-Encoding'] = '7bit';
    }

    $header = null;
    foreach ($headers as $key => $value) {
        if ($header) {
            $header .= "\n";
        }

        $key   = regexp_replace('(\r|\n)', '', $key);
        $value = regexp_replace('(\r|\n)', '', $value);

        $header .= $key . ': ' . $value;
    }

    return mail($to, $subject, $body, $header, $parameters);
}
