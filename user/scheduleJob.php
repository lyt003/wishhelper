<?php

namespace user;

include_once dirname ( '__FILE__' ) . './Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
use Wish\WishClient;
use mysql\dbhelper;

class scheduleJob {
	private $dbhelper;
	public function __construct() {
		$this->dbhelper = new dbhelper();
	}
	public function execute() {
	
		$this->jobexecute();
	}
	private function jobexecute() {
		$jobs = $this->dbhelper->getOptimizeJobs ();
		$this->dbhelper->updateSettingMsg ( "start to process jobs" );
		
		$optimizeparams = $this->dbhelper->getOptimizeParams ();
		if ($oparams = mysql_fetch_array ( $optimizeparams )) {
			$regularInventory = $oparams ['inventory'];
			$daysUploaded = $oparams ['daysuploaded'];
			$regularInventoryExtra = $oparams ['inventoryextra'];
			$regularImpressions = $oparams ['impression'];
			$regularBuyctr = $oparams ['buyctr'];
			$regularConversion = $oparams ['checkoutconversion'];
		}
		
		while ( $job = mysql_fetch_array ( $jobs ) ) {
			$accountid = $job ['accountid'];
			$operator = $job ['operator'];
			$jobproductid = $job ['productid'];
			$date = $job ['startdate'];
			if ($client == null || ($accountid != $client->getAccountid ())) {
				$accountAcess = $this->dbhelper->getAccountToken ( $accountid );
				if ($rows = mysql_fetch_array ( $accountAcess )) {
					$token = $rows ['token'];
					$client = new WishClient ( $token, 'prod' );
					$client->setAccountid ( $accountid );
				}
			}
			if ($client == null) {
				$this->dbhelper->updateJobMsg ( $accountid, $jobproductid, $date, "Failed to init WishClient of accountid" );
				continue;
			}
			
			if (strcmp ( $operator, DISABLEPRODUCT ) == 0) {
				$response = $client->disableProductById ( $jobproductid );
				$this->dbhelper->updateJobFinished ($accountid,  "1", $jobproductid, $date, date ( 'Y-m-d  H:i:s' ) . "   :  " . $response->getMessage());
				$this->dbhelper->updateSettingMsg ( "finished process product:" . $jobproductid . " of " . $date );
			} else if (strcmp ( $operator, LOWERSHIPPING ) == 0 || strcmp ( $operator, ADDINVENTORY ) == 0) {
			/* 	 暂停降价和库存增加功能；
				$vars = $this->dbhelper->getProductVars ( $jobproductid );
				$varsResponse = "V0618";
				while ( $var = mysql_fetch_array ( $vars ) ) {
					$currSKU = $var ['sku'];
					$productVar = $client->getProductVariationBySKU ( $currSKU );
					
					$enabled = $productVar->enabled;
					if (strcmp ( $enabled, 'True' ) == 0) {
						$params = array ();
						$params ['sku'] = $currSKU;
						
						if (strcmp ( $operator, LOWERSHIPPING ) == 0) {
							
							$lowesttotalprice = $this->dbhelper->getLowesttotalprice($jobproductid, $accountid);
							
							$shipping = $productVar->shipping;
							$price = $productVar->price;

							if($lowesttotalprice == null || $shipping + $price > $lowesttotalprice){
								if ($shipping > 1) {
									$params ['shipping'] = $shipping - 0.01;
								} else {
									if ($price > 2) {
										$params ['price'] = $price - 0.01;
									}
								}	
							}else {
								$varsResponse .= " has reached the lowestprice,didn't update price  ";								
							}
						}
						
						if (strcmp ( $operator, ADDINVENTORY ) == 0) {
							$curInventory = $productVar->inventory;
							if ($curInventory < $regularInventory) {
								$productVar->inventory = $regularInventory;
							} else {
								$productVar->inventory = $productVar->inventory + $regularInventoryExtra;
							}
							$params ['inventory'] = $productVar->inventory;
						}
						
						if (count ( $params ) > 1) {
							$updateResponse = $client->updateProductVarByParams ( $params );
							$varsResponse .= $updateResponse->getMessage ();
						}
					} else {
						$varsResponse .= " SKU" . $currSKU . " has disabled";
					}
				}
				$this->dbhelper->updateJobFinished ($accountid,  "1", $jobproductid, $date, date ( 'Y-m-d  H:i:s' ) . "   :  " . $varsResponse );
				$this->dbhelper->updateSettingMsg ( "finished process product:" . $jobproductid . " of " . $date ); */
			} else if(strcmp ( $operator, SYNCHRONIZEDSTORE ) == 0){

				$curtime = date('Y-m-d  H:i:s');
				if(strtotime($curtime) >= strtotime($date)){
					//设定好下次同步的记录，然后完成本次同步。
					$nextdate = date ( 'Y-m-d',strtotime('+1 day',strtotime($date)));
					$this->dbhelper->insertOptimizeJob($accountid, "SYNCHRONIZEDSTORE", "", $nextdate);
					$this->synchronizedStore($accountid,$jobproductid,$date);
					
					
					//添加本次黄钻产品优化记录:
					$promotedProducts = $this->dbhelper->getPromotedProducts($accountid);
					$ordersProducts = $this->dbhelper->getProductsHasOrder($accountid);
					
				    $hasOrderProductids = array();
				    while($op = mysql_fetch_array($ordersProducts)){
				    	$hasOrderProductids[] = $op['product_id'];
				    }
					
				    while($promotedProduct = mysql_fetch_array($promotedProducts)){
				    	$curProductID = $promotedProduct['id'];
				    	
				    	if(in_array($curProductID,$hasOrderProductids)){
				    		$this->dbhelper->insertOptimizeJob($accountid, ADDINVENTORY, $curProductID, $date);
				    	}else{
				    		$this->dbhelper->insertOptimizeJob($accountid, LOWERSHIPPING, $curProductID, $date);
				    	}
				    }
				    
				    $this->dbhelper->updateSettingMsg ( "finished process product:" . $jobproductid . " of " . $date );
				}
			}
		}
	}
	
	
	private function synchronizedStore($accountid,$jobproductid,$date) {
		$accountAcess = $this->dbhelper->getAccountToken ( $accountid );
		if ($rows = mysql_fetch_array ( $accountAcess )) {
			$token = $rows ['token'];
			$client = new WishClient ( $token, 'prod' );
			$client->setAccountid ( $accountid );
		}

		if(!isset($client)){
			$this->dbhelper->updateJobMsg ($accountid, $jobproductid, $date, "WishClient set failed " . $e->getErrorMessage () );
			return;
		}
			
		$start = 0;
		$limit = 50;
		do{
			$productsResult = $client->getProducts($start, $limit);
			$hasmore = $productsResult['more'];
			$products = $productsResult['data'];
			foreach ($products as $product){
		
				$tempProduct = array();
				
				$vars = get_object_vars($product);
				foreach ($vars as $key=>$val){
					$tempProduct[$key] = $val;
				}
		
				$tempTags = "";
				foreach ($tempProduct['tags'] as $tagObj){
					$tempTags = $tempTags.$tagObj->Tag->name.",";
				}
				$tempTags = rtrim($tempTags,",");
				$tempProduct['tags'] = $tempTags;
				
				if($tempProduct['wish_express_country_codes']!= null){
					$tempWE = "";
					foreach ($tempProduct['wish_express_country_codes'] as $weObj){
						$tempWE .= $weObj.",";
					}
					$tempProduct['wecountrycodes'] = $tempWE;
				}
		
				$tempProduct['accountid'] = $accountid ;
				$uploaded = $tempProduct['date_uploaded'];
				$tempdate = explode("-",trim($uploaded));
				$tempProduct['date_uploaded'] = $tempdate[2]."-".$tempdate[0]."-".$tempdate[1];
				$tempProduct['date_updated'] = $tempProduct['date_uploaded'];
				$this->insertOnlineProduct($tempProduct);
		
				$productVars = $tempProduct['variants'];
				foreach ($productVars as $productvar){
						
					$tempVars = array();
					$vvvvars = get_object_vars($productvar);
					foreach ($vvvvars as $key=>$val){
						$tempVars[$key] = $val;
					}
						
					$tempVars['accountid'] = $accountid ;
					$this->insertOnlineProductVar($tempVars);
				}
			}
				
			$start += $limit;
		}while ($hasmore); 
		
		$this->dbhelper->updateJobFinished ($accountid, "1", $jobproductid, $date, date ( 'Y-m-d  H:i:s' ));
	}
	
/* PRODUCT FORMAT:
 * 
  { ["original_image_url"]=> string(51) "http://www.wishconsole.com/images/587645adf3f22.jpg" 
   ["main_image"]=> string(124) "https://contestimg.wish.com/api/webimage/5876461865becd2824896ecc-original.jpg?cache_buster=f23e7453e3581380945f49f630d1ffae" 
   ["is_promoted"]=> string(5) "False" 
   ["name"]=> string(60) "Fashion 4PCS Men's Ultra Soft Bamboo Fiber underwear 4Colors" 
   ["tags"]=> array(10) { 
			[0]=> object(stdClass)#10 (1) { ["Tag"]=> object(stdClass)#11 (2) { ["id"]=> string(9) "underwear" ["name"]=> string(9) "Underwear" } } 
			[1]=> object(stdClass)#12 (1) { ["Tag"]=> object(stdClass)#13 (2) { ["id"]=> string(7) "fashion" ["name"]=> string(7) "Fashion" } } 
			[2]=> object(stdClass)#14 (1) { ["Tag"]=> object(stdClass)#15 (2) { ["id"]=> string(10) "boxerbrief" ["name"]=> string(12) "boxer briefs" } } 
			[3]=> object(stdClass)#16 (1) { ["Tag"]=> object(stdClass)#17 (2) { ["id"]=> string(10) "breathable" ["name"]=> string(10) "Breathable" } } 
			[4]=> object(stdClass)#18 (1) { ["Tag"]=> object(stdClass)#19 (2) { ["id"]=> string(3) "men" ["name"]=> string(3) "Men" } } 
			[5]=> object(stdClass)#20 (1) { ["Tag"]=> object(stdClass)#21 (2) { ["id"]=> string(11) "bamboofiber" ["name"]=> string(12) "Bamboo Fiber" } } 
			[6]=> object(stdClass)#22 (1) { ["Tag"]=> object(stdClass)#23 (2) { ["id"]=> string(5) "print" ["name"]=> string(5) "Print" } } 
			[7]=> object(stdClass)#24 (1) { ["Tag"]=> object(stdClass)#25 (2) { ["id"]=> string(5) "sizem" ["name"]=> string(6) "Size M" } } 
			[8]=> object(stdClass)#26 (1) { ["Tag"]=> object(stdClass)#27 (2) { ["id"]=> string(6) "bamboo" ["name"]=> string(6) "Bamboo" } } 
			[9]=> object(stdClass)#28 (1) { ["Tag"]=> object(stdClass)#29 (2) { ["id"]=> string(4) "soft" ["name"]=> string(4) "Soft" } } } 
	["review_status"]=> string(8) "approved" 
	["extra_images"]=> string(323) "https://contestimg.wish.com/api/webimage/5876461865becd2824896ecc-1-original.jpg|https://contestimg.wish.com/api/webimage/5876461865becd2824896ecc-2-original.jpg|https://contestimg.wish.com/api/webimage/5876461865becd2824896ecc-3-original.jpg|https://contestimg.wish.com/api/webimage/5876461865becd2824896ecc-4-original.jpg" 
	["wish_express_country_codes"]=> array(1) { [0]=> string(2) "US" } 
	["number_saves"]=> string(1) "0" 
	["variants"]=> array(1) { 
			[0]=> object(Wish\Model\WishProductVariation)#34 (11) { 
					["sku"]=> string(48) "H_WEJDR9352_Chinese size XXXL(32"-34")" 
					["product_id"]=> string(24) "5876461865becd2824896ecc" 
					["all_images"]=> string(0) "" 
					["price"]=> string(4) "14.0" 
					["enabled"]=> string(4) "True" 
					["shipping"]=> string(3) "3.0" 
					["inventory"]=> string(3) "200" 
					["size"]=> string(36) "Chinese size XXXL(32"-34")" 
					["id"]=> string(24) "5876461865becd2824896ece" 
					["msrp"]=> string(4) "38.0" 
					["shipping_time"]=> string(3) "2-7" } } 
	["number_sold"]=> string(1) "0" 
	["parent_sku"]=> string(11) "H_WEJDR9352" 
	["id"]=> string(24) "5876461865becd2824896ecc" 
	["date_uploaded"]=> string(10) "01-11-2017" 
	["description"]=> string(291) "Very Soft,Breathable Men's boxer brief. Size: Chinese size XXXL, the waistline is about 80 cm-88 cm(32''-34''), fit US Size M. 4 color:Black,Blue,Brown and Gray. Material:95% bamboo fiber + 5% spandex. The order include: 4pcs, one of each color.	" } 
 */	
	
	private function isProductExist($productid){
		$result = $this->dbhelper->isProductExist($productid);
		if($curproduct = mysql_fetch_array($result)){
			if($curproduct['id'] != null)
				return true;
		}
		return false;
	}
	
	private function isProductVarExist($productvarid){
		$result = $this->dbhelper->isProductVarExist($productvarid);
		if($curproduct = mysql_fetch_array($result)){
			if($curproduct['id'] != null)
				return true;
		}
		return false;
	}
	
	private function insertOnlineProduct($currentProduct){
		if($this->isProductExist($currentProduct['id'])){
			$this->dbhelper->updateOnlineProduct($currentProduct);
		}else{
			$this->dbhelper->insertOnlineProduct($currentProduct);
		}
	}
	
	private function insertOnlineProductVar($currentProductVar){
		if($this->isProductVarExist($currentProductVar['id'])){
			$this->dbhelper->updateOnlineProductVar($currentProductVar);
		}else{
			$this->dbhelper->insertOnlineProductVar($currentProductVar);
		}
	}
}
