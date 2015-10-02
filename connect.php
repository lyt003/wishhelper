<?php

use Wish\WishClient;
include 'Wish/WishClient.php';
/**
 * Copyright 2014 Wish.com, ContextLogic or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


require_once './vendor/autoload.php';

/**
 * get the access token
 *
 */
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
// json data: {"message":"","code":0,"data":{"expiry_time":1446073198,"token_type ":"access_token","access_token":"c10a316adfb449ffb321984aee91fe50","expires_in":2591918,"merchant_user_id":"535bb01471795166f8be12d0","refresh_token":"3cdbddd6c23249d39ab951d58b454a93"}}
$response = json_decode($response);
$access_obj = $response->{'data'};
$access_token = '0';
$refresh_token = '0';
foreach($access_obj as $k=>$v){
    echo 'key  '.$k.'  value:'.$v;
    if ($k == 'access_token'){
        $access_token = $v;
    }
    if($k == 'refresh_token'){
        $refresh_token = $v;
    }
}
echo "\n";
echo $access_token;



$key = 'JHBia2RmMiQxMDAkTG1WTUNTRkVLSVdRa3ZJZXcvZ2ZndyRoM1pNL3BoQmtmZG8vbnlRWFl0WE1XWnozMjA=';
$client = new WishClient($access_token,'sandbox');

print "RESULT: ".$client->authTest();

