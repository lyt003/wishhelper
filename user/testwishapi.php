<?php
include dirname ( '__FILE__' ) . './Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
use Wish\WishClient;
use mysql\dbhelper;


$dbhelper = new dbhelper ();

$pid = $_GET['pid'];
$accountid = $_GET['aid'];

$accountInfo = $dbhelper->getAccountToken($accountid);
$accounts = array ();
if($rows = mysql_fetch_array ( $accountInfo )){
	$accounts ['token'] = $rows ['token'];
	$accounts ['refresh_token'] = $rows ['refresh_token'];
	$accounts ['accountname'] = $rows ['accountname'];
}

$client = new WishClient ($accounts ['token'], 'prod' );

echo "<br/>pid = ".$pid.", and accountid = ".$accountid;
$params = array('id'=>$pid);
try {
	$response = $client->getResponse('GET','product',$params);
	echo "<br/>Print_r:";
	print_r($response);
} catch ( ServiceResponseException $e ) {
	echo "<br/>error:".$e->getResponse()->getData();
}

