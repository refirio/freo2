<?php

/*******************************************************************************

 Functions for SMTP

*******************************************************************************/

import('libs/modules/mail.php');

/**
 * SMTPでメールを送信（引数は mail_send() と同じで、先頭に接続設定を取る）
 *
 * @param array  $config  接続設定（host, port, encryption, username, password, timeout）
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param array  $headers
 * @param array  $files
 * @param string $envelope 送信元のメールアドレス（エンベロープ）。省略すると From ヘッダーのアドレス
 *
 * @return bool
 */
function smtp_send($config, $to, $subject, $message, $headers = [], $files = [], $envelope = null)
{
    // mail() で送るときと同じく組み立てる
    $mail = mail_build($to, $subject, $message, $headers, $files);

    // From ヘッダーのメールアドレス（「名前 <アドレス>」からアドレスを取り出す）
    if (preg_match('/<([^>]*)>/', $mail['headers']['From'], $matches)) {
        $from = $matches[1];
    } else {
        $from = trim($mail['headers']['From']);
    }

    // mail() では自動で付くヘッダーを足す
    $domain = substr(strrchr($from, '@'), 1);

    $lines = [
        'Date: ' . date('r'),
        'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . ($domain ? $domain : 'localhost') . '>',
        'To: ' . preg_replace('/\r|\n/', '', $mail['to']),
        'Subject: ' . $mail['subject'],
    ];
    foreach ($mail['headers'] as $key => $value) {
        $lines[] = $key . ': ' . $value;
    }

    return smtp_transfer($config, $envelope === null || $envelope === '' ? $from : $envelope, $mail['to'], implode("\r\n", $lines) . "\r\n\r\n" . $mail['body']);
}

/**
 * SMTPでメールを転送（組み立て済みのメールを、SMTPサーバーとやりとりして送る）
 *
 * @param array  $config 接続設定（host, port, encryption, username, password, timeout）
 * @param string $from   送信元のメールアドレス（エンベロープ）
 * @param string $to     宛先のメールアドレス（エンベロープ）
 * @param string $data   ヘッダーと本文
 *
 * @return bool
 */
function smtp_transfer($config, $from, $to, $data)
{
    smtp_error(null);

    // メールアドレスに改行や山括弧を含めさせない（コマンドの差し込みを防ぐ）
    if (preg_match('/[\r\n<>]/', $from . $to)) {
        smtp_error('メールアドレスが正しくありません。');

        return false;
    }

    $host       = isset($config['host']) ? $config['host'] : '';
    $port       = isset($config['port']) ? intval($config['port']) : 25;
    $encryption = isset($config['encryption']) ? $config['encryption'] : 'none';
    $username   = isset($config['username']) ? $config['username'] : '';
    $password   = isset($config['password']) ? $config['password'] : '';
    $timeout    = isset($config['timeout']) ? intval($config['timeout']) : 10;

    // 接続（ssl は接続時から暗号化する）
    $context = stream_context_create([
        'ssl' => [
            'peer_name'   => $host,
            'verify_peer' => true,
        ],
    ]);

    $socket = @stream_socket_client(($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        smtp_error('SMTPサーバーに接続できません。（' . ($errstr !== '' ? $errstr : smtp_last_warning()) . '）');

        return false;
    }

    stream_set_timeout($socket, $timeout);

    $result = smtp_session($socket, $host, $encryption, $username, $password, $from, $to, $data);
    if (!$result && smtp_error() === null) {
        smtp_error('SMTPサーバーとのやりとりに失敗しました。');
    }

    fclose($socket);

    return $result;
}

/**
 * SMTPのやりとり
 *
 * @param resource $socket
 * @param string   $host
 * @param string   $encryption
 * @param string   $username
 * @param string   $password
 * @param string   $from
 * @param string   $to
 * @param string   $data
 *
 * @return bool
 */
function smtp_session($socket, $host, $encryption, $username, $password, $from, $to, $data)
{
    $hostname = gethostname();
    if (!$hostname) {
        $hostname = 'localhost';
    }

    if (smtp_response($socket) !== 220) {
        return false;
    }

    // 挨拶（応答から、使える拡張機能を受け取る）
    $extensions = smtp_hello($socket, $hostname);
    if ($extensions === null) {
        return false;
    }

    // STARTTLS で暗号化し、挨拶をやり直す
    if ($encryption === 'tls') {
        if (smtp_command($socket, 'STARTTLS') !== 220) {
            return false;
        }
        if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            smtp_error('TLSで暗号化できません。（' . smtp_last_warning() . '）');

            return false;
        }

        $extensions = smtp_hello($socket, $hostname);
        if ($extensions === null) {
            return false;
        }
    }

    // 認証（サーバーが PLAIN に対応していれば PLAIN、そうでなければ LOGIN）
    if ($username !== '') {
        if (preg_match('/^AUTH\s.*\bPLAIN\b/im', $extensions)) {
            if (smtp_command($socket, 'AUTH PLAIN ' . base64_encode("\0" . $username . "\0" . $password)) !== 235) {
                return false;
            }
        } else {
            if (smtp_command($socket, 'AUTH LOGIN') !== 334) {
                return false;
            }
            if (smtp_command($socket, base64_encode($username)) !== 334) {
                return false;
            }
            if (smtp_command($socket, base64_encode($password)) !== 235) {
                return false;
            }
        }
    }

    // 送信元と宛先
    if (smtp_command($socket, 'MAIL FROM:<' . $from . '>') !== 250) {
        return false;
    }

    $code = smtp_command($socket, 'RCPT TO:<' . $to . '>');
    if ($code !== 250 && $code !== 251) {
        return false;
    }

    // 本文（改行を CRLF にそろえ、行頭の「.」は「..」にする）
    if (smtp_command($socket, 'DATA') !== 354) {
        return false;
    }

    $data = preg_replace('/\r\n|\r|\n/', "\r\n", $data);
    $data = preg_replace('/^\./m', '..', $data);
    if (substr($data, -2) !== "\r\n") {
        $data .= "\r\n";
    }

    if (smtp_command($socket, $data . '.') !== 250) {
        return false;
    }

    smtp_command($socket, 'QUIT');

    return true;
}

/**
 * SMTPの挨拶
 *
 * @param resource $socket
 * @param string   $hostname
 *
 * @return string|null 応答（拡張機能の一覧）、失敗したら null
 */
function smtp_hello($socket, $hostname)
{
    fwrite($socket, 'EHLO ' . $hostname . "\r\n");

    $response = '';
    if (smtp_response($socket, $response) !== 250) {
        return null;
    }

    return $response;
}

/**
 * SMTPのコマンドを送信
 *
 * @param resource $socket
 * @param string   $command
 *
 * @return int|null 応答コード
 */
function smtp_command($socket, $command)
{
    fwrite($socket, $command . "\r\n");

    return smtp_response($socket);
}

/**
 * SMTPの応答を受信
 *
 * @param resource $socket
 * @param string   $response 応答の本文（各行の4文字目以降）
 *
 * @return int|null 応答コード、受信できなかったら null
 */
function smtp_response($socket, &$response = null)
{
    $response = '';
    $code     = null;

    // 複数行の応答は「250-...」が続き、最後の行が「250 ...」になる
    while (($line = fgets($socket, 515)) !== false) {
        $code      = intval(substr($line, 0, 3));
        $response .= substr($line, 4);

        if (substr($line, 3, 1) !== '-') {
            break;
        }
    }

    if ($code === null) {
        smtp_error('SMTPサーバーから応答がありません。');
    } elseif ($code >= 400) {
        smtp_error('SMTPサーバーがエラーを返しました。（' . $code . ' ' . trim($response) . '）');
    }

    return $code;
}

/**
 * 直前の PHP の警告（証明書の検証の失敗など、@ で抑えた警告の理由）
 *
 * @return string
 */
function smtp_last_warning()
{
    $error = error_get_last();

    return $error === null ? '' : $error['message'];
}

/**
 * SMTPのエラーを記録・取得
 *
 * @param string|null $message 記録するメッセージ（省略すると取得のみ）
 *
 * @return string|null 最後のエラー
 */
function smtp_error($message = false)
{
    static $error = null;

    if ($message !== false) {
        $error = $message;
    }

    return $error;
}
