<?php
header ( "Content-Type: text/html;charset=utf-8" );
include 'Wish/WishClient.php';
use Wish\WishClient;

session_start ();
const host = "localhost";
const user = "root";
const psd = "yangwu";
const userid = "104903";

$db = mysql_connect ( host, user, psd );
mysql_select_db ( 'wish' );
mysql_query ( "set names 'utf-8'" );
const ServiceEndPoint = "http://online.yw56.com.cn/service";

$post_header = array (
		'Authorization: basic MTA0OTAzOnlhbmd3dTE5ODIxMTEy',
		'Content-Type: text/xml; charset=utf-8' 
);

$email = $_SESSION ['email'];
echo "email" . $email;
$result = mysql_query ( "select clientid,clientsecret,token,refresh_token from accounts, users where users.email = '" . $email . "' and users.userid = accounts.userid" );
if (mysql_num_rows ( $result ) >= 1) {
	echo "get user info";
	$rows = mysql_fetch_array ( $result );
	$clientid = $rows ['clientid'];
	$clientsecret = $rows ['clientsecret'];
	$token = $rows ['token'];
	$refresh_token = $rows ['refresh_token'];
	echo "clientid" . $clientid . $clientsecret;
	if (! empty ( $token )) {
		// Get an array of all unfufilled orders since January 20, 2010
		$client = new WishClient ( $token, 'prod' );
		$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
		print ("\n orders count:" . count ( $unfulfilled_orders ) . " changed orders.\n") ;
		$orders_count = count ( $unfulfilled_orders );
		$i = 0;
		foreach ( $unfulfilled_orders as $cur_order ) {
			$i ++;
			$shippingDetail = $cur_order->ShippingDetail;
			if (strcmp ( $shippingDetail->country, "US" ) != 0) {
				$xml = simplexml_load_string ( '<?xml version="1.0" encoding="utf-8"?><ExpressType/>' );
				
				$epcode = $xml->addChild ( "Epcode" );
				$userid = $xml->addChild ( "Userid", userid ); // *
				
				$orderPrice = $cur_order->price;
				$orderQuantity = $cur_order->quantity;
				$intPrice = intval ( $orderPrice );
				if (strcmp ( $orderQuantity, "1" ) == 0 && $intPrice < 6) {
					$channel = $xml->addChild ( "Channel", "105" ); // *
				} else {
					$channel = $xml->addChild ( "Channel", "154" ); // *
				}
				
				$userOrderNum = $xml->addChild ( "UserOrderNumber", "1027" . $i );
				$sendDate = $xml->addChild ( "SendDate", $cur_order->order_time ); // *
				$quantity = $xml->addChild ( "Quantity", $orderQuantity ); // *
				$packageno = $xml->addChild ( "PackageNo" );
				$insure = $xml->addChild ( "Insure" );
				$memo = $xml->addChild ( "Memo" );
				
				$Receiver = $xml->addChild ( "Receiver" );
				$RcUserid = $Receiver->addChild ( "Userid", userid ); // *
				$RcName = $Receiver->addChild ( "Name", $shippingDetail->name ); // *
				echo "RcName: " . $shippingDetail->name;
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
					$gsNameEn = $Goods->addChild ( "NameEn", "earring" ); // *
				} else if (strpos ( $gsName, "wear" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "内裤" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "underwear" ); // *
				} else if (strpos ( $gsName, "cami" ) != false) {
					$gsNameCh = $Goods->addChild ( "NameCh", "吊带" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "camisole" ); // *
				} else {
					$gsNameCh = $Goods->addChild ( "NameCh", "耳钉" ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", "earring" ); // *
				}
				
				$gsWeight = $Goods->addChild ( "Weight", "100" ); // *
				$gsDeclaredValue = $Goods->addChild ( "DeclaredValue", "4" ); // *
				$gsDeclaredCurrency = $Goods->addChild ( "DeclaredCurrency", "USD" ); // *
				$gsMoreGoodsName = $Goods->addChild ( "MoreGoodsName" );
				$GsHsCode = $Goods->addChild ( "HsCode" );
				
				$resultXML = $xml->asXML ();
				
				$curl = curl_init ();
				$url = ServiceEndPoint . "/Users/" . userid . "/Expresses";
				curl_setopt ( $curl, CURLOPT_URL, $url );
				curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
				curl_setopt ( $curl, CURLOPT_POST, true );
				curl_setopt ( $curl, CURLOPT_HTTPHEADER, $post_header );
				curl_setopt ( $curl, CURLOPT_POSTFIELDS, $resultXML );
				$result = curl_exec ( $curl );
				$error = curl_error ( $curl );
				curl_close ( $curl );
				echo "error:" . $error;
			}
		}
	} else {
	}
} else {
	echo "get null info";
}
		 