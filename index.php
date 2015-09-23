<?php
echo test;

$access_code = $_GET['code'];
echo $access_code;


$client_id = urlencode('55f8363cbce30916c8d92a0e');
$client_secret = urlencode('aa9b618b09444410a0752968891c8542');

$code = $access_code;


$redirect_uri = urlencode('https://localhost/zendphp1/index.php');

$url = sprintf(
    "https://merchant.wish.com/api/v2/oauth/access_token?&client_id=%s&client_secret=%s&code=%s&redirect_uri=%s&grant_type=authorization_code", $client_id, $client_secret, $code, $redirect_uri);

$context = stream_context_create(array(
    'http' => array(
        'method'        => 'POST',
        'ignore_errors' => true,
    ),
));

// Send the request
$response = file_get_contents($url, TRUE, $context);
echo $response;
echo "\n";


//get the access token and refresh token



?>