<?php 
use mysql\dbhelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
header ( "Content-Type: text/html;charset=utf-8" );

$accountid = $_GET['uid'];
$orderid = $_GET['orderid'];

$dbhelper = new dbhelper();

if($orderid != null){
	$orderdetailsresult = $dbhelper->getorderdetails($accountid, $orderid);
	if($orderdetailsresult != null){
		$orderdetails = mysql_fetch_array($orderdetailsresult);
		
		$tempstate = $orderdetails['state'];
		if($tempstate == null){
			$stateresult = $dbhelper->getstatesByCityCode($orderdetails['city'], $orderdetails['countrycode']);
			if($stateresult != null){
				while($statedetails = mysql_fetch_array($stateresult)){
					$currentstate = $statedetails['state'];
					$currentzipcode = $statedetails['zipcode'];
					if($currentstate != null && trim($currentstate != '')){
						$tempstate = $currentstate;
					}
					
					if(strcmp($currentzipcode,$orderdetails['zipcode']) == 0){
						break;
					}
				}
			}
		}
	}
}else{
	$accountid = $_POST['accountid'];
	$orderid = $_POST['orderid'];
	$name = $_POST['name'];
	//name,streetaddress1,streetaddress2,
		//city,state,zipcode,phonenumber,countrycode
	$streetaddress1 = $_POST['streetaddress1'];
	$streetaddress2 = $_POST['streetaddress2'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zipcode = $_POST['zipcode'];
	$phonenumber = $_POST['phonenumber'];
	$countrycode = $_POST['countrycode'];
	
	if($orderid != null){
		$result = $dbhelper->updateorderaddress($accountid, $orderid, $name, $streetaddress1, $streetaddress2, $city, $state, $zipcode, $phonenumber, $countrycode);
		if(!$result){
			$resultmsg = "更新地址信息失败。";
		}else{
			$resultmsg = "更新地址信息成功。";
			$orderdetailsresult = $dbhelper->getorderdetails($accountid, $orderid);
			if($orderdetailsresult != null){
				$orderdetails = mysql_fetch_array($orderdetailsresult);
			}
		}
	}
}
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
				<link rel="stylesheet" type="text/css" href="../css/add_products_page.css" />
				<link href="../css/bootstrap.min.css" rel="stylesheet" media="screen">
				<link href="../css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
</head>
<body>
	<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top 



"
		style="left: 0px;">
		<div class="container-fluid ">
			<a class="brand" href="https://wishconsole.com/"> <span
				class="merchant-header-text">Wish管理助手-更有效率的Wish商户实用工具 </span>
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
						<?php include("./menu.php");?>
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

	<div id="page-content" class="container-fluid  user">
		<form id="add_product" action="./weditaddress.php" method="post">
			<input type="hidden" id="accountid" name="accountid" value="<?php echo $accountid?>"/>
			<input type="hidden" id="orderid" name="orderid" value="<?php echo $orderid?>"/>
			<div id="add-products-page" class="center">
				<div>
					<!-- NOTE: if you update this, make sure the add product page in onboarding flow still works -->
						<?php 
	                      		if(isset($resultmsg)){
	                      			echo "<div class=\"alert alert-block alert-success fade in\">";
	                      			echo "<h4 class=\"alert-heading\">";
	                      			echo $resultmsg;
	                      			echo "</h4>";
	                      			echo "</div>";
	                      			$resultmsg = null;
	                      		}
	                    ?>
					<div id="add-product-form">
						<div id="basic-info" class="form-horizontal">
							<div class="section-title" align="left">订单<?php echo $orderid?>的地址信息</div>

							<div class="control-group">
								<label class="control-label" data-col-index="3"><span
									class="col-name">名称</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" id="name"
										name="name" type="text"
										value="<?php echo $orderdetails['name']?>"
										/>
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="3"><span
									class="col-name">地址1:</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" id="streetaddress1"
										name="streetaddress1" type="text"
										value="<?php echo $orderdetails['streetaddress1']?>"
										/>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" data-col-index="3"><span
									class="col-name">地址2:</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" id="streetaddress2"
										name="streetaddress2" type="text"
										value="<?php echo $orderdetails['streetaddress2']?>"
										/>
								</div>
							</div>

							<div class="control-group">
								<label class="control-label" data-col-index="7"><span
									class="col-name">城市</span></label>
								<div class="controls input-append">
									<input class="input-block-level required" name="city"
										value="<?php echo $orderdetails['city']?>" id="city" type="text"
										 />
								</div>
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">州</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="state"
										value="<?php echo $tempstate?>" id="state" type="text"
										  />
								<label><a target="_blank" href="https://en.wikipedia.org/wiki/<?php echo $orderdetails['city'];?>">提示</a></label>
								</div>
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">邮编</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="zipcode"
										id="zipcode" type="text" value="<?php echo $orderdetails['zipcode']?>"
										 />
								</div>
							</div>

							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">电话</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="phonenumber"
										value="<?php echo $orderdetails['phonenumber']?>" id="phonenumber" type="text"
										  />
								</div>
							</div>
							
							<div class="control-group" style="display: block;">
								<label class="control-label" data-col-index="1"><span
									class="col-name">国家</span></label>

								<div class="controls input-append">
									<input class="input-block-level required" name="countrycode"
										id="countrycode" type="text" value="<?php echo $orderdetails['countrycode']?>"
										  /> 
								</div>
							</div>

						</div>

						<div id="buttons-section" class="control-group text-right">
							<button id="submit-button" type="submit"
								class="btn btn-primary btn-large">提交</button>
						</div>
					</div>
				</div>
			</div>
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
	
<script type="text/javascript" src="../js/jquery-2.2.0.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/bootstrap.min.js"></script>
<script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/locales/bootstrap-datetimepicker.zh-CN.js" charset="UTF-8"></script>
<script type="text/javascript" src="../js/jquery.ajaxfileupload.js"></script>
</body>
</html>