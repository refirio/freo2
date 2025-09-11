<?php

/*******************************************************************************

 Functions for S3

*******************************************************************************/

import('_aws-sdk/vendor/autoload.php');
//import('libs/vendor/autoload.php');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

/**
 * ストレージに接続
 *
 * @param string|null $config
 *
 * @return array
 */
function s3_init($config = [])
{
    static $client = null;
    static $setting = [];

    if (empty($setting)) {
        $setting = $config;
    }
    if (!is_null($client)) {
        return [$client, $setting];
    }

    try {
        $client = new S3Client([
            'credentials' => [
                'key'    => $setting['credential_key'],
                'secret' => $setting['credential_secret'],
            ],
            'region'      => $setting['region'],
            'version'     => $setting['version'],
        ]);
    } catch (S3Exception $e) {
        error('S3Exception: ' . $e->getMessage());
    } catch (Exception $e) {
        error('Exception: ' . $e->getMessage());
    }

    return [$client, $setting];
}

/**
 * オブジェクトを確認
 *
 * @param string $key
 *
 * @return bool
 */
function s3_exist($key)
{
    list($client, $setting) = s3_init();

    try {
        $result = $client->doesObjectExist($setting['bucket'], $key);
    } catch (S3Exception $e) {
        error('S3Exception: ' . $e->getMessage());
    } catch (Exception $e) {
        error('Exception: ' . $e->getMessage());
    }

    return $result;
}

/**
 * オブジェクトを取得
 *
 * @param string $key
 *
 * @return bool
 */
function s3_get($key)
{
    list($client, $setting) = s3_init();

    try {
        $result = $client->getObject([
            'Bucket' => $setting['bucket'],
            'Key'    => $key,
        ]);
        $result = $result['Body'];
    } catch (S3Exception $e) {
        error('S3Exception: ' . $e->getMessage());
    } catch (Exception $e) {
        error('Exception: ' . $e->getMessage());
    }

    return $result;
}

/**
 * オブジェクトを保存
 *
 * @param string $key
 * @param string $body|null
 *
 * @return bool
 */
function s3_put($key, $body = null)
{
    list($client, $setting) = s3_init();

    if (preg_match('/\/$/', $key)) {
        // do nothing.
    } else {
        try {
            $result = $client->putObject(array(
                'Bucket' => $setting['bucket'],
                'Key'    => $key,
                'Body'   => $body,
            ));
        } catch (S3Exception $e) {
            error('S3Exception: ' . $e->getMessage());
        } catch (Exception $e) {
            error('Exception: ' . $e->getMessage());
        }
    }

    return $result;
}

/**
 * オブジェクトを削除
 *
 * @param string $key
 *
 * @return bool
 */
function s3_remove($key)
{
    list($client, $setting) = s3_init();

    if (preg_match('/\/$/', $key)) {
        try {
            $result = $client->listObjects([
                'Bucket'    => $setting['bucket'],
                'Prefix'    => $key,
                'Delimiter' => '/',
            ]);

            foreach ($result['Contents'] as $content) {
                s3_remove($content['Key']);
            }
        } catch (S3Exception $e) {
            error('S3Exception: ' . $e->getMessage());
        } catch (Exception $e) {
            error('Exception: ' . $e->getMessage());
        }

        $result = true;
    } else {
        try {
            $result = $client->deleteObject(array(
                'Bucket' => $setting['bucket'],
                'Key'    => $key,
            ));
        } catch (S3Exception $e) {
            error('S3Exception: ' . $e->getMessage());
        } catch (Exception $e) {
            error('Exception: ' . $e->getMessage());
        }
    }

    return $result;
}
