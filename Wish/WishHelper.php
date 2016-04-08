<?php

namespace Wish;

include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
use mysql\dbhelper;

class WishHelper {
	private $dbhelper;
	
	public function __construct(){
		$this->dbhelper = new dbhelper();
	}
	
	// save the unfulfilled orders into db;
	public function saveOrders($unfulfilled_orders, $accountid) {
		$preTransactionid = "";
		$preOrderNum = 0;
		
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
			
			$insertResult = $this->dbhelper->insertOrder ( $orderarray );
		}
	}
	
	public function getUserLabelsArray($userid){
		$labels = array();
		$labelResult = $this->dbhelper->getUserLabels($userid);
		while ($label = mysql_fetch_array ( $labelResult )) {
			$labels[$label['parentsku']] = $label['cn_name']."|".$label['en_name'];
		}
		return $labels;
	}
	
	public function getLabelsArray($userLabels){
		$labelsarray = array();
		foreach ($userLabels as $lKey=>$lValue){
			$labelsarray[] = $lValue;
		}
		return $labelsarray;
	}
	
	public function getCNENLabel($labels,$sku){
		$curLabel = $labels[$sku];
		$cnenlabel = explode('|',$curLabel);
		if($cnenlabel[0] == null)
			$cnenlabel[0] = "";
		if($cnenlabel[1] == null)
			$cnenlabel[1] = "";
		return $cnenlabel;
	}
	
	public function applyTrackingsForOrders($accountid,$labels,$expressinfo){
		
		$post_header = array (
				'Authorization: basic '.$expressinfo[YANWEN_API_TOKEN],
				'Content-Type: text/xml; charset=utf-8'
		);
		
		$ordersNoTracking = $this->dbhelper->getOrdersNoTracking ( $accountid );
		echo "get ordersNoTracking:" . mysql_num_rows ( $ordersNoTracking ) . "<br/>";
		$preTransactionid = "";
		while ( $orderNoTracking = mysql_fetch_array ( $ordersNoTracking ) ) {
		
			//if (strcmp ( $orderNoTracking ['countrycode'], "US" ) != 0) {
				$xml = simplexml_load_string ( '<?xml version="1.0" encoding="utf-8"?><ExpressType/>' );
					
				$epcode = $xml->addChild ( "Epcode" );
				$ywuserid = $xml->addChild ( "Userid", $expressinfo[YANWEN_USER_ATTR] ); // *
					
				$orderTotalPrice = $orderNoTracking ['totalcost'];
				$orderQuantity = $orderNoTracking ['quantity'];
				$intPrice = intval ( $orderTotalPrice );
					
				if ($orderNoTracking ['orderNum'] != 0) {
					$preGoodsNameEn = $preGoodsNameEn . $orderNoTracking ['sku'] . "-" . $orderNoTracking ['color'] . "-" . $orderNoTracking ['size'] . "*" . $orderQuantity;
					$preTransactionid = $orderNoTracking ['transactionid'];
				} else {
					if (strcmp ( $preGoodsNameEn, "" ) != 0 && strcmp ( $orderNoTracking ['transactionid'], $preTransactionid ) == 0) {
						$channel = $xml->addChild ( "Channel", "154" ); // *
						$orderNoTracking ['provider'] = "ChinaAirPost";
					} else {
						$preTransactionid = $orderNoTracking ['transactionid'];
						if (strcmp ( $orderQuantity, "1" ) == 0 && $intPrice < 7) {
							$channel = $xml->addChild ( "Channel", "105" ); // *
							$orderNoTracking ['provider'] = "YanWen";
						} else {
							$channel = $xml->addChild ( "Channel", "154" ); // *
							$orderNoTracking ['provider'] = "ChinaAirPost";
						}
					}
					
					if (strcmp ( $orderNoTracking ['countrycode'], "US" ) == 0 && strcmp ($orderNoTracking ['provider'],"ChinaAirPost") == 0){// process by EUB;
						$preGoodsNameEn = "";
						continue;
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
					$gsUserid = $Goods->addChild ( "Userid", $expressinfo[YANWEN_USER_ATTR] ); // *
		
					$tempSKU = $orderNoTracking ['sku'];
					$tempSKU = str_replace(' ','_',$tempSKU);
					$tempSKU = str_replace('&amp;','AND',$tempSKU);
					
					$gsLabel = $this->getCNENLabel($labels, $tempSKU);
					$gsNameCh = $Goods->addChild ( "NameCh", $gsLabel[0] ); // *
					$gsNameEn = $Goods->addChild ( "NameEn", $gsLabel[1] ." :". $orderNoTracking ['sku'] . "-" . $orderNoTracking ['color'] . "-" . $orderNoTracking ['size'] . ";" . $preGoodsNameEn ); // *
		
					$preGoodsNameEn = "";
					$gsWeight = $Goods->addChild ( "Weight", "100" ); // *
					$gsDeclaredValue = $Goods->addChild ( "DeclaredValue", "4" ); // *
					$gsDeclaredCurrency = $Goods->addChild ( "DeclaredCurrency", "USD" ); // *
					$gsMoreGoodsName = $Goods->addChild ( "MoreGoodsName" );
					$GsHsCode = $Goods->addChild ( "HsCode" );
		
					$XMLString = $xml->asXML ();
		
					$curl = curl_init ();
					$url = $expressinfo[YANWEN_SERVICE_URL] . "/Users/" . $expressinfo[YANWEN_USER_ATTR] . "/Expresses";
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
						$this->dbhelper->updateOrder ( $orderNoTracking );
					}
					if (! empty ( $error )){
						echo "<br/>Failed to get the tracking from YW, error:" . $error . "<br/>";
						echo "<br/>post header:".$post_header[0]."<br/>";
						var_dump($XMLString);
						echo "<br/>result:".$result."<br/>";
					}
						
				}
			//}
		}
	}
	
	public function getExpressInfo($userid){
		$expressInfo = array();
		$expressResult = $this->dbhelper->getExpressInfo($userid, 1);
		while($expressAttr = mysql_fetch_array($expressResult)){
			$expressInfo[$expressAttr['express_attr_name']] = $expressAttr['express_attr_value'];
		}
		return $expressInfo;
	}
	
	public function getTrackingNumbersForLabel($userid){
		$numbers;
		$result = $this->dbhelper->getUserOrdersForLabels($userid);
		while($order = mysql_fetch_array($result)){
			if($order['tracking'] != null && $order['tracking']!= '')
				$numbers = $numbers.$order['tracking'].',';
		}
		return $numbers;
	}
	
	public function getEUBOrders($userid){
		return $this->dbhelper->getEUBOrders($userid);
	}
	
	public function updateEUBOrders($orderid,$status){
		return $this->dbhelper->updateEUBOrderStatus($orderid, $status);
	}
	
	public function updateHasDownloadLabel($numbers){
		$trackings = explode(',',$numbers);
		foreach ($trackings as $tracking){
			if($tracking != null && $tracking != '')
				$this->dbhelper->updateOrderStatus($tracking, ORDERSTATUS_DOWNLOADEDLABEL);
		}
	}
	
	public function getProductVarsCount($productsVars){
		$productsInfo = array();
		$tempParentSKU = "";
		$varCounts = 0;
		$productsarray = array();
		$index = 0;
		while ( $curProductVar = mysql_fetch_array ( $productsVars) ) {
			$productsarray[$index++] = $curProductVar;
			
			$currentParentSKU =  $curProductVar['parent_sku'];
			
			if($currentParentSKU != $tempParentSKU ){
				
				
				if($tempParentSKU != ""){
					$productsInfo[$tempParentSKU] = $varCounts;
				}
				
				$tempParentSKU = $currentParentSKU;
				$varCounts = 0;
			}
			
			$varCounts ++;
		}
		
		//for last product:
		if($tempParentSKU != ""){
			$productsInfo[$tempParentSKU] = $varCounts;
		}
		
		$productsInfo['productvars'] = $productsarray;
		return $productsInfo;
	}
	
	public  function getProductDetails($productDetails){
		$productsInfo = array();
		$productVarsarray = array();
		$index = 0;
		while($curProductDetail = mysql_fetch_array($productDetails)){
			$productVarsarray[$index++] = $curProductDetail;
		}
		if($index>0){
			$productsInfo['parent_sku'] =  $productVarsarray[0]['parent_sku'];
			$productsInfo['id'] =  $productVarsarray[0]['id'];
			$productsInfo['main_image'] =  $productVarsarray[0]['main_image'];
			$productsInfo['extra_images'] =  $productVarsarray[0]['extra_images'];
			$productsInfo['is_promoted'] =  $productVarsarray[0]['is_promoted'];
			$productsInfo['name'] =  $productVarsarray[0]['name'];
			$productsInfo['review_status'] =  $productVarsarray[0]['review_status'];
			$productsInfo['tags'] =  $productVarsarray[0]['tags'];
			$productsInfo['description'] =  $productVarsarray[0]['description'];
			$productsInfo['number_saves'] =  $productVarsarray[0]['number_saves'];
			$productsInfo['number_sold'] =  $productVarsarray[0]['number_sold'];
			$productsInfo['date_uploaded'] =  $productVarsarray[0]['date_uploaded'];
			$productsInfo['date_updated'] =  $productVarsarray[0]['date_updated'];
			
			$productsInfo['productvars'] = $productVarsarray;
		}
		return $productsInfo;
	}
	
	
	public  function getProductVars($productid){
		$skus = array();
		$vars = $this->dbhelper->getProductVars($productid);
		while($var = mysql_fetch_array($vars)){
			$skus[] = $var['sku'];
		}
		return $skus;
	}
	
	public function getProductOrders($accountid,$productid,$startdate,$enddate){
		$orders = array();
		$productOrders = $this->dbhelper->getProductOrders($accountid, $productid, $startdate, $enddate);
		while($curOrder = mysql_fetch_array($productOrders)){
				$orders[] = $curOrder['orders'];
		}
		return $orders;
	}
}
