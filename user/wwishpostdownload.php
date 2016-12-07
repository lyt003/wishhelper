<?php
header("Content-Type: text/html;charset=utf-8");
session_start ();
include_once dirname ( '__FILE__' ) . '/wconfig.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
include_once dirname ( '__FILE__' ) . './wishpost/Wishposthelper.php';
use Wish\WishHelper;
use wishpost\Wishposthelper;

$userid = $_SESSION ['userid'];
session_commit();

$wishHelper = new WishHelper();
$wishposthelper = new Wishposthelper();
echo "<br/>start userid:".$userid;

$accountids = $wishposthelper->getWishPostAccounts($userid);
$wpdownloadurl = array();
$printlang = 1;
$printcode = 1;
echo "<br/>get result:";
var_dump($accountids);
foreach ($accountids as $accountid){
	echo "<br/>accountid:".$accountid;
	$barcodes = $wishposthelper->getWishPostNumbersForLabel($accountid);
	
	if(count($barcodes)>0){
		$downloadurl = $wishposthelper->downloadlabels($accountid, $printlang, $printcode, $barcodes);
		$wpdownloadurl[] = $downloadurl;
		echo "<br/>downloadurl:".$downloadurl;
	}
}

$index = 0;
$result = '';
foreach ($wpdownloadurl as $url){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $post_header);
	
	$result .= curl_exec($curl);
	$error = curl_error($curl);
	curl_close($curl);
}
	
	$filename = "label.pdf";
	$filesize = file_put_contents($filename, $result);
	header('Cache-Control: public');
	header('Content-type: application/pdf');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Content-Length: '.$filesize);
	
	readfile($filename);
	//$wishHelper->updateHasDownloadLabel($numbers);
