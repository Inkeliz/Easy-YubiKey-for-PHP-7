<?php
/*
 * Easy Yubikey for PHP 7
 * @author Inkeliz
 *
 * LICENSE: MIT LICENSE
 */

function newYubikey($client_id, $secret_key, $otp){

    return [
        'client_id' => $client_id,
        'secret_key' => base64_decode($secret_key),
        'otp' => strtolower(trim($otp)),
        'nonce' => bin2hex(random_bytes(20))
    ];

}

function isYubikey(array $ykey){

    $return = false;
    $otp = $ykey['otp'];

    if(strlen($otp) >= 32 || strlen($otp) <= 48){
        $return = true;
    }

    return $return;

}

function compareYubikey($ykey, $idCompare){

    $return = false;

    if(isYubikey($ykey)) {

        // Check if idCompare is hashed or not
        $HashedCompare = substr($idCompare, 0, 4) === '$2y$';

        // Check if have more than one idComare (array)
        $idCompare = is_array($idCompare) === false ? [$idCompare] : $idCompare;

        // Get the id from OTP key
        $idKey = getYubikey($ykey);

        for($loop = 0; $loop < count($idCompare); $loop++){

            if ($HashedCompare === true && password_verify($idKey, $idCompare[$loop]) || ($HashedCompare === false && $idKey === $idCompare[$loop])) {
                $return = true;
            }

        }

    }

    return $return;

}

function getYubikey(array $ykey){

    return substr($ykey['otp'], 0, 12);

}

function hashYubikey(array $ykey, $hash = PASSWORD_DEFAULT){

    $idKey = getYubikey($ykey);
    return password_hash($idKey, $hash);

}

function curlYubikey(array $url, $https){

    $return = false;

    $multi = curl_multi_init();
    $curls = [];

    foreach ($url as $index => $request) {
        $curls[$index] = curl_init();
        curl_setopt_array($curls[$index], array(
            CURLOPT_URL => $request,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYHOST => $https === 'https://' ? 2 : 0,
            CURLOPT_SSL_VERIFYPEER => $https === 'https://' ? 1 : 0,
            CURLOPT_FOLLOWLOCATION => 0,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_DNS_CACHE_TIMEOUT, '3600'
        ));
        curl_multi_add_handle($multi, $curls[$index]);
    }

    do {
        while ((curl_multi_exec($multi, $active)) == CURLM_CALL_MULTI_PERFORM);
        while ($info = curl_multi_info_read($multi)) {

            if ($info['result'] === CURLE_OK && curl_getinfo($info['handle'])['http_code'] === 200) {
                $return = curl_multi_getcontent($info['handle']);
                $active = 0;
            }

        }
    }while($active > 0);

    return $return;

}

function checkYubikey(array $ykey, $protocol = 'https://', $hosts = ['api.yubico.com/wsapi/2.0/verify', 'api2.yubico.com/wsapi/2.0/verify', 'api3.yubico.com/wsapi/2.0/verify', 'api4.yubico.com/wsapi/2.0/verify', 'api5.yubico.com/wsapi/2.0/verify']){

    $return = false;

    $params = ['id' => $ykey['client_id'], 'nonce' => $ykey['nonce'], 'otp' => $ykey['otp']];
    ksort($params);
    $params = http_build_query($params);

    $sig = str_replace('%3A', ':', $params);
    $sig = utf8_encode($sig);
    $sig = hash_hmac('sha1', $sig , $ykey['secret_key'], true);
    $sig = base64_encode($sig);
    $sig = preg_replace('/\+/', '%2B', $sig);


    if(is_array($hosts) === false){
        $hosts = explode(',', $hosts);
    }

    foreach($hosts as $host){
        $url[] = $protocol . $host . (strpos($host, '?') !== false ? '&' : '?') . $params . '&h=' . $sig;
    }


    if(isset($url) && $curl = curlYubikey($url, $protocol)){

        if(preg_match("/status=OK/", $curl) && preg_match("/otp=".$ykey['otp']."/", $curl) && preg_match("/nonce=".$ykey['nonce']."/", $curl)){

            $return = true;

        }

    }

    return $return;

}

