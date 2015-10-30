<?php
use mysql\dbhelper;
use Wish\WishClient;

include 'mysql/dbhelper.php';
include 'Wish/WishClient.php';

header ( "Content-Type: text/html;charset=utf-8" );

$accountid = $_POST ['accountid'];
$access_token = $_POST ['token'];

$dbhelper = new dbhelper ();
$result = $dbhelper->getOrdersNotUploadTracking ( $accountid );
echo $result;
var_dump ( $result );

while ( $row = mysql_fetch_array ( $result ) ) {
	$orderid = $row ['orderid'];
	$tracking = $row ['tracking'];
	$provider = $row ['provider'];
	echo "orderid:" . $orderid;
	
	try {
		$client = new WishClient ( $access_token, 'prod' );
		// Generate your own tracking information here:"
		$tracker = new WishTracker ( $provider, $tracking, 'Thanks for buying,welcome next time!' );
		// Fulfill the order using the tracking information
		$fulResult = $client->fulfillOrderById ( $orderid, $tracker );
		echo 'Order ' . $order->order_id . 'fulfilled: ' . $fulResult;
	} catch ( OrderAlreadyFulfilledException $e ) {
		print 'Order ' . $order->order_id . " already fulfilled.\n";
	}
}
