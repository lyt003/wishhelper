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

$accountids = $wishposthelper->getWishPostAccounts($userid);
$wpdownloadurl = array();
$printlang = 1;
$printcode = 1;
foreach ($accountids as $accountid){
	echo "<br/>accountid:".$accountid;
	$barcodes = $wishposthelper->getWishPostNumbersForLabel($accountid);
	
	if(count($barcodes)>0){
		$downloadurl = $wishposthelper->downloadlabels($accountid, $printlang, $printcode, $barcodes);
		$wpdownloadurl[] = $downloadurl;
		echo "<br/>downloadurl:".$downloadurl;
		if($downloadurl != null && strcmp($downloadurl,'') != 0){
			echo "<br/> update downloadlables";
			$wishHelper->updateHasDownloadLabelForArray($barcodes);
			echo '<script>window.open("'.$downloadurl.'")</script>';
		}
	}
}
