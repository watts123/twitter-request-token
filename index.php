<?php
function index($appKey,$appSecret){
    $oauthParams = [
        'oauth_callback' => 'https://www.xxx.com',//callback url
        'oauth_consumer_key' => $appKey,
        'oauth_nonce' => md5(uniqid()),
        'oauth_signature_method' => 'HMAC-SHA1',
        'oauth_timestamp' => time(),
        'oauth_version' => '1.0',
    ];
    $baseURI = 'https://api.twitter.com/oauth/request_token';
    $baseString = buildBaseString($baseURI, $oauthParams); // build the base string
    $consumerSecret = $appSecret; // consumer secret from your twitter app: https://apps.twitter.com
    $compositeKey   = getCompositeKey($consumerSecret, null); // first request, no request token yet
    $oauthParams['oauth_signature'] = base64_encode(hash_hmac('sha1', $baseString, $compositeKey, true)); // sign the base string
    $response = sendRequest($oauthParams, $baseURI);
    if($response['success']){
        return $response['response'];
    }else{
        return false;
    }
}
function getCompositeKey($consumerSecret, $requestToken){
    return rawurlencode($consumerSecret) . '&' . rawurlencode($requestToken);
}
function buildBaseString($baseURI, $oauthParams){
    $baseStringParts = [];
    ksort($oauthParams);
    foreach($oauthParams as $key => $value){
        $baseStringParts[] = "$key=" . rawurlencode($value);
    }
    return 'POST&' . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $baseStringParts));
}
function buildAuthorizationHeader($oauthParams){
    $authHeader = 'Authorization: OAuth ';
    $values = [];
    foreach($oauthParams as $key => $value) {
        $values[] = "$key=" . rawurlencode( $value ) . "";
    }
    $authHeader .= implode(', ', $values);
    return $authHeader;
}
function sendRequest($oauthParams, $baseURI,$param = ''){
    $header = [buildAuthorizationHeader($oauthParams), 'Expect:'];
    $options = [
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_HEADER => false,
        CURLOPT_URL => $baseURI,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
    ];
    if($param){
        $options[CURLOPT_POSTFIELDS] = $param;
    }
    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $httpInfo = curl_getinfo($ch);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);
    if($httpInfo['http_code'] <> 200) {
        return [
            'success' => false,
            'message' => $error,
            'code' => $errno,
            'http_info' => (object) $httpInfo,
        ];
    }
    return [
        'success' => true,
        'message' => false,
        'code' => false,
        'response' => $response,
    ];
}
$appKey    = 'xxxxxxxx';      // consumer key from your twitter app: https://apps.twitter.com
$appSecret = 'xxxxxxxx';      // consumer key from your twitter app: https://apps.twitter.com
echo index($appKey,$appSecret);