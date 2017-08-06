<?php
header("Content-Type: text/html;charset=utf-8");
session_start ();
include_once dirname ( '__FILE__' ) . '/wconfig.php';
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
use mysql\dbhelper;
use Wish\WishHelper;

$dbhelper = new dbhelper ();
$wishhelper = new WishHelper();
$username = $_SESSION ['username'];
$userid = $_SESSION ['userid'];
session_commit();

$result = $dbhelper->getUserToken ( $username );

$accounts = array ();
$i = 0;
while ( $rows = mysql_fetch_array ( $result ) ) {
	if($rows ['token'] != null && strlen($rows ['token']) > 1){
		$accounts ['accountid' . $i] = $rows ['accountid'];
		$i ++;
	}
}

$productslist = array();
for($ut = 0; $ut < $i; $ut ++) {
	$ordersNotUpload = $dbhelper->getOrdersForUploadTracking ( $accounts ['accountid' . $ut] );
	while ( $orderUpload = mysql_fetch_array ( $ordersNotUpload ) ) {
		
		$skuvalue = $orderUpload['sku']."_".$orderUpload['color']."_".$orderUpload['size'];
		$key = md5($skuvalue);
		
		$currproduct = $productslist[$key];
		if($currproduct == null)
			$currproduct = array();
		$currproduct['sku'] = $skuvalue;
		$currproduct['quantity'] += $orderUpload['quantity'];
		
		$productslist[$key] = $currproduct;
		
		$wishhelper->updateproductInventory($accounts ['accountid' . $ut], $orderUpload['sku'], $orderUpload['quantity'], INVENTORY_OUT, $orderUpload['orderid']);
	}
}

echo "Date:".date("Y/m/d H:m:s")."<br/>";
if(count($productslist) > 0){
	foreach ($productslist as $productkey=>$productvalue){
		echo $productvalue['sku']."  :  ".$productvalue['quantity'].';<br/>';
	}	
}else {
	echo "暂无需要处理的订单";
}

