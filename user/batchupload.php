<?php
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';

use mysql\dbhelper;


$dbhelper = new dbhelper ();


$salt = $_POST['salt'];
$md5 = $_POST['sign'];
$token = $dbhelper->getJaveUploadAppToken();
$localmd5 = md5($token.$salt);
if(strcmp($md5,$localmd5) !=0){
	echo "sign error";
	exit();
}
 
$accountid = $_POST ['currentAccountid'];
$productName = $_POST ['Product_Name'];
$productName = str_replace ( '"', "''", $productName );
$description = $_POST ['Description'];
$description = str_replace ( '"', "''", $description );
$tags = $_POST ['Tags'];
$uniqueID = $_POST ['Unique_Id'];
$mainImage = $_POST ['Main_Image'];
$extraImages = $_POST ['Extra_Images'];
$colors = $_POST ['colors'];
$sizes = $_POST ['sizes'];
$price = $_POST ['Price'];
$incrementPrice = $_POST ['increment_price'];
$quantity = $_POST ['Quantity'];
$shipping = $_POST ['Shipping'];
$shippingTime = $_POST ['Shipping_Time'];
$MSRP = $_POST ['MSRP'];
$brand = $_POST ['Brand'];
$UPC = $_POST ['UPC'];
$landingPageURL = $_POST ['Landing_Page_URL'];
$productSourceURL = $_POST ['Product_Source_URL'];
$scheduleDate = $_POST ['Schedule_Date'];

if ($productName != null && $description != null && $mainImage != null && $price != null && $uniqueID != null && $quantity != null && $shipping != null && $shippingTime != null && $tags != null) {
	$productarray = array ();
	$productarray ['name'] = $productName;
	$productarray ['brand'] = $brand;
	$productarray ['description'] = $description;
	
	$extraImagesArray = explode ( "|", $extraImages );
	foreach ( $extraImagesArray as $extraImage ) {
		if ($extraImage != null) {
			$productarray ['extra_images'] = $productarray ['extra_images'] .  $extraImage  . '|';
		}
	}
	
	$productarray ['landingPageURL'] = $landingPageURL;
	
	$productarray ['main_image'] = $mainImage;
	$productarray ['MSRP'] = $MSRP;
	$productarray ['price'] = $price;
	$productarray ['parent_sku'] = $uniqueID;
	$productarray ['quantity'] = $quantity;
	$productarray ['shipping'] = $shipping;
	$productarray ['shipping_time'] = $shippingTime;
	$productarray ['tags'] = $tags;
	$productarray ['UPC'] = $UPC;
	$productarray ['productSourceURL'] = $productSourceURL;
	
	$insertSourceResult = $dbhelper->insertProductSource ( $accountid, $productarray );
	if ($insertSourceResult != '1') {
		echo "insert product source failed:" . $uniqueID."\n";
	}
	
	if($colors != null){
		$colors = rtrim($colors,"|");
	}
	
	if(sizes != null){
		$sizes = rtrim($sizes,"|");
	}
	$colorArray = explode ( "|", $colors );
	
	$sizeArray = explode ( "|", $sizes );
	
	foreach ( $colorArray as $color ) {
		$basePrice = $price;
		$sizeCount = 0;
		foreach ( $sizeArray as $size ) {
			if ($color != null && strcmp(trim($color),"") != 0) {
				if ($size != null && strcmp(trim($size),"") != 0) {
					$productarray ['sku'] = $uniqueID . "_" . $color . "_" . $size;
					$productarray ['color'] = $color;
					$productarray ['size'] = $size;
					$productarray ['price'] = $basePrice + $sizeCount * $incrementPrice;
					$sizeCount ++;
				} else {
					$productarray ['sku'] = $uniqueID . "_" . $color;
					$productarray ['color'] = $color;
					$productarray ['price'] = $price;
				}
			} else {
				if ($size != null && strcmp(trim($size),"") != 0) {
					$productarray ['sku'] = $uniqueID . "_" . $size;
					$productarray ['size'] = $size;
					$productarray ['price'] = $basePrice + $sizeCount * $incrementPrice;
					$sizeCount ++;
				} else {
					$productarray ['sku'] = $uniqueID;
					$productarray ['price'] = $price;
				}
			}
			$insertResult = $dbhelper->insertProduct ( $productarray );
			if ($insertResult != '1') {
				echo "insert product failed:" .$uniqueID. "\n";
			}
			
			$productarray ['sku'] = null;
			$productarray ['color'] = null;
			$productarray ['size'] = null;
			$productarray ['price'] = null;
		}
	}
	
	if( $scheduleDate == null || strcmp($scheduleDate,'') == 0){
		$scheduleDate = date('Y-m-d H:i');
	}
	$checkdate = strtotime(trim($scheduleDate));
	if(!$checkdate){
		$scheduleDate = date('Y-m-d H:i');
	}
	
	$productarray ['accountid'] = $accountid;
	$productarray ['scheduledate'] = $scheduleDate;
	
	$scheduleResult = $dbhelper->insertScheduleProduct ( $productarray );
	if ($scheduleResult != '1') {
		echo "insert schedule product failed:" .$uniqueID. "\n";
	}
}else{
	echo "params not right, can't be null";
	echo "params:".$productName.$description.$mainImage.$price.$uniqueID.$quantity.$shipping.$shippingTime.$tags."END\n";
}