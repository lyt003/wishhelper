<?php
include 'Wish/WishClient.php';
include 'mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;
//phpinfo ();
echo strtotime ( date ( 'Y-m-d  H:i:s' ) ) . "<br/>";
echo strtotime ( date ( 'Y-m-d  H:i:s' ) ) . "<br/>";
echo 10000 * microtime ( true ) . "<br/>";
echo substr ( 10000 * microtime ( true ), 8 ) . "<br/>";

$printTrackingnumbers = "<string>RG228167292CN,RG228167292CN,";
$printTrackingnumbers = substr ( $printTrackingnumbers, 0, strlen ( $printTrackingnumbers ) - 1 ) . "</string>";
echo "tracingnumbers: " . $printTrackingnumbers . "<br/>";

$testStr = 'NWT 4 pcs Mens soft bamboo fiber Underwears Comfort Boxer briefs M 28"-38"';
echo $testStr . "before <br/>";
$result = str_replace ( '"', "''", $testStr ); // use '' replace the " in the sql;
echo $result . "after<br/>";

$colors = null;
$colorArray = explode ( "|", $colors );
if ($colorArray != null)
	foreach ( $colorArray as $color ) {
		if ($color != null)
			echo "color:" . $color . "<br/>";
	}

$array = array ();
for($i = 0; $i < 10; $i ++) {
	$array [$i . "t"] = $i;
}
for($i = 0; $i < 10; $i ++) {
	$array [$i . "t"] = $i;
}
foreach ( $array as $a ) {
	echo "a:" . $a . "<br/>";
}

$colors = "Black";
$sizes = "L|XL|XXL";
$colorArray = explode ( "|", $colors );

$sizeArray = explode ( "|", $sizes );

$skus = array ();
foreach ( $colorArray as $color ) {
	foreach ( $sizeArray as $size ) {
		if ($color != null) {
			if ($size != null) {
				$skus [] = $uniqueID . "_" . $color . "_" . $size;
			} else {
				$skus [] = $uniqueID . "_" . $color;
			}
		} else {
			if ($size != null) {
				$skus [] = $uniqueID . "_" . $size;
			}
		}
	}
}
echo "current sku list:<br/>";
foreach ( $skus as $sku ) {
	echo "sku:" . $sku . "<br/>";
}

$add = 0;
if($add != 0){
	echo "add = 0";
}

$curDate = date('Ymd');
echo "curDate = ".$curDate."<br/>";
echo date("y-m-d H:i:s",time());// H: 24小时制；   h：12小时制


$dbhelper = new dbhelper ();
$accountid = 1;
if ($client == null || ($accountid == $client->getAccountid ())) {
	$accountAcess = $dbhelper->getAccountToken ( $accountid );
	if ($rows = mysql_fetch_array ( $accountAcess )) {
		$token = $rows ['token'];
		$client = new WishClient ( $token, 'prod' );
		$client->setAccountid ( $accountid );
		$clientid = $rows ['clientid'];
		$clientsecret = $rows ['clientsecret'];
		$refresh_token = $rows ['refresh_token'];
		echo "client account id:".$client->getAccountid()."<br/>";
	}
}

$accountid = 3;
echo "equal: ".($accountid != $client->getAccountid ());
if ($client == null || ($accountid != $client->getAccountid ())) {
	$accountAcess = $dbhelper->getAccountToken ( $accountid );
	if ($rows = mysql_fetch_array ( $accountAcess )) {
		$token = $rows ['token'];
		$client = new WishClient ( $token, 'prod' );
		$client->setAccountid ( $accountid );
		$clientid = $rows ['clientid'];
		$clientsecret = $rows ['clientsecret'];
		$refresh_token = $rows ['refresh_token'];
		echo "client account id:".$client->getAccountid()."<br/>";
	}
}

echo "the last, client account id:".$client->getAccountid()."<br/>";