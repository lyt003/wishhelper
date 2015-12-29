<?php
include 'mysql/dbhelper.php';
use mysql\dbhelper;

header ( "Content-type:application/vnd.ms-excel" );
header ( "Content-Disposition:filename=test.xls" );
$dbhelper = new dbhelper ();
$result = $dbhelper->getUSOrders ();

$preTransactionid = null;
$curTransactionid = null;
while ( $rows = mysql_fetch_array ( $result ) ) {
	
	$curTransactionid = $rows ['transactionid'];
	if (strcmp ( $preTransactionid, $curTransactionid ) == 0) {
		echo " + " . $rows ['sku'] . ":" . $rows ['color'] . " " . $rows ['size'] . " " . "*" . $rows ['quantity'];
	} else {
		echo "\t\n";
		$preTransactionid = $curTransactionid;
		
		echo $rows ['transactionid'] . "\t";
		echo "\t";
		$gsName = $rows ['productname'];
		if (stripos ( $gsName, "earring" ) != false) {
			echo "4\t";
		} else if (stripos ( $gsName, "wear" ) != false) {
			echo "1\t";
		} else if (stripos ( $gsName, "cami" ) != false) {
			echo "7\t";
		} else if (stripos ( $gsName, "sticker" ) != false) {
			echo "sticker\t";
		} else {
			echo "3\t";
		}
		
		echo $rows ['quantity'] . "\t";
		
		echo $rows ['name'] . "\t";
		echo $rows ['streetaddress1'] . "\t";
		echo $rows ['streetaddress2'] . "\t";
		echo "\t";
		echo $rows ['city'] . "\t";
		echo $rows ['state'] . "\t";
		echo $rows ['zipcode'] . "\t";
		echo "United States\t";
		echo $rows ['phonenumber'] . "\t";
		echo "\t";
		echo $rows ['sku'] . ":" . $rows ['color'] . " " . $rows ['size'] . " " . "*" . $rows ['quantity'];
	}
}
