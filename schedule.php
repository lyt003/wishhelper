<?php
include 'Wish/WishClient.php';
include 'mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;
ignore_user_abort ();
set_time_limit ( 0 );
$dbhelper = new dbhelper ();
$client = null;
$isRunning = $_GET ['isRunning'];
if ($isRunning == 1) {
	$dbhelper->stopScheduleRunning ();
	$dbhelper->resetSettingCount ();
} else {
	$dbhelper->startScheduleRunning ();
	$currentPID = uniqid();
	do {
		$duringtime = $dbhelper->getSettingDuringTime ();
		if ($rows = mysql_fetch_array ( $duringtime )) {
			sleep ( $rows ['during_time'] );
		} else {
			sleep ( 120 ); // sleep for 2 minutes defaultly;
		}
		
		$pid = $dbhelper->getPID();
		if($pid != null){
			if(strcmp($pid,$currentPID) != 0){
				die();
			}
		}
		$dbhelper->registerPID($currentPID);
		
		$dbhelper->updateSettingCount ();
		$curDate = date ( 'Y-m-d  H:i' );
		$productsInfo = $dbhelper->getScheduleProducts ( $curDate );
		while ( $productInfo = mysql_fetch_array ( $productsInfo ) ) {
			$parent_sku = $productInfo ['parent_sku'];
			$accountid = $productInfo ['accountid'];
			if ($client == null || ($accountid != $client->getAccountid ())) {
				$accountAcess = $dbhelper->getAccountToken ( $accountid );
				if ($rows = mysql_fetch_array ( $accountAcess )) {
					$token = $rows ['token'];
					$client = new WishClient ( $token, 'prod' );
					$client->setAccountid ( $accountid );
					$clientid = $rows ['clientid'];
					$clientsecret = $rows ['clientsecret'];
					$refresh_token = $rows ['refresh_token'];
					$dbhelper->updateSettingMsg ( "get new client of account:" . $accountid );
				} else {
					$dbhelper->updateSettingMsg ( "failed to get token of account:" . $accountid );
				}
			} else {
				$dbhelper->updateSettingMsg ( "client account id:" . $client->getAccountid () );
			}
			$products = $dbhelper->getProducts ( $parent_sku );
			$log = "process parent_sku:" . $parent_sku . " client account id:" . $client->getAccountid ();
			$dbhelper->updateSettingMsg ( $log );
			$addProduct = 0;
			$addSuccess = 1;
			$prod_res = null;
			while ( $product = mysql_fetch_array ( $products ) ) {
				if ($addProduct == 0) { // add product;
					$currentProduct = array ();
					$currentProduct ['name'] = $product ['name'];
					$currentProduct ['description'] = $product ['description'];
					$currentProduct ['tags'] = $product ['tags'];
					$currentProduct ['sku'] = $product ['sku'];
					if ($product ['color'] != null)
						$currentProduct ['color'] = $product ['color'];
					if ($product ['size'] != null)
						$currentProduct ['size'] = $product ['size'];
					$currentProduct ['inventory'] = $product ['quantity'];
					$currentProduct ['price'] = $product ['price'];
					$currentProduct ['shipping'] = $product ['shipping'];
					if ($product ['MSRP'] != null)
						$currentProduct ['msrp'] = $product ['MSRP'];
					$currentProduct ['shipping_time'] = $product ['shipping_time'];
					$currentProduct ['main_image'] = $product ['main_image'];
					$currentProduct ['parent_sku'] = $product ['parent_sku'];
					if ($product ['brand'] != null)
						$currentProduct ['brand'] = $product ['brand'];
					if ($product ['landingPageURL'] != null)
						$currentProduct ['landing_page_url'] = $product ['landingPageURL'];
					if ($product ['UPC'] != null)
						$currentProduct ['upc'] = $product ['UPC'];
					if ($product ['extra_images'] != null)
						$currentProduct ['extra_images'] = $product ['extra_images'];
					
					try {
						$prod_res = $client->createProduct ( $currentProduct );
					} catch ( ServiceResponseException $e ) {
						if ($e->getStatusCode () == 1015 || $e->getStatusCode () == 1016) {
							$response = $client->refreshToken ( $clientid, $clientsecret, $refresh_token );
							$log = $log . "<br/>errorMessage:" . $response->getMessage ();
							$dbhelper->updateSettingMsg ( $log );
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
							$log = $log . "<br/>newToken = " . $newToken . $newRefresh_token;
							$dbhelper->updateSettingMsg ( $log );
							$dbhelper->updateUserToken ( $accountid, $newToken, $newRefresh_token );
							$client = new WishClient ( $newToken, 'prod' );
							$prod_res = $client->createProduct ( $currentProduct );
						}
						$log = $log . $e->getErrorMessage () . " of account " . $accountid . "<br/>";
						$dbhelper->updateSettingMsg ( $log );
						if(stristr($e->getErrorMessage(),"You have already added SKU")){
							$addProduct = 1;
							//$dbhelper->updateScheduleError($productInfo, '222You have already added SKU '.$product ['sku']);
						}else{
							$dbhelper->updateScheduleError($productInfo, 'add product faild '.$product ['sku'].':'.$e->getStatusCode().'-'.str_replace ( '"', "''", $e->getErrorMessage())."  ".date("y-m-d H:i:s",time()));
							$addSuccess = 0;
						}
					}
					if ($prod_res != null) {
						$log = $log . "add product success<br/>";
						$dbhelper->updateSettingMsg ( $log );
						$addProduct = 1;
					} else {
						$log = $log . "add product failed<br/>";
						$dbhelper->updateSettingMsg ( $log );
					}
				} else { // add product variation
					$currentProductVar = array ();
					$currentProductVar ['parent_sku'] = $product ['parent_sku'];
					$currentProductVar ['sku'] = $product ['sku'];
					if ($product ['color'] != null)
						$currentProductVar ['color'] = $product ['color'];
					if ($product ['size'] != null)
						$currentProductVar ['size'] = $product ['size'];
					$currentProductVar ['inventory'] = $product ['quantity'];
					$currentProductVar ['price'] = $product ['price'];
					$currentProductVar ['shipping'] = $product ['shipping'];
					if ($product ['MSRP'] != null)
						$currentProductVar ['msrp'] = $product ['MSRP'];
					$currentProductVar ['shipping_time'] = $product ['shipping_time'];
					$currentProductVar ['main_image'] = $product ['main_image'];
					try {
						$prod_var = $client->createProductVariation ( $currentProductVar );
					} catch ( ServiceResponseException $e ) {
						if(!stristr($e->getErrorMessage(),"has already been added")){
							$dbhelper->updateScheduleError($productInfo, 'add product var failed '.$product ['sku'].':'.$e->getStatusCode().'-'.str_replace ( '"', "''", $e->getErrorMessage())."  ".date("y-m-d H:i:s",time()));
							$addSuccess = 0;
						}
						$log = $log . "add product var failed<br/>";
						$dbhelper->updateSettingMsg ( $log );
					}
				}
			}
			if($addSuccess){
				$dbhelper->updateScheduleFinished ( $productInfo );
				$log = $log. "finish to process parent_sku:" . $parent_sku . " client account id:" . $client->getAccountid ();
				$dbhelper->updateSettingMsg ( $log );
			}else{
				$log = $log. "failed to process parent_sku:" . $parent_sku . " client account id:" . $client->getAccountid ();
				$dbhelper->updateSettingMsg ( $log );
			}
		}
	} while ( $dbhelper->isScheduleRunning () );
}
