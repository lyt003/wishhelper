<?php
header ( "Content-Type: text/html;charset=utf-8" );

include_once dirname ( '__FILE__' ) . '/cpws/CPWSManager.php';
include_once dirname ( '__FILE__' ) . '/mysql/dbhelper.php';

use cpws\CPWSManager;
use mysql\dbhelper;

$cpwsMain = new CPWSManager();
$products = $cpwsMain->getProducts();

echo "<br/>**********START PROCESS WEProduct********<br/>";

$dbMain = new dbhelper();
var_dump($dbMain);

/**
 * add WE products into mysql;
 * */
/* foreach ($products as $curproduct){
	$dbMain->addWEProduct($curproduct['product_id'], $curproduct['product_sku']);
}
echo "<br/>**********FINISH ADD WEProduct********";
 */
 

 /* 
$shipping = $cpwsMain->getShippingMethod();
print_r($shipping);

echo "<br/>";
$weproductresult = $dbMain->getWEProducts();
while($curresult = mysql_fetch_array($weproductresult)){
	echo "<br/>".$curresult['weproductid'].$curresult['weproductsku'];
}
 */
 
$cpwsMain->queryorder("1630-171223-0001");