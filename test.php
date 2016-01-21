<?php
header ( "Content-Type: text/html;charset=utf-8" );
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
echo substr ( 10000 * microtime ( true ), 3,9 ) . "<br/>";

$orderCount = 0;
if($orderCount /2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}

$orderCount++;
if($orderCount % 2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}

$orderCount++;
echo "</br>";
if($orderCount % 2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}

$orderCount++;
echo "</br>";
if($orderCount % 2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}

$orderCount++;
echo "</br>";
if($orderCount % 2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}


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


/*  $dbhelper = new dbhelper ();

$accountid = 2;
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


$response = $client->refreshToken ( $clientid, $clientsecret, $refresh_token );
echo "<br/>errorMessage:" . $response->getMessage ();
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
echo "<br/>newToken = " . $newToken . $newRefresh_token;
$dbhelper->updateUserToken ( $accountid, $newToken, $newRefresh_token );
$client = new WishClient ( $newToken, 'prod' );
$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
echo "\n get orders count:" . count ( $unfulfilled_orders ) . "<br/>";


echo "the last, client account id:".$client->getAccountid()."<br/>";
try {
			$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
		} catch ( ServiceResponseException $e ) {
			echo "<br/>error,code:".$e->getStatusCode ();
			if ($e->getStatusCode () == 1015 || $e->getStatusCode () == 4000) {
				echo "<br/>refresh token params:".$clientid.$clientsecret.$refresh_token;
				$response = $client->refreshToken ( $clientid, $clientsecret, $refresh_token );
				echo "<br/>errorMessage:" . $response->getMessage ();
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
				echo "<br/>newToken = " . $newToken . $newRefresh_token;
				$dbhelper->updateUserToken ( $accountid, $newToken, $newRefresh_token );
				$client = new WishClient ( $newToken, 'prod' );
				$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
			}
		}
		echo "\n get orders count:" . count ( $unfulfilled_orders ) . "<br/>";
		$orders_count = count ( $unfulfilled_orders );
echo "the last, client account id:".$client->getAccountid()."<br/>";  */

$pwd ="123456";
$hash = md5($pwd);
echo "<br/>hash:".$hash;

?>
<a href="addTrackingData.php">新增单号</a>