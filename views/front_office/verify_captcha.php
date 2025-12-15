<?php
function verifyRecaptcha($token) {
    $secret = RECAPTCHA_SECRET_KEY;
    $remoteIp = $_SERVER['REMOTE_ADDR'] ?? null;

    if (function_exists('curl_version')) {
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $remoteIp
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $response = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify'
            . '?secret=' . urlencode($secret)
            . '&response=' . urlencode($token)
            . '&remoteip=' . urlencode($remoteIp)
        );
    }

    if ($response === false) return false;
    $data = json_decode($response, true);
    return isset($data['success']) && $data['success'] === true;
}