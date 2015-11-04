<?php
header ( "Content-Type: text/html;charset=utf-8" );
include 'Wish/WishClient.php';
include 'mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;

session_start ();
const host = "localhost";
const user = "root";
const psd = "yangwu";
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
echo "get the result of usertoken : ".mysql_num_rows($result)."<br/>";

while ( $rows = mysql_fetch_array ( $result ) ) {
	$clientid = $rows ['clientid'];
	$clientsecret = $rows ['clientsecret'];
	$token = $rows ['token'];
	$refresh_token = $rows ['refresh_token'];
	$accountid = $rows ['accountid'];
	echo "get clientid of accountid:" . $accountid . ", the client id is: " . $clientid . $clientsecret;
	$client = new WishClient ( $token, 'prod' );
	
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
		$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
		echo "\n get orders count:" . count ( $unfulfilled_orders ) . "<br/>";
		$orders_count = count ( $unfulfilled_orders );
		$printTrackingnumbers;
		foreach ( $unfulfilled_orders as $cur_order ) {
			$shippingDetail = $cur_order->ShippingDetail;
			$orderarray = array ();
			$orderarray ['orderid'] = $cur_order->order_id;
			$orderarray ['accountid'] = $accountid;
			$orderarray ['ordertime'] = $cur_order->order_time;
			$orderarray ['transactionid'] = $cur_order->transaction_id;
			$orderarray ['orderstate'] = $cur_order->state;
			$orderarray ['sku'] = $cur_order->sku;
			$orderarray ['productname'] = str_replace ( "'", " ", $cur_order->product_name ); // remove the ' in the sql;
			$orderarray ['productimage'] = $cur_order->product_image_url;
			$orderarray ['color'] = $cur_order->color;
			$orderarray ['size'] = $cur_order->size;
			$orderarray ['price'] = $cur_order->price;
			$orderarray ['cost'] = $cur_order->cost;
			$orderarray ['shipping'] = $cur_order->shipping;
			$orderarray ['shippingcost'] = $cur_order->shipping_cost;
			$orderarray ['quantity'] = $cur_order->quantity;
			$orderarray ['totalcost'] = $cur_order->order_total;
			$orderarray ['provider'] = '';
			$orderarray ['tracking'] = '';
			$orderarray ['name'] = $shippingDetail->name;
			$orderarray ['streetaddress1'] = $shippingDetail->street_address1;
			$orderarray ['streetaddress2'] = $shippingDetail->street_address2;
			$orderarray ['city'] = $shippingDetail->city;
			$orderarray ['state'] = $shippingDetail->state;
			$orderarray ['zipcode'] = $shippingDetail->zipcode;
			$orderarray ['phonenumber'] = $shippingDetail->phone_number;
			$orderarray ['countrycode'] = $shippingDetail->country;
			
			$orderarray ['orderstatus'] = '0'; // 0: new order; 1: applied tracking number; 2: has download label; 3: has uploaded tracking number;
			$insertResult = $dbhelper->insertOrder ( $orderarray );
			echo "insert: " . $insertResult;
			
			if (strcmp ( $shippingDetail->country, "US" ) != 0) {
				$xml = simplexml_load_string ( '<?xml version="1.0" encoding="utf-8"?><ExpressType/>' );
				
				$epcode = $xml->addChild ( "Epcode" );
				$userid = $xml->addChild ( "Userid", userid ); // *
				
				$orderPrice = $cur_order->price;
				$orderQuantity = $cur_order->quantity;
				$intPrice = intval ( $orderPrice );
				if (strcmp ( $orderQuantity, "1" ) == 0 && $intPrice < 6) {
					$channel = $xml->addChild ( "Channel", "105" ); // *
					$orderarray ['provider'] = "YanWen";
				} else {
					$channel = $xml->addChild ( "Channel", "154" ); // *
					$orderarray ['provider'] = "ChinaAirPost";
				}
				
				$userOrderNum = $xml->addChild ( "UserOrderNumber", substr ( 10000 * microtime ( true ), 4 ) );
				$sendDate = $xml->addChild ( "SendDate", date ( 'Y-m-d  H:i:s' ) ); // *
				$quantity = $xml->addChild ( "Quantity", $orderQuantity ); // *
				$packageno = $xml->addChild ( "PackageNo" );
				$insure = $xml->addChild ( "Insure" );
				$memo = $xml->addChild ( "Memo" );
				
				$Receiver = $xml->addChild ( "Receiver" );
				$RcUserid = $Receiver->addChild ( "Userid", userid ); // *
				$RcName = $Receiver->addChild ( "Name", $shippingDetail->name ); // *
				$RcPhone = $Receiver->addChild ( "Phone", $shippingDetail->phone_number );
				$RcMobile = $Receiver->addChild ( "Mobile" );
				$RcEmail = $Receiver->addChild ( "Email" );
				$RcCompany = $Receiver->addChild ( "Company" );
				$RcCountry = $Receiver->addChild ( "Country", $shippingDetail->country );
				$RcPostcode = $Receiver->addChild ( "Postcode", $shippingDetail->zipcode ); // *
				$RcState = $Receiver->addChild ( "State", $shippingDetail->state ); // *
				$RcCity = $Receiver->addChild ( "City", $shippingDetail->city ); // *
				$RcAddress1 = $Receiver->addChild ( "Address1", $shippingDetail->street_address1 ); // *
				$RcAddress2 = $Receiver->addChild ( "Address2", $shippingDetail->street_address2 );
				
				$Goods = $xml->addChild ( "GoodsName" );
				$gsUserid = $Goods->addChild ( "Userid", userid ); // *
				
				$gsName = $cur_order->product_name;
				if (strpos ( $gsName, "earring" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "耳钉" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "earring: " . $cur_order->sku . "-" . $cur_order->color . "-" . $cur_order->size ); // *
				} else if (strpos ( $gsName, "wear" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "内裤" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "underwear: " . $cur_order->sku . "-" . $cur_order->color . "-" . $cur_order->size ); // *
				} else if (strpos ( $gsName, "cami" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "吊带" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "camisole: " . $cur_order->sku . "-" . $cur_order->color . "-" . $cur_order->size ); // *
				} else if (strpos ( $gsName, "sticker" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "墙贴" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "sticker: " . $cur_order->sku . "-" . $cur_order->color . "-" . $cur_order->size ); // *
				} else {
					$gsNameCh = $Goods->addChild ( "NameCh", "衣服" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "clothes: " . $cur_order->sku . "-" . $cur_order->color . "-" . $cur_order->size ); // *
				}
				
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
				echo "tracking:" . $trackingnumber . "success:" . $success;
				if (strcmp ( $success, "true" ) == 0) {
					$printTrackingnumbers = $printTrackingnumbers . $trackingnumber . ",";
				}
				if (! empty ( $error ))
					echo "error:" . $error;
				
				$orderarray ['tracking'] = $trackingnumber;
				$orderarray ['orderstatus'] = '1';
				
				$dbhelper->updateOrder ( $orderarray );
			}
		}
	} else {
	}
}
?>
<body>
	<form action="downloadlabel.php" method="post">
		<input type="hidden" name="labels"
			value="<?php echo $printTrackingnumbers?>"> <input type="submit"
			value="下载标签" />
	</form>

	<form action="uploadTracking.php" method="post">
		<input type="hidden" name="accountid" value="<?php echo $accountid?>">
		<input type="hidden" name="token" value="<?php echo $token?>"> <input
			type="submit" value="上传订单号" />
	</form>
</body>

