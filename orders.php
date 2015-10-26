<?php
include 'Wish/WishClient.php';
use Wish\WishClient;

session_start ();
$host = "localhost";
$user = "root";
$psd = "yangwu";
$db = mysql_connect ( $host, $user, $psd );
mysql_select_db ( 'wish' );
mysql_query ( "set names 'utf-8'" );

$email = $_SESSION ['email'];
echo "email".$email;
$result = mysql_query ( "select clientid,clientsecret,token,refresh_token from accounts, users where users.email = '" . $email . "' and users.userid = accounts.userid" );
if (mysql_num_rows ( $result ) >= 1) {
	echo "get user info";
	$rows = mysql_fetch_array($result);
	$clientid = $rows['clientid'];
	$clientsecret = $rows['clientsecret'];
	$token = $rows['token'];
	$refresh_token = $rows['refresh_token'];
	echo "clientid".$clientid.$clientsecret;
	if(!empty($token)){
		//Get an array of all unfufilled orders since January 20, 2010
		$client = new WishClient($token,'prod');
		$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince('2010-01-20');
		print("\n orders count:".count($unfulfilled_orders)." changed orders.\n");
		//var_dump($unfulfilled_orders);
		$orders_count = count($unfulfilled_orders);
		for($i=0;i<$orders_count;$i++){
			$cur_order = $unfulfilled_orders[$i];
			
			$shippingDetail = $cur_order->ShippingDetail;
			
			
			$xml = simplexml_load_string ( '<?xml version="1.0" encoding="utf-8"?><ExpressType/>' );
			
			$epcode = $xml->addChild ( "Epcode");
			$userid = $xml->addChild ( "Userid", "104903" );//*
			$channel = $xml->addChild("Channel","154");//*
			$userOrderNum = $xml->addChild("UserOrderNumber","1026".i);
			$sendDate = $xml->addChild("SendDate",$cur_order->order_time);//*
			$quantity = $xml->addChild("Quantity",$cur_order->quantity);//*
			$packageno = $xml->addChild("PackageNo");
			$insure = $xml->addChild("Insure");
			$memo = $xml->addChild("Memo");
			
			$Receiver = $xml->addChild("Receiver");
			$RcUserid = $Receiver->addChild("Userid","104903");//*
			$RcName = $Receiver->addChild("Name",$shippingDetail->name);//*
			$RcPhone = $Receiver->addChild("Phone",$shippingDetail->phone_number);
			$RcMobile = $Receiver->addChild("Mobile");
			$RcEmail = $Receiver->addChild("Email");
			$RcCompany = $Receiver->addChild("Company");
			$RcCountry = $Receiver->addChild("Country",$shippingDetail->country);
			$RcPostcode = $Receiver->addChild("Postcode",$shippingDetail->zipcode);//*
			$RcState = $Receiver->addChild("State",$shippingDetail->state);//*
			$RcCity = $Receiver->addChild("City",$shippingDetail->city);//*
			$RcAddress1 = $Receiver->addChild("Address1",$shippingDetail->street_address1);//*
			$RcAddress2 = $Receiver->addChild("Address2",$shippingDetail->street_address2);
			
			
			$Goods = $xml->addChild("GoodsName");
			$gsUserid = $Goods->addChild("Userid","104903");//*
			$gsNameCh = $Goods->addChild("NameCh");//*
			$gsNameEn = $Goods->addChild("NameEn");//*
			$gsWeight = $Goods->addChild("Weight");//*
			$gsDeclaredValue = $Goods->addChild("DeclaredValue");//*
			$gsDeclaredCurrency = $Goods->addChild("DeclaredCurrency");//*
			$gsMoreGoodsName = $Goods->addChild("MoreGoodsName");
			$GsHsCode = $Goods->addChild("HsCode");
			
			$resultXML = $xml->asXML();
			
			
			/*$shippingDetail = $cur_order->ShippingDetail;
			 if (strcmp($shippingDetail->country,'US') == 0){
				$eub = new EUBOrders();
				$eub->getTrackingID();
			} */
		}
	}else{
		
	}
} else {
	echo "get null info";
}
		 