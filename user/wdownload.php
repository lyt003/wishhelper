<?php
header("Content-Type: text/html;charset=utf-8");
session_start ();
include_once dirname ( '__FILE__' ) . '/wconfig.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
use Wish\WishHelper;

$wishHelper = new WishHelper();

$expressinfo = $wishHelper->getExpressInfo($_SESSION ['userid']);  
$post_header = array (
		'Authorization: basic '.$expressinfo[YANWEN_API_TOKEN],
		'Content-Type: text/xml; charset=utf-8'
);

$curl = curl_init();
$url = $expressinfo[YANWEN_SERVICE_URL] . "/Users/" . $expressinfo[YANWEN_USER_ATTR] . "/Expresses/"."A10x10LCLabel";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl,CURLOPT_HTTPHEADER, $post_header); 

$numbers = $wishHelper->getTrackingNumbersForLabel($_SESSION ['userid'],PROVIDER_YANWEN);
session_commit();
$xmldata = '<string>' . substr ( $numbers, 0, strlen ( $numbers ) - 1 ) . '</string>';

curl_setopt($curl, CURLOPT_POSTFIELDS, $xmldata);

$result = curl_exec($curl);
$error = curl_error($curl);
curl_close($curl);

$filename = "label.pdf";
$filesize = file_put_contents($filename, $result);
header('Cache-Control: public');
header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.$filesize);

readfile($filename); 
$wishHelper->updateHasDownloadLabel($numbers);

