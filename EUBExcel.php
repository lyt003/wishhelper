<?php
include 'mysql/dbhelper.php';
use mysql\dbhelper;

header ( "Content-type:application/vnd.ms-excel" );
header ( "Content-Disposition:filename=test.xls" );
$dbhelper = new dbhelper ();
$result = $dbhelper->getUSOrders();

while ($rows = mysql_fetch_array($result)){
	echo $rows['orderid']."\t";	
	echo "\t";
	
	$gsName = $rows['productname'];
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
	
	echo $rows['quantity']."\t";
	
	echo $rows['name']."\t";
	echo $rows['streetaddress1']."\t";
	echo $rows['streetaddress2']."\t";
	echo "\t";
	echo $rows['city']."\t";
	echo $rows['state']."\t";
	echo $rows['zipcode']."\t";
	echo "United States\t";
	echo $rows['phonenumber']."\t";
	echo "\t";
	echo $rows['sku'].":".$rows['color']." ".$rows['size']." "."*".$rows['quantity']."\t\n";
}
