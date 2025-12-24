<?php

/*******************************************************************************

 Functions for reCAPTCHA

*******************************************************************************/

/**
 * reCAPTCHAを読み込み
 *
 * @param string $site_key
 *
 * @return void
 */
function recaptcha_import($site_key)
{
    echo "<script src=\"https://www.google.com/recaptcha/api.js?render=" . t($site_key, true) . "\"></script>\n";
    echo "<script>\n";
    echo "grecaptcha.ready(function() {\n";
    echo "    grecaptcha.execute('" . t($site_key, true) . "', {action: 'homepage'}).then(function(token) {\n";
    echo "        var recaptchaResponse = document.getElementById('g-recaptcha-response');\n";
    echo "        recaptchaResponse.value = token;\n";
    echo "    });\n";
    echo "});\n";
    echo "</script>\n";
}

/**
 * reCAPTCHAを入力
 *
 * @return void
 */
function recaptcha_input()
{
    echo "<input type=\"hidden\" name=\"g-recaptcha-response\" id=\"g-recaptcha-response\">\n";
}

/**
 * reCAPTCHAを確認
 *
 * @param string $secret_key
 * @param number $score
 *
 * @return bool
 */
function recaptcha_verify($secret_key, $score = 0.5)
{
    $recaptcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : null;
    if (!$recaptcha){
        return false;
    }

    $response = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha), true);
    if (intval($response['success']) === 1 && $response['score'] >= $score) {
        return true;
    } else {
        return false;
    }
}
