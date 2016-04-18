<?php
session_start ();
include dirname ( '__FILE__' ) . './Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
use Wish\WishClient;
use mysql\dbhelper;
use Wish\WishHelper;
use Wish\Model\WishTracker;
use Wish\Exception\ServiceResponseException;
header ( "Content-Type: text/html;charset=utf-8" );
$dbhelper = new dbhelper ();
$wishHelper = new WishHelper ();

$username = $_SESSION ['username'];
if ($username == null) { // 未登录
	$type = $_GET ['type'];
	if (strcmp ( $type, "register" ) == 0) {
		$email = $_POST ["email"];
		$username = $_POST ["username"];
		$password = $_POST ["password"];
		$check = $dbhelper->queryUser ( $username, $email );
		$checkrow = mysql_fetch_array ( $check );
		if ($checkrow) {
			if ($checkrow ['username'] == $username) {
				header ( "Location:./wregister.php?errorMsg=该用户已经存在" );
				exit ();
			}
			if ($checkrow ['email'] == $email) {
				header ( "Location:./wregister.php?errorMsg=该邮箱地址已经被注册" );
				exit ();
			}
		} else {
			$result = $dbhelper->createUser ( $username, md5 ( $password ), $email );
			if ($result != 0) {
				$_SESSION ['username'] = $username;
				$_SESSION ['email'] = $email;
				$_SESSION ['userid'] = $result;
			} else {
				header ( "Location:./wregister.php?errorMsg=注册失败" );
				exit ();
			}
		}
	} else {
		// login;
		$username = $_POST ["username"];
		$password = $_POST ["password"];
		
		$dbhelper = new dbhelper ();
		$result = $dbhelper->userLogin ( $username, md5 ( $password ) );
		$row = mysql_fetch_array ( $result );
		if ($row) {
			$_SESSION ['username'] = $row ['username'];
			$_SESSION ['email'] = $row ['email'];
			$_SESSION ['userid'] = $row ['userid'];
		} else {
			header ( "Location:./wlogin.php?errorMsg=登录失败" );
			exit ();
		}
	}
}

$currentUserid = $_SESSION ['userid'];
session_commit();
// 已登录
$result = $dbhelper->getUserToken ( $username );
$accounts = array ();
$i = 0;
echo "<br/><br/><br/><br/><br/>";
while ( $rows = mysql_fetch_array ( $result ) ) {
	if($rows ['token'] != null && strlen($rows ['token']) > 1){
		$accounts ['clientid' . $i] = $rows ['clientid'];
		$accounts ['clientsecret' . $i] = $rows ['clientsecret'];
		$accounts ['token' . $i] = $rows ['token'];
		$accounts ['refresh_token' . $i] = $rows ['refresh_token'];
		$accounts ['accountid' . $i] = $rows ['accountid'];
		$accounts ['accountname' . $i] = $rows ['accountname'];
	
		$client = new WishClient ( $rows ['token'], 'prod' );
		try {
			$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
			$wishHelper->saveOrders ( $unfulfilled_orders, $rows ['accountid'] );
		} catch (ServiceResponseException $e) {
			echo "<br/>get orders faild of ".$accounts['accountname'.$i].", the error info:".$e->getStatusCode().$e->getMessage().$e->getErrorMessage();
			if ($e->getStatusCode () == 1015 || $e->getStatusCode() == 1016) {
				$response = $client->refreshToken ( $accounts ['clientid' . $i], $accounts ['clientsecret' . $i], $accounts ['refresh_token' . $i] );
				echo "<br/>Message:" . $response->getMessage ();
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
				$dbhelper->updateUserToken ( $accounts ['accountid' . $i], $newToken, $newRefresh_token );
				
				$client = new WishClient ( $newToken, 'prod' );
				$unfulfilled_orders = $client->getAllUnfulfilledOrdersSince ( '2010-01-20' );
				$wishHelper->saveOrders ( $unfulfilled_orders, $accounts ['accountid' . $i] );
			}
		}		
		$i ++;
	}
}

$add = $_GET ['add'];

