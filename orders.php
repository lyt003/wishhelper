<?php
header ( "Content-Type: text/html;charset=utf-8" );
include 'Wish/WishClient.php';
include 'mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;

session_start ();
const userid = "104903";
const ServiceEndPoint = "http://online.yw56.com.cn/service";
const NoteToCustomers = 'Thanks for buying,welcome next time!';

$post_header = array (
		'Authorization: basic MTA0OTAzOnlhbmd3dTE5ODIxMTEy',
		'Content-Type: text/xml; charset=utf-8' 
);

$email = $_SESSION ['email'];
$dbhelper = new dbhelper ();
$result = $dbhelper->getUserToken ( $email );
echo "get the result of usertoken : " . mysql_num_rows ( $result ) . "<br/>";

$accounts = array ();
$i = 0;
while ( $rows = mysql_fetch_array ( $result ) ) {
	$accounts ['clientid' . $i] = $rows ['clientid'];
	$accounts ['clientsecret' . $i] = $rows ['clientsecret'];
	$accounts ['token' . $i] = $rows ['token'];
	$accounts ['refresh_token' . $i] = $rows ['refresh_token'];
	$accounts ['accountid' . $i] = $rows ['accountid'];
	$i ++;
}

$printTrackingnumbers;
for($count = 0; $count < $i; $count ++) {
	$clientid = $accounts ['clientid' . $count];
	$clientsecret = $accounts ['clientsecret' . $count];
	$token = $accounts ['token' . $count];
	$refresh_token = $accounts ['refresh_token' . $count];
	$accountid = $accounts ['accountid' . $count];
	echo "get clientid of accountid:" . $accountid . ", the client id is: " . $clientid . $clientsecret;
	$client = new WishClient ( $token, 'prod' );
	
	$preTransactionid = "";
	$preOrderNum = 0;
	$preGoodsNameEn = "";
	
	// First, get the orders from db that didn't fulfilled tracking number to wish.
	// and then get the unfulfilled orders from wish.
	$ordersNotUpload = $dbhelper->getOrdersNotUploadTracking ( $accountid );
	echo "<br/>get the orders that not upload: " . $ordersNotUpload . "<br/>";
	while ( $orderUpload = mysql_fetch_array ( $ordersNotUpload ) ) {
		$tracker = new WishTracker ( $orderUpload ['provider'], $orderUpload ['tracking'], NoteToCustomers );
		$fulResult = $client->fulfillOrderById ( $orderUpload ['orderid'], $tracker );
		
		// orderstatus: 0: new order; 1: applied tracking number; 2: has download label; 3: has uploaded tracking number;
		if ($fulResult) {
			$orderUpload ['orderstatus'] = '3';
			$orderUpload ['accountid'] = $accountid;
			$updateResult = $dbhelper->updateOrder ( $orderUpload );
		}
	}
	
	if (! empty ( $token )) {
		// Get an array of all unfufilled orders since January 20, 2010
		try {
			$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
		} catch ( ServiceResponseException $e ) {
			if ($e->getStatusCode () == 1015) {
				$response = $client->refreshToken ( $clientid, $clientsecret, $refresh_token );
				echo "<br/>errorMessage:" . $response->getMessage ();
				$values = $response->getResponse ()->{'data'};
				$newToken = '0';
				$newRefresh_token = '0';
				foreach ( $values as $k => $v ) {
					echo 'key  ' . $k . '  value:' . $v;
					if ($k == 'access_token') {
						$newToken = $v;
					}
					if ($k == 'refresh_token') {
						$newRefresh_token = $v;
					}
				}
				echo "<br/>newToken = " . $newToken . $newRefresh_token;
				$dbhelper->updateUserToken ( $accountid, $newToken, $newRefresh_token );
				$client = new WishClient ( $newToken, 'prod' );
				$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
			}
		}
		echo "\n get orders count:" . count ( $unfulfilled_orders ) . "<br/>";
		$orders_count = count ( $unfulfilled_orders );
		
		foreach ( $unfulfilled_orders as $cur_order ) {
			$shippingDetail = $cur_order->ShippingDetail;
			$orderarray = array ();
			$orderarray ['transactionid'] = $cur_order->transaction_id;
			$orderarray ['orderid'] = $cur_order->order_id;
			
			if (strcmp ( $cur_order->transaction_id, $preTransactionid ) == 0) { // there are more than one orders in a transaction.
				$preOrderNum = $preOrderNum + 1;
				$orderarray ['orderNum'] = $preOrderNum;
			} else {
				$orderarray ['orderNum'] = 0;
				$preTransactionid = $cur_order->transaction_id;
				$preOrderNum = 0;
			}
			
			$orderarray ['accountid'] = $accountid;
			$orderarray ['ordertime'] = $cur_order->order_time;
			
			$orderarray ['orderstate'] = $cur_order->state;
			$orderarray ['sku'] = $cur_order->sku;
			$orderarray ['productname'] = str_replace ( '"', "''", $cur_order->product_name ); // use '' replace the " in the sql;
			$orderarray ['productimage'] = $cur_order->product_image_url;
			if (! empty ( $cur_order->color )) {
				$orderarray ['color'] = $cur_order->color;
			} else {
				$orderarray ['color'] = "";
			}
			
			if (! empty ( $cur_order->size )) {
				$orderarray ['size'] = $cur_order->size;
			} else {
				$orderarray ['size'] = "";
			}
			
			$orderarray ['price'] = $cur_order->price;
			$orderarray ['cost'] = $cur_order->cost;
			$orderarray ['shipping'] = $cur_order->shipping;
			$orderarray ['shippingcost'] = $cur_order->shipping_cost;
			$orderarray ['quantity'] = $cur_order->quantity;
			$orderarray ['totalcost'] = $cur_order->order_total;
			$orderarray ['provider'] = '';
			$orderarray ['tracking'] = '';
			$orderarray ['name'] = $shippingDetail->name;
			$orderarray ['streetaddress1'] = str_replace ( '"', "''", $shippingDetail->street_address1 );
			if (! empty ( $shippingDetail->street_address2 )) {
				$orderarray ['streetaddress2'] = str_replace ( '"', "''", $shippingDetail->street_address2 );
			} else {
				$orderarray ['streetaddress2'] = "";
			}
			
			$orderarray ['city'] = $shippingDetail->city;
			if (! empty ( $shippingDetail->state )) {
				$orderarray ['state'] = $shippingDetail->state;
			} else {
				$orderarray ['state'] = "";
			}
			$orderarray ['zipcode'] = $shippingDetail->zipcode;
			$orderarray ['phonenumber'] = $shippingDetail->phone_number;
			$orderarray ['countrycode'] = $shippingDetail->country;
			
			$orderarray ['orderstatus'] = '0'; // 0: new order; 1: applied tracking number; 2: has download label; 3: has uploaded tracking number;
			
			echo "<br/> countrycode  = " . $orderarray ['countrycode'];
			$insertResult = $dbhelper->insertOrder ( $orderarray );
			echo "insert: " . $insertResult . "<br/>";
		}
	}
	
	// apply tracking for orders.
	$ordersNoTracking = $dbhelper->getOrdersNoTracking ( $accountid );
	echo "get ordersNoTracking:" . mysql_num_rows ( $ordersNoTracking ) . "<br/>";
	$preTransactionid = "";
	while ( $orderNoTracking = mysql_fetch_array ( $ordersNoTracking ) ) {
		
		if (strcmp ( $orderNoTracking ['countrycode'], "US" ) != 0) {
			$xml = simplexml_load_string ( '<?xml version="1.0" encoding="utf-8"?><ExpressType/>' );
			
			$epcode = $xml->addChild ( "Epcode" );
			$userid = $xml->addChild ( "Userid", userid ); // *
			
			$orderPrice = $orderNoTracking ['price'];
			$orderQuantity = $orderNoTracking ['quantity'];
			$intPrice = intval ( $orderPrice );
			
			if ($orderNoTracking ['orderNum'] != 0) {
				$preGoodsNameEn = $preGoodsNameEn . $orderNoTracking ['sku'] . "-" . $orderNoTracking ['color'] . "-" . $orderNoTracking ['size'] . "*" . $orderQuantity;
				$preTransactionid = $orderNoTracking ['transactionid'];
			} else {
				if (strcmp ( $preGoodsNameEn, "" ) != 0 && strcmp ( $orderNoTracking ['transactionid'], $preTransactionid ) == 0) {
					$channel = $xml->addChild ( "Channel", "154" ); // *
					$orderNoTracking ['provider'] = "ChinaAirPost";
				} else {
					$preTransactionid = $orderNoTracking ['transactionid'];
					if (strcmp ( $orderQuantity, "1" ) == 0 && $intPrice < 6) {
						$channel = $xml->addChild ( "Channel", "105" ); // *
						$orderNoTracking ['provider'] = "YanWen";
					} else {
						$channel = $xml->addChild ( "Channel", "154" ); // *
						$orderNoTracking ['provider'] = "ChinaAirPost";
					}
				}
				
				$userOrderNum = $xml->addChild ( "UserOrderNumber", $accountid . "_" . substr ( 10000 * microtime ( true ), 4, 9 ) );
				$sendDate = $xml->addChild ( "SendDate", date ( 'Y-m-d  H:i:s' ) ); // *
				$quantity = $xml->addChild ( "Quantity", $orderQuantity ); // *
				$packageno = $xml->addChild ( "PackageNo" );
				$insure = $xml->addChild ( "Insure" );
				$memo = $xml->addChild ( "Memo" );
				
				$Receiver = $xml->addChild ( "Receiver" );
				$RcUserid = $Receiver->addChild ( "Userid", userid ); // *
				$RcName = $Receiver->addChild ( "Name", $orderNoTracking ['name'] ); // *
				$RcPhone = $Receiver->addChild ( "Phone", $orderNoTracking ['phonenumber'] );
				$RcMobile = $Receiver->addChild ( "Mobile" );
				$RcEmail = $Receiver->addChild ( "Email" );
				$RcCompany = $Receiver->addChild ( "Company" );
				$RcCountry = $Receiver->addChild ( "Country", $orderNoTracking ['countrycode'] );
				$RcPostcode = $Receiver->addChild ( "Postcode", $orderNoTracking ['zipcode'] ); // *
				$RcState = $Receiver->addChild ( "State", $orderNoTracking ['state'] ); // *
				$RcCity = $Receiver->addChild ( "City", $orderNoTracking ['city'] ); // *
				$RcAddress1 = $Receiver->addChild ( "Address1", $orderNoTracking ['streetaddress1'] ); // *
				$RcAddress2 = $Receiver->addChild ( "Address2", $orderNoTracking ['streetaddress2'] );
				
				$Goods = $xml->addChild ( "GoodsName" );
				$gsUserid = $Goods->addChild ( "Userid", userid ); // *
				
				$gsName = $orderNoTracking ['productname'];
				if (stripos ( $gsName, "earring" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "耳钉" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "earring: " . $orderNoTracking ['sku'] . "-" . $orderNoTracking ['color'] . "-" . $orderNoTracking ['size'] . ";" . $preGoodsNameEn ); // *
				} else if (stripos ( $gsName, "wear" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "内裤" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "underwear: " . $orderNoTracking ['sku'] . "-" . $orderNoTracking ['color'] . "-" . $orderNoTracking ['size'] . ";" . $preGoodsNameEn ); // *
				} else if (stripos ( $gsName, "cami" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "吊带" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "camisole: " . $orderNoTracking ['sku'] . "-" . $orderNoTracking ['color'] . "-" . $orderNoTracking ['size'] . ";" . $preGoodsNameEn ); // *
				} else if (stripos ( $gsName, "sticker" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "墙贴" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "sticker: " . $orderNoTracking ['sku'] . "-" . $orderNoTracking ['color'] . "-" . $orderNoTracking ['size'] . ";" . $preGoodsNameEn ); // *
				} else {
					$gsNameCh = $Goods->addChild ( "NameCh", "衣服" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "clothes: " . $orderNoTracking ['sku'] . "-" . $orderNoTracking ['color'] . "-" . $orderNoTracking ['size'] . ";" . $preGoodsNameEn ); // *;
				}
				
				$preGoodsNameEn = "";
				
				$gsWeight = $Goods->addChild ( "Weight", "100" ); // *
				$gsDeclaredValue = $Goods->addChild ( "DeclaredValue", "4" ); // *
				$gsDeclaredCurrency = $Goods->addChild ( "DeclaredCurrency", "USD" ); // *
				$gsMoreGoodsName = $Goods->addChild ( "MoreGoodsName" );
				$GsHsCode = $Goods->addChild ( "HsCode" );
				
				$XMLString = $xml->asXML ();
				
				$curl = curl_init ();
				$url = ServiceEndPoint . "/Users/" . userid . "/Expresses";
				curl_setopt ( $curl, CURLOPT_URL, $url );
				curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt ( $curl, CURLOPT_POST, true );
				curl_setopt ( $curl, CURLOPT_HTTPHEADER, $post_header );
				curl_setopt ( $curl, CURLOPT_POSTFIELDS, $XMLString );
				$result = curl_exec ( $curl );
				$error = curl_error ( $curl );
				curl_close ( $curl );
				$resultXML = simplexml_load_string ( $result );
				var_dump ( $resultXML );
				$response = $resultXML->Response;
				$trackingnumber = $response->Epcode;
				$success = $response->Success;
				if ($trackingnumber == null || strcmp ( $trackingnumber, "" ) == 0) {
					$createExpress = $resultXML->CreatedExpress;
					$trackingnumber = $createExpress->Epcode;
					if ($trackingnumber == null || strcmp ( $trackingnumber, "" ) == 0) {
						$trackingnumber = $createExpress->YanwenNumber;
					}
				}
				echo "tracking:" . $trackingnumber . "success:" . $success;
				if (strcmp ( $success, "true" ) == 0) {
					$printTrackingnumbers = $printTrackingnumbers . $trackingnumber . ",";
					$orderNoTracking ['tracking'] = $trackingnumber;
					$orderNoTracking ['orderstatus'] = '1';
					$dbhelper->updateOrder ( $orderNoTracking );
				}
				if (! empty ( $error ))
					echo "<br/>Failed to get the tracking from YW, error:" . $error . "<br/>";
			}
		}
	}
}
?>
<body>
	<form action="downloadlabel.php" method="post">
		<input type="hidden" name="labels"
			value="<?php echo $printTrackingnumbers?>"> <input type="submit"
			value="下载标签" />
	</form>

	<form action="orders.php" method="post">
		<input type="hidden" name="accountid" value="<?php echo $accountid?>">
		<input type="hidden" name="token" value="<?php echo $token?>"> <input
			type="submit" value="上传订单号" />
	</form>
	<a href="EUBExcel.php">下载美国订单</a>
</body>

