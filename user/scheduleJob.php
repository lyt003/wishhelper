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
				
				$vars = $this->dbhelper->getProductVars ( $jobproductid );
				$varsResponse = "V0605";
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
									if ($price > 1) {
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
				$this->dbhelper->updateSettingMsg ( "finished process product:" . $jobproductid . " of " . $date );
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
		
				$tempProduct['accountid'] = $currentAccountid ;
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
						
					$tempVars['accountid'] = $currentAccountid ;
					$this->insertOnlineProductVar($tempVars);
				}
			}
				
			$start += $limit;
		}while ($hasmore); 
		
		$this->dbhelper->updateJobFinished ($accountid, "1", $jobproductid, $date, date ( 'Y-m-d  H:i:s' ));
	}
	
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
