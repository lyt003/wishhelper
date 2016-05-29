<?php
session_start ();
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
use mysql\dbhelper;

header ( "Content-Type: text/html;charset=utf-8" );
$dbhelper = new dbhelper ();
$username = $_SESSION ['username'];
session_commit();

if ($username == null) { // 未登录
	header ( "Location:./wlogin.php?errorMsg=请先登录" );
	exit ();
}

echo "<br/><br/><br/>";
$accountid = $_GET['uid'];
$productid = $_GET['pid'];

if($accountid != null || $productid != null){
	$dbhelper->deleteProduct($accountid, $productid);
	echo "删除产品".$productid."完成";
}else{
	echo "参数不正确";	
}
?>
