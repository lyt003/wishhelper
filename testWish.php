<?php
include 'Wish/WishClient.php';
include 'mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;

$dbhelper = new dbhelper ();
$client = null;

$accountid = '1';
$uniqueID = 'N160';
if ($client == null || ($accountid != $client->getAccountid ())) {
	$accountAcess = $dbhelper->getAccountToken ( $accountid );
	if ($rows = mysql_fetch_array ( $accountAcess )) {
		$token = $rows ['token'];
		$client = new WishClient ( $token, 'prod' );
		$client->setAccountid ( $accountid );
		$clientid = $rows ['clientid'];
		$clientsecret = $rows ['clientsecret'];
		$refresh_token = $rows ['refresh_token'];
		echo "get new client of account:" . $accountid;
	} else {
		echo "failed to get token of account:" . $accountid;
	}
} else {
	echo "client account id:" . $client->getAccountid ();
}
/*
//test lowesttotalprice:
$jobproductid = '53bea5ef8e0d6c0cf96f562b';

$vars = $dbhelper->getProductVars ($jobproductid);
$date = date('Y-m-d');
$varsResponse = "";
if ( $var = mysql_fetch_array ( $vars ) ) {
	$currSKU = $var ['sku'];
	echo "<br/>currSKU:".$currSKU;
	try {
		$productVar = $client->getProductVariationBySKU ( $currSKU );
		echo "<br/>ProductVar:";
		print_r($productVar);

		$lowesttotalprice = $dbhelper->getLowesttotalprice($jobproductid, $accountid);
		echo "<br>lowestprice:".$lowesttotalprice;
		$shipping = $productVar->shipping;
		$price = $productVar->price;
		
		if($lowesttotalprice == null || $shipping + $price > $lowesttotalprice){
			
			if ($shipping > 1) {
				$params ['shipping'] = $shipping - 0.01;
				echo "<br/>update shipping to ".$params['shipping'];
			} else {
				if ($price > 1) {
					$params ['price'] = $price - 0.01;
					echo "<br/>update price to ".$params['price'];
				}
			}
		}else {
			echo "<br/> has reached the lowestprice,didn't update price  ";
		}
		
	} catch ( ServiceResponseException $e ) {
		echo "<br/>Error:";
		print_r ( $e );
		echo "<br/>Error Message:".$e->getErrorMessage();
		echo "<br/>";
	}
}
 */

/* print_r($client);
$jobproductid = '54578c68d26bc71607383832';
$vars = $dbhelper->getProductVars ($jobproductid);
$date = date('Y-m-d');
$varsResponse = "";
if ( $var = mysql_fetch_array ( $vars ) ) {
	$currSKU = $var ['sku'];
	echo "<br/>currSKU:".$currSKU;
	try {
		$productVar = $client->getProductVariationBySKU ( $currSKU );
		echo "<br/>ProductVar:";
		print_r($productVar);
	} catch ( ServiceResponseException $e ) {
		echo "<br/>Error:";
		print_r ( $e );
		echo "<br/>Error Message:".$e->getErrorMessage();
		echo "<br/>";
		$dbhelper->updateJobMsg ("1", $jobproductid, $date, "Failed to get productvars of productid " . $e->getErrorMessage () );
	}
		
	$enabled = $productVar->enabled;
	echo  "<br/>enabled:".$enabled;
	if (strcmp ( $enabled, 'True' ) == 0) {
		$params = array ();
		$params ['sku'] = $currSKU;

		if (strcmp ( $operator, LOWERSHIPPING ) == 0) {
			$shipping = $productVar->shipping;
			$price = $productVar->price;
			if ($shipping > 1) {
				$params ['shipping'] = $shipping - 0.01;
			} else {
				if ($price > 1) {
					$params ['price'] = $price - 0.01;
				}
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

		echo "<br/>process jobs";
		if (count ( $params ) > 1) {
			try {
				$updateResponse = $client->updateProductVarByParams ( $params );
				echo "<br/>finished jobs";
			} catch ( ServiceResponseException $e ) {
				$dbhelper->updateJobMsg ($accountid, $jobproductid, $date, "Failed to updateProductVar of SKU " . $currSKU . "   " . $e->getErrorMessage () );
			}
			$varsResponse .= $updateResponse->getMessage ();
		}
	} else {
		$varsResponse .= " SKU" . $currSKU . " has disabled";
	} 
	echo "<br/>VarRespones:".$varsResponse;
}


$prod_res = null; */

// add product;
$products = $dbhelper->getProducts ( $uniqueID );
$addProduct = 0;
$prod_res = null;
while ($product = mysql_fetch_array ( $products )) {
	echo "<br/>Current product SKU:".$product['sku'];
	/*
	 * $currentProduct = array ();
	 * $currentProduct ['name'] = 'fashion test wall sticker';
	 * $currentProduct ['description'] = 'fashion test wall sticker';
	 * $currentProduct ['tags'] = 'wall sticker,home wall sticker, 3D wall sticker, broken wall sticker,red leaves wall sticker,goose wall sticker,living room wall sticker, fashion wall sticker,3D broken wall sticker,pool wall sticker';
	 * $currentProduct ['sku'] = 'htest1';
	 * $currentProduct ['inventory'] = 10;
	 * $currentProduct ['price'] = '7.5';
	 * $currentProduct ['shipping'] = 2;
	 * $currentProduct ['shipping_time'] = '10-30';
	 * $currentProduct ['main_image'] = 'http://img.china.alibaba.com/img/ibank/2015/400/592/2595295004_1929931971.jpg';
	 * $currentProduct ['extra_images'] = 'http://img.china.alibaba.com/img/ibank/2015/400/592/2595295004_1929931971.jpg';
	 * $currentProduct ['parent_sku'] = 'htest1';
	 */
	if ($addProduct == 0) {
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
		
		echo "product:<br/>";
		print_r ( $product );
		
		try {
			$prod_res = $client->createProduct ( $currentProduct );
		} catch ( ServiceResponseException $e ) {
			echo "<br/>ERROR:";
			print_r ( $e );
			if(stristr($e->getErrorMessage(),"You have already added SKU")){
				$addProduct = 1;
				echo "You have already added SKU".$e->getErrorMessage();				
				//$dbhelper->updateScheduleError($productInfo, '222You have already added SKU '.$product ['sku']);
			}else{
				//$dbhelper->updateScheduleError($productInfo, 'add product faild '.$product ['sku'].':'.$e->getStatusCode().'-'.str_replace ( '"', "''", $e->getErrorMessage())."  ".date("y-m-d H:i:s",time()));
				$addSuccess = 0;
			}
		}
		
		echo "<br/>print prod_res:<br/>";
		print_r ( $prod_res );
		if ($prod_res != null) {
			echo "add product success<br/>";
			$addProduct = 1;
		} else {
			echo "add product failed<br/>";
		}
	}else { // add product variation
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




echo "finish to process parent_sku:" . $parent_sku . " client account id:" . $client->getAccountid ();
