<?php
include 'Wish/WishClient.php';
include 'EUB/EUBOrders.php';
use Wish\WishClient;
use Wish\Model\WishOrder;
use Wish\Model\WishShippingDetail;
use EUBOrders;

echo test;

$access_code = $_GET['code'];
echo $access_code;


$client_id = urlencode('55f8363cbce30916c8d92a0e');
$client_secret = urlencode('aa9b618b09444410a0752968891c8542');

$code = $access_code;


$redirect_uri = urlencode('https://localhost/zendphp1/index.php');


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



/*
 * get the unfulfilled orders;
 * */
/* $get_unfulfilled_order_url = "https://merchant.wish.com/api/v2/order/get-fulfill";
$start = 0;
$limit = 50;


$get_unfulfilled_order_url = sprintf(
    "https://merchant.wish.com/api/v2/order/get-fulfill?&start=%s&limit=%s&access_token=%s", $start, $limit, $access_token);

$order_context = stream_context_create(array(
    'http' => array(
        'method'        => 'POST',
        'ignore_errors' => true,
    ),
));

// Send the request
$order_response = file_get_contents($get_unfulfilled_order_url, TRUE, $order_context);
echo $order_response;
echo "\n";
 */

//Get an array of all unfufilled orders since January 20, 2010
$client = new WishClient($access_token,'prod');
$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince('2010-01-20');
print("\n orders count:".count($unfulfilled_orders)." changed orders.\n");
//var_dump($unfulfilled_orders);
$orders_count = count($unfulfilled_orders);
for($i=0;i<$orders_count;$i++){
    $cur_order = $unfulfilled_orders[$i];
    echo $cur_order->sku;
    $shippingDetail = $cur_order->ShippingDetail;
    if (strcmp($shippingDetail->country,'US') == 0){
        $eub = new EUBOrders();
        $eub->getTrackingID();
    }
}

?>