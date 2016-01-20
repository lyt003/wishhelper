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
	do {
		$duringtime = $dbhelper->getSettingDuringTime ();
		if ($rows = mysql_fetch_array ( $duringtime )) {
			sleep ( $rows ['during_time'] );
		} else {
			sleep ( 120 ); // sleep for 2 minutes defaultly;
		}
		$dbhelper->updateSettingCount ();
		$curDate = date ( 'Ymd' );
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
					$currentProduct ['msrp'] = $product ['MSRP'];
					$currentProduct ['shipping_time'] = $product ['shipping_time'];
					$currentProduct ['main_image'] = $product ['main_image'];
					$currentProduct ['parent_sku'] = $product ['parent_sku'];
					$currentProduct ['brand'] = $product ['brand'];
					$currentProduct ['landing_page_url'] = $product ['landingPageURL'];
					$currentProduct ['upc'] = $product ['UPC'];
					$currentProduct ['extra_images'] = $product ['extra_images'];
					
					try {
						$prod_res = $client->createProduct ( $currentProduct );
					} catch ( ServiceResponseException $e ) {
						if ($e->getStatusCode () == 1015 || $e->getStatusCode () == 4000) {
							$response = $client->refreshToken ( $clientid, $clientsecret, $refresh_token );
							$log = $log. "<br/>errorMessage:" . $response->getMessage ();
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
							$log = $log. "<br/>newToken = " . $newToken . $newRefresh_token;
							$dbhelper->updateSettingMsg ( $log );
							$dbhelper->updateUserToken ( $accountid, $newToken, $newRefresh_token );
							$client = new WishClient ( $newToken, 'prod' );
							$prod_res = $client->createProduct ( $currentProduct );
						}
						$log = $log.$e->getErrorMessage()." of account " . $accountid."<br/>";
						$dbhelper->updateSettingMsg ( $log );
					}
					print_r ( $prod_res );
					if ($prod_res != null) {
						$log = $log."add product success<br/>";
						$dbhelper->updateSettingMsg ( $log );
						$addProduct = 1;
					} else {
						$log = $log. "add product failed<br/>";
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
					$currentProductVar ['msrp'] = $product ['MSRP'];
					$currentProductVar ['shipping_time'] = $product ['shipping_time'];
					$currentProductVar ['main_image'] = $product ['main_image'];
					$prod_var = $client->createProductVariation ( $currentProductVar );
					print_r ( $prod_var );
					if (prod_var != null) {
						$log = $log. "add product var success<br/>";
						$dbhelper->updateSettingMsg ( $log );
					}
				}
			}
			
			$dbhelper->updateScheduleFinished ( $productInfo );
			$log = $log. "finish to process parent_sku:" . $parent_sku . " client account id:" . $client->getAccountid ();
			$dbhelper->updateSettingMsg ( $log );
		}
	} while ( $dbhelper->isScheduleRunning () );
}