// process orders;
if (strcmp ( $add, "1" ) == 0) {
	foreach ( $_POST as $key => $value ) {
		if (preg_match ( "/^label/", $key )) {
			$sku = explode ( "|", $key )[1];
			$names = explode ( "|", $value );
			$dbhelper->insertproductLabel ( $currentUserid, $sku, $dbhelper->insertLabel ( $names [0], $names [1] ) );
		}
	}
	
	// get info of current user id:
	$labels = $wishHelper->getUserLabelsArray ( $currentUserid );
	$expressinfo = $wishHelper->getExpressInfo ( $currentUserid );
	
	for($ct = 0; $ct < $i; $ct ++) {
		$wishHelper->applyTrackingsForOrders ( $accounts ['accountid' . $ct], $labels, $expressinfo );
	}
} else if (strcmp ( $add, "2" ) == 0) {
	for($ut = 0; $ut < $i; $ut ++) {
		$ordersNotUpload = $dbhelper->getOrdersForUploadTracking ( $accounts ['accountid' . $ut] );
		while ( $orderUpload = mysql_fetch_array ( $ordersNotUpload ) ) {
			if ($orderUpload ['provider'] != null && $orderUpload ['tracking'] != null) {
				$tracker = new WishTracker ( $orderUpload ['provider'], $orderUpload ['tracking'], NOTETOCUSTOMERS );
				if ($client == null || $accounts ['accountid' . $ut] != $client->getAccountid ()) {
					$client = new WishClient ( $accounts ['token' . $ut], 'prod' );
					$client->setAccountid ( $accounts ['accountid' . $ut] );
				}
				try {
					$fulResult = $client->fulfillOrderById ( $orderUpload ['orderid'], $tracker );
				} catch (ServiceResponseException $e ) {
					echo "<br/>failed to fulfillOrder" . $orderUpload ['orderid'] . $orderUpload ['tracking'] . $e->getStatusCode () . $e->getMessage ();
				}
				
				if ($fulResult) {
					$orderUpload ['orderstatus'] = ORDERSTATUS_UPLOADEDTRACKING;
					$orderUpload ['accountid'] = $accounts ['accountid' . $ut];
					$updateResult = $dbhelper->updateOrder ( $orderUpload );
				}
			}
		}
	}
}
$labels = $wishHelper->getUserLabelsArray ( $currentUserid );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0031)http://china-merchant.wish.com/ -->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Wish管理助手-更有效率的Wish商户实用工具</title>
			<meta name="keywords" content="">
				<link rel="stylesheet" type="text/css" href="../css/home_page.css">
					<link href="../css/bootstrap.min.css" rel="stylesheet">
						<script src="../js/jquery-2.2.0.min.js"></script>
						<script src="../js/bootstrap.min.js"></script>

</head>
<script type="text/javascript">
	function processorders(){
		var a=$('input[name^="label"]').map(function(){return {value:this.value,name:this.name}}).get();
		for(var i=0;i<a.length;i++){
			if(a[i].value == null || a[i].value == ""){
				alert("请填写好每个订单的中英文品名");
				return;
			}
		}
		var form = document.getElementById("processorders");
		form.submit();
	}

	function setValue(value,test){
		document.getElementById(test).value=value;
	}

	function downloadlabels(){
		window.location.href="./wdownload.php";
	}

	function uploadtrackings(){
		var form = document.getElementById("processorders");
		form.action = "./wusercenter.php?add=2";
		form.submit();
	}

	function downloadEUB(){
		window.location.href="./weubdownload.php";
	}
	function uploadEUB(){
		window.open("./wuploadEUBTrackings.php");
	}
</script>
<body>
	<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top 
" style="left: 0px;">
		<div class="container-fluid ">
			<a class="brand" href="https://wishconsole.com/"> <span
				class="merchant-header-text">Wish管理助手-更有效率的Wish商户实用工具</span>
			</a>

			<div class="pull-right">
				<ul class="nav">
					<li data-mid="5416857ef8abc87989774c1b"
						data-uid="5413fe984ad3ab745fee8b48">
<?php echo $username?>
</li>
					<li><button>
							<a href="./wlogin.php?type=exit">注销</a>
						</button></li>

				</ul>

			</div>

		</div>
	</div>
	<!-- END HEADER -->
	<!-- SUB HEADER NAV-->
	<!-- splash page subheader-->



	<div id="sub-header-nav" class="navbar navbar-fixed-top sub-header"
		style="left: 0px;">
		<div class="navbar-inner">
			<div class="container-fluid">
				<div class="pull-left">
					<div class="navbar-inner">
						<div class="container">
						
						<ul class="nav">
							<!-- <li><a href="./wusercenter.php"> 订单处理 </a></li> -->
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">产品<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./wuploadproduct.php">产品上传</a></li>
								<li><a href="./wproductstatus.php">定时产品状态</a></li>
								<li><a href="./wproductsource.php">产品源查询</a></li>
								</ul>
								</li>  
							<!-- <li><a href="./wuserinfo.php"> 个人信息 </a></li> -->
							<li> <a href="./whelper.php">帮助文档</a></li>
						</ul>
						</div>
					</div>
					<!-- /navbar-inner -->
				</div>

				<div class="pull-right">
					<ul class="nav">
					</ul>
				</div>

			</div>
		</div>
	</div>
	<!-- END SUB HEADER NAV -->
	<div class="banner-container"></div>
	<div id="page-content" class="dashboard-wrapper">
		<form class="form-horizontal" id="processorders"
			action="./wusercenter.php?add=1" method="post">
			<li>已绑定的wish账号:
<?php
for($count = 0; $count < $i; $count ++) {
	if($accounts ['token' . $count] != null)
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $accounts ['accountname' . $count];
}
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a
				href="./wbindwish.php">绑定wish账号</a>
			</li>
			<ul align="center">
				<button class="btn btn-info" type="button" onclick="processorders()">处理订单</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button"
					onclick="downloadlabels()">下载标签</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button"
					onclick="uploadtrackings()">上传单号</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button" onclick="downloadEUB()">下载E邮宝订单</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button" onclick="uploadEUB()">上传E邮宝单号</button>
			</ul>

<?php
$orderCount = 0;
for($count1 = 0; $count1 < $i; $count1 ++) {
	if($accounts ['token' . $count1] != null){
		$orders = $dbhelper->getOrdersNoTracking ( $accounts ['accountid' . $count1] );
		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">账号" . $accounts ['accountid' . $count1] . ":&nbsp;&nbsp;" . mysql_num_rows ( $orders ) . "个未处理订单";
		echo "</div><span class=\"tools\"><a class=\"fs1\" aria-hidden=\"true\" data-icon=\"&#xe090;\"></a></span></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr><th style=\"width:5%\"><input type=\"checkbox\" class=\"no-margin\" /></th>";
		echo "<th style=\"width:10%\">日期</th><th style=\"width:35%\" class=\"hidden-phone\">产品 (SKU)参数|数量</th>";
		echo "<th style=\"width:20%\" class=\"hidden-phone\">总价(价格+运费)($)</th><th style=\"width:20%\" class=\"hidden-phone\">客户名称|国家</th><th style=\"width:10%\" class=\"hidden-phone\">中英文品名</th></tr></thead>";
		echo "<tbody>";
		while ( $cur_order = mysql_fetch_array ( $orders ) ) {
			$tempsku = str_replace(' ','_',$cur_order ['sku']);
			$tempsku = str_replace('&amp;','AND',$tempsku);//WishHelper.applyTrackingsForOrders时需要替换回来；
			if ($orderCount % 2 == 0) {
				echo "<tr>";
			} else {
				echo "<tr class=\"gradeA success\">";
			}
			echo "<td style=\"width:5%;vertical-align:middle;\"><input type=\"checkbox\" class=\"no-margin\" /></td><td style=\"width:10%;vertical-align:middle;\">" . substr ( $cur_order ['ordertime'], 0, 10 ) . "</td>";
			echo "<td style=\"width:25%;vertical-align:middle;\" class=\"hidden-phone\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $cur_order ['productimage'] . "\">" . $cur_order ['sku'] . ":(" . $cur_order ['color'] . " - " . $cur_order ['size'] . " * " . $cur_order ['quantity'] . ")</li><ul></td>";
			echo "<td style=\"width:20%;vertical-align:middle;\" class=\"hidden-phone\">" . $cur_order ['quantity'] . " * (" . $cur_order ['cost'] . " + " . $cur_order ['shippingcost'] . ")=" . $cur_order ['totalcost'] . "</td>";
			echo "<td style=\"width:20%;vertical-align:middle;\" class=\"hidden-phone\">" . $cur_order ['name'] . "&nbsp;|&nbsp;" . $cur_order ['countrycode'] . "</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\" class=\"hidden-phone\"><div class=\"input-group\"><input type=\"text\" id=\"label|" . $tempsku . "|" . $orderCount . "\" name=\"label|" . $tempsku . "|" . $orderCount . "\" value=\"" . $labels [$tempsku] . "\" placeholder=\"中文|英文\">";
			echo "<div class=\"input-group-btn\"><button type=\"button\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">选择 <span class=\"caret\"></span></button>";
			echo "<ul class=\"dropdown-menu dropdown-menu-right\" role=\"menu\">";
			foreach ( array_unique ( $labels ) as $labelkey => $labelvalue ) {
				echo "<li><a onclick=setValue(\"" . $labelvalue . "\",\"label|" . $tempsku . "|" . $orderCount . "\")>" . $labelvalue . "</a></li>";
			}
			$orderCount ++;
		}
		echo "</tbody></table></div></div></div></div>";
	}
}
?>
</form>
	</div>
	<!-- FOOTER -->
	<div id="footer" class="navbar navbar-fixed-bottom" style="left: 0px;">
		<div class="navbar-inner">
			<div class="footer-container">
				<span><a href="https://wishconsole.com/">关于我们</a></span> <span><a>2016
						wishconsole版权所有 京ICP备16000367号</a>
						<!-- 51.la 网站统计 -->
						<script language="javascript" type="text/javascript" src="http://js.users.51.la/18799105.js"></script>
						<noscript><a href="http://www.51.la/?18799105" target="_blank"><img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="http://img.users.51.la/18799105.asp" style="border:none" /></a></noscript>
				</span>
			</div>
		</div>
	</div>
	<!-- END FOOTER -->
	<!-- GoStats JavaScript Based Code -->
<script type="text/javascript" src="https://ssl.gostats.com/js/counter.js"></script>
<script type="text/javascript">_gos='c5.gostats.cn';_goa=1068962;
_got=5;_goi=1;_gol='淘宝店铺计数器';_GoStatsRun();</script>
<noscript><a target="_blank" title="淘宝店铺计数器" 
href="http://gostats.cn"><img alt="淘宝店铺计数器" 
src="https://ssl.gostats.com/bin/count/a_1068962/t_5/i_1/ssl_c5.gostats.cn/counter.png" 
style="border-width:0" /></a></noscript>
<!-- End GoStats JavaScript Based Code -->
</body>
</html>