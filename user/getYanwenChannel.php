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

session_commit();

$curl = curl_init();
$url = $expressinfo[YANWEN_SERVICE_URL] . "/Users/" . $expressinfo[YANWEN_USER_ATTR] . "/GetChannels";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_GET, true);
curl_setopt($curl,CURLOPT_HTTPHEADER, $post_header);

$result = curl_exec($curl);
$error = curl_error($curl);
curl_close($curl);

$resultXML = simplexml_load_string ( $result );
/* echo "<br/>resultXML:";
var_dump ( $resultXML ); */

$callsuccess = $resultXML->CallSuccess;
$Channelscoll = $resultXML->ChannelCollection;
$Channels = $Channelscoll->ChannelType;
echo "<br/>Channels:".count($Channels);
foreach ($Channels as $currchannel){
	echo "<br/>";
	echo $currchannel->Id;
	echo ":".$currchannel->Name;
	echo "    ".$currchannel->Status;
}