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
$uniqueID = 'htest4';
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

$prod_res = null;

// add product;
$products = $dbhelper->getProducts ( $uniqueID );
$addProduct = 0;
$prod_res = null;
if ($product = mysql_fetch_array ( $products )) {
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
}

try {
	$prod_res = $client->createProduct ( $currentProduct );
} catch ( ServiceResponseException $e ) {
	print_r ( $e );
}
echo "<br/>print prod_res:<br/>";
print_r ( $prod_res );
if ($prod_res != null) {
	echo "add product success<br/>";
	$addProduct = 1;
} else {
	echo "add product failed<br/>";
}

echo "finish to process parent_sku:" . $parent_sku . " client account id:" . $client->getAccountid ();
