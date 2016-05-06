<?php
session_start ();
header ( "Content-Type: text/html;charset=utf-8" );

include_once dirname ( '__FILE__' ).'/mysql/dbhelper.php';
include_once dirname ( '__FILE__' ).'/Wish/WishHelper.php';
include_once dirname ( '__FILE__' ).'/Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . '/user/wconfig.php';

use Wish\WishClient;
use Wish\WishHelper;
use mysql\dbhelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
use Wish\WishResponse;
//phpinfo ();

$dbhelper = new dbhelper ();
$wishHelper = new WishHelper();
echo strtotime ( date ( 'Y-m-d  H:i:s' ) ) . "<br/>";
echo strtotime ( date ( 'Y-m-d  H:i:s' ) ) . "<br/>";
echo 10000 * microtime ( true ) . "<br/>";
echo substr ( 10000 * microtime ( true ), 3,9 ) . "<br/>";

$ttresult = strtotime(trim('   '));
if($ttresult){
	echo "<br/>time true";	
}else{
	echo "<br/>time false";
}

/* $nextday = date ( 'Y-m-d',strtotime('+1 day'));
echo "<br/><br/><br/><br/><br/>nextday:".$nextday;
 */
$date = "2016-05-04";
$curtime = date('Y-m-d  H:i:s');
if(strtotime($curtime) >= strtotime($date)){
	//设定好下次同步的记录，然后完成本次同步。
	$nextdate = date ( 'Y-m-d',strtotime('+1 day',strtotime($date)));
	echo "<br/>nextdate:".$nextdate;
}else{
	echo "<br/>curdate:".$curtime;
}


echo "<br/><br/><br/><br/>START TO PROCESS ORDER:";
$labels = $wishHelper->getUserLabelsArray ( $_SESSION ['userid'] );
$expressinfo = $wishHelper->getExpressInfo ( $_SESSION ['userid'] );

$wishHelper->applyTrackingsForOrders ( "0", $labels, $expressinfo );

echo "<br/><br/><br/><br/>STARTTIME*************";
$initTime =  date ( 'Y-m-d  H:i:s',time());
for ($l=0;$l<5;$l++){
	$curWeek = getPreWeek($initTime);
	echo "<br/><br/><br/><br/>";
	print_r($curWeek);
	echo "<br/>".$curWeek[0]." | ".$curWeek[1];
	$initTime = $curWeek[0];
}

/*
echo "<br/>test promoted optimize";
$accountid = "1";
//添加本次黄钻产品优化记录:
$promotedProducts = $dbhelper->getPromotedProducts($accountid);
$ordersProducts = $dbhelper->getProductsHasOrder($accountid);
	
$hasOrderProductids = array();
while($op = mysql_fetch_array($ordersProducts)){
	$hasOrderProductids[] = $op['product_id'];
}
	
while($promotedProduct = mysql_fetch_array($promotedProducts)){
	$curProductID = $promotedProduct['id'];
	 
	if(in_array($curProductID,$hasOrderProductids)){
		echo "<br/>".$accountid."+".ADDINVENTORY."|Product:".$curProductID;
	}else{
		echo "<br/>".$accountid."+".LOWERSHIPPING."|Product:".$curProductID;
	}
}
echo "<br/>test promoted optimize end";
*/

function getPreWeek($curtime){
	$preendDate = date('Y-m-d',strtotime('last monday',strtotime($curtime)));
	$prestartDate = date('Y-m-d',strtotime('last monday',strtotime($preendDate)));
	$week = array($prestartDate,$preendDate);
	echo "<br/>curtime:".$curtime;
	echo "<br/>preendDate:".$preendDate;
	echo "<br/>prestartDate:".$prestartDate;
	return $week;
}

echo "<br/><br/><br/><br/> date time:";
echo "今天是第几周：".date('W');
$endDate = date('Y-m-d',strtotime('last monday',time()));
$startDate = date('Y-m-d',strtotime('last monday',strtotime($endDate)));
echo "endDate:".$endDate.",startDate:".$startDate;

/** init wish colors
// add wish colors by wishcolor.txt;
$wishcolors = array();
$file = "wishcolor.txt";
$content = file_get_contents($file);
//$wishcolors = explode("\r\n", $content);  //Windows system
$wishcolors = explode("\n", $content);     //Linux system
$colorsz = count($wishcolors);
echo "<br/>colors:".$colorsz;
$i = 1;
foreach ( $wishcolors as $color ) {
	echo "<br/>".$i++."    :".$color;
	$dbhelper->insertColors(trim($color));
}
**/


$expressInfo = array();
$expressResult = $dbhelper->getExpressInfo(1, 1);
while($expressAttr = mysql_fetch_array($expressResult)){
	echo "<br/>values:".$expressAttr['express_attr_name'].$expressAttr['express_attr_value'];
	$expressInfo[$expressAttr['express_attr_name']] = $expressAttr['express_attr_value'];
}

echo "<br/>CONFIG:".YANWEN_USER_ATTR." value:".$expressInfo[YANWEN_USER_ATTR]."<br/>";
foreach ($expressInfo as $ekey=>$eValue){
	echo "<br/>".$ekey.":".$eValue;
}


$key = "label_fdafdkakfdas_12";
if(preg_match("/^label/",$key,$matches)){
	echo "preg yes".$matches[0];
	$sku = explode("_",$key)[1];
	echo "sku".$sku;
}else{
	echo "preg no";
}
echo "<br/>";

/* $sku = 'TESTSKU';
$names = explode("|","Afdae|fdaBBB");
//$dbhelper->insertproductLabel(1, $sku, $dbhelper->insertLabel($names[0], $names[1]));
echo "insert label result:".$dbhelper->insertLabel($names[0], $names[1]); */

$labels = $wishHelper->getUserLabelsArray(1);
foreach ( array_unique($labels) as $labelkey => $labelvalue ) {
	echo "<br/>".$labelkey.$labelvalue;
}



$orderCount = 0;
if($orderCount /2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}

$orderCount++;
if($orderCount % 2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}

$orderCount++;
echo "</br>";
if($orderCount % 2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}

$orderCount++;
echo "</br>";
if($orderCount % 2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}

$orderCount++;
echo "</br>";
if($orderCount % 2 == 0){
	echo "trgradeA".$orderCount;
}else{
	echo "gradeA success".$orderCount;
}


$printTrackingnumbers = "<string>RG228167292CN,RG228167292CN,";
$printTrackingnumbers = substr ( $printTrackingnumbers, 0, strlen ( $printTrackingnumbers ) - 1 ) . "</string>";
echo "tracingnumbers: " . $printTrackingnumbers . "<br/>";

$testStr = 'NWT 4 pcs Mens soft bamboo fiber Underwears Comfort Boxer briefs M 28"-38"';
echo $testStr . "before <br/>";
$result = str_replace ( '"', "''", $testStr ); // use '' replace the " in the sql;
echo $result . "after<br/>";

$colors = null;
$colorArray = explode ( "|", $colors );
if ($colorArray != null)
	foreach ( $colorArray as $color ) {
		if ($color != null)
			echo "color:" . $color . "<br/>";
	}

$array = array ();
for($i = 0; $i < 10; $i ++) {
	$array [$i . "t"] = $i;
}
for($i = 0; $i < 10; $i ++) {
	$array [$i . "t"] = $i;
}
foreach ( $array as $a ) {
	echo "a:" . $a . "<br/>";
}

$colors = "Black";
$sizes = "L|XL|XXL";
$colorArray = explode ( "|", $colors );

$sizeArray = explode ( "|", $sizes );

$skus = array ();
foreach ( $colorArray as $color ) {
	foreach ( $sizeArray as $size ) {
		if ($color != null) {
			if ($size != null) {
				$skus [] = $uniqueID . "_" . $color . "_" . $size;
			} else {
				$skus [] = $uniqueID . "_" . $color;
			}
		} else {
			if ($size != null) {
				$skus [] = $uniqueID . "_" . $size;
			}
		}
	}
}
echo "current sku list:<br/>";
foreach ( $skus as $sku ) {
	echo "sku:" . $sku . "<br/>";
}

$add = 0;
if($add != 0){
	echo "add = 0";
}

$curDate = date('Ymd');
echo "curDate = ".$curDate."<br/>";
echo date("y-m-d H:i:s",time());// H: 24小时制；   h：12小时制

$currentPID = uniqid();

$pid = $dbhelper->getPID();
if($pid != null){
	echo "<br/>111".$currentPID."   ".$pid;
	if(strcmp($pid,$currentPID) != 0){
		echo "<br/>".$currentPID."   ".$pid;
	}
}else{
	echo "<br/>222".$currentPID."   ".$pid;
	$dbhelper->registerPID($currentPID);
}


/*  $dbhelper = new dbhelper ();

$accountid = 2;
if ($client == null || ($accountid != $client->getAccountid ())) {
	$accountAcess = $dbhelper->getAccountToken ( $accountid );
	if ($rows = mysql_fetch_array ( $accountAcess )) {
		$token = $rows ['token'];
		$client = new WishClient ( $token, 'prod' );
		$client->setAccountid ( $accountid );
		$clientid = $rows ['clientid'];
		$clientsecret = $rows ['clientsecret'];
		$refresh_token = $rows ['refresh_token'];
		echo "client account id:".$client->getAccountid()."<br/>";
	}
}


$response = $client->refreshToken ( $clientid, $clientsecret, $refresh_token );
echo "<br/>errorMessage:" . $response->getMessage ();
$values = $response->getResponse ()->{'data'};
$newToken = '0';
$newRefresh_token = '0';
foreach ( $values as $k => $v ) {
	echo 'key  ' . $k . '  value:' . $v;
	if ($k == 'access_token') {
		$newToken = $v;
	}
	if ($k == 'refresh_token') {
		$newRefresh_token = $v;
	}
}
echo "<br/>newToken = " . $newToken . $newRefresh_token;
$dbhelper->updateUserToken ( $accountid, $newToken, $newRefresh_token );
$client = new WishClient ( $newToken, 'prod' );
$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
echo "\n get orders count:" . count ( $unfulfilled_orders ) . "<br/>";


echo "the last, client account id:".$client->getAccountid()."<br/>";
try {
			$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
		} catch ( ServiceResponseException $e ) {
			echo "<br/>error,code:".$e->getStatusCode ();
			if ($e->getStatusCode () == 1015 || $e->getStatusCode () == 4000) {
				echo "<br/>refresh token params:".$clientid.$clientsecret.$refresh_token;
				$response = $client->refreshToken ( $clientid, $clientsecret, $refresh_token );
				echo "<br/>errorMessage:" . $response->getMessage ();
				$values = $response->getResponse ()->{'data'};
				$newToken = '0';
				$newRefresh_token = '0';
				foreach ( $values as $k => $v ) {
					echo 'key  ' . $k . '  value:' . $v;
					if ($k == 'access_token') {
						$newToken = $v;
					}
					if ($k == 'refresh_token') {
						$newRefresh_token = $v;
					}
				}
				echo "<br/>newToken = " . $newToken . $newRefresh_token;
				$dbhelper->updateUserToken ( $accountid, $newToken, $newRefresh_token );
				$client = new WishClient ( $newToken, 'prod' );
				$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
			}
		}
		echo "\n get orders count:" . count ( $unfulfilled_orders ) . "<br/>";
		$orders_count = count ( $unfulfilled_orders );
echo "the last, client account id:".$client->getAccountid()."<br/>";  */

$pwd ="123456";
$hash = md5($pwd);
echo "<br/>hash:".$hash."<br/>";


$addsuccess = 0;
if($addsuccess){
	echo "1";
}else{
	echo "nothing";
}

echo "<br/>";
$data = array();
$curdata = array();
$data['1'] = $curdata;
$curdata[]  = 100;
$curdata[]  = "else";
var_dump($curdata);

echo "<br/>data:";

var_dump($data);
$temp = $data['1'];
$temp[] = "final";

$data['1'] = $curdata;
echo "<br/>final data:";
var_dump($data);

/* 
echo "<br/>";
$curdata = array();
$curdata[]  = "new array100";
$curdata[]  = "new else";
var_dump($curdata);
$data[] = $curdata;

echo "<br/>data:";
var_dump($data); */
?>
<!DOCTYPE html>
<html>
<head>
		<title></title>
		<link href="./css/bootstrap.min.css" rel="stylesheet" media="screen">
		<link href="./css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
</head>

<body>
<div class="container">
    <form action="" class="form-horizontal"  role="form">
        <fieldset>
            <legend>Test</legend>
            <div class="form-group">
                <label for="dtp_input1" class="col-md-2 control-label">DateTime Picking</label>
                <div class="input-group date form_datetime col-md-5" data-date="<?php echo date('Y-m-d')?>" data-date-format="yyyy mm dd - hh:ii" data-link-field="dtp_input1">
                    <input class="form-control" size="16" type="text" value="" readonly>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
					<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                </div>
				<input type="hidden" id="dtp_input1" value="" /><br/>
            </div>
            
            <input type="text" value="<?php echo date('Y-m-d  H:i')?>" id="datetimepicker" data-date-format="yyyy-mm-dd hh:ii">
        </fieldset>
    </form>
</div>

<script type="text/javascript" src="./js/jquery-2.2.0.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="./js/bootstrap.min.js"></script>
<script type="text/javascript" src="./js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="./js/locales/bootstrap-datetimepicker.zh-CN.js" charset="UTF-8"></script>
<script type="text/javascript">
    $('.form_datetime').datetimepicker({
        language: 'zh-CN',
        weekStart: 1,
        todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		forceParse: 0,
        showMeridian: 1
    });
    $('#datetimepicker').datetimepicker({
    	language: 'zh-CN',
        weekStart: 1,
        todayBtn:  1,
		autoclose: 1,
		todayHighlight: 1,
		startView: 2,
		forceParse: 0,
        showMeridian: 1});
</script>

</body>
</html>
