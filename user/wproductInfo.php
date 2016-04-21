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
set_time_limit ( 0 );
$dbhelper = new dbhelper ();
$wishHelper = new WishHelper ();

$username = $_SESSION ['username'];
session_commit();
if ($username == null) { // 未登录
	header ( "Location:./wlogin.php?errorMsg=登录失败" );
	exit ();
}

// 已登录
$result = $dbhelper->getUserToken ( $username );
$accounts = array ();
$i = 0;
while ( $rows = mysql_fetch_array ( $result ) ) {
	if($rows ['token'] != null){
		$accounts ['clientid' . $i] = $rows ['clientid'];
		$accounts ['clientsecret' . $i] = $rows ['clientsecret'];
		$accounts ['token' . $i] = $rows ['token'];
		$accounts ['refresh_token' . $i] = $rows ['refresh_token'];
		$accounts ['accountid' . $i] = $rows ['accountid'];
		$accounts ['accountname' . $i] = $rows ['accountname'];
		
		$accounts[$rows ['accountid']] = $rows ['token']; 
		$i ++;
	}
}

$queryParentSKU = $_POST['query_parent_sku'];

$optimizeparams = $dbhelper->getOptimizeParams();
if($oparams = mysql_fetch_array($optimizeparams)){
	$regularInventory = $oparams['inventory'];
	$daysUploaded = $oparams['daysuploaded'];
	$regularInventoryExtra = $oparams['inventoryextra'];
	$regularImpressions = $oparams['impression'];
	$regularBuyctr = $oparams['buyctr'];
	$regularConversion = $oparams['checkoutconversion'];
}

$command = $_POST['command'];
$accountid = $_POST['currentAccountid'];
$client = new WishClient ($accounts[$accountid], 'prod' );

if($command != null && strcmp($command,'updateInventory') == 0){
	$SKUS = $dbhelper->getSKUSforInventory($accountid);
	
	$resultSKU = array();
	
	while($skuarray = mysql_fetch_array($SKUS)){
		$sku = $skuarray['sku'];
		$sku = str_replace("&amp;","&",$sku);
		$onlineProductVar = $client->getProductVariationBySKU($sku);
		if($onlineProductVar->inventory< $regularInventory){
			$params = array();
			$params['sku'] = $sku;
			$params['inventory'] = $regularInventory;
			$client->updateProductVarByParams($params);
			$resultSKU[] = $sku;
		}
	}
}else if($command != null && strcmp($command,'salesOptimize') == 0){
	$weekdate = $_POST['weekdate'];
	$dates = explode(" | ",$weekdate);
	$endDate = $dates[1];
	$startDate = $dates[0];
	
	$productsResults = $dbhelper->getWeekImpressions($accountid, $startDate, $endDate, $daysUploaded);
}else if($command != null && strcmp($command,'hotSalesOptimize') == 0){
	$weekdate = $_POST['weekdate'];
	$dates = explode(" | ",$weekdate);
	$endDate = $dates[1];
	$startDate = $dates[0];
	
	$hotproducts = $dbhelper->getHotProducts($accountid, $startDate, $endDate);
	$threeweeksdateEnd = $startDate;
	$tempmonday = date('Y-m-d',strtotime('last monday',strtotime($threeweeksdateEnd)));
	$threeweeksdateStart = date('Y-m-d',strtotime('last monday',strtotime($tempmonday)));

	$updatecontent;
	while ($hotproduct = mysql_fetch_array($hotproducts)){
		$hotproductid = $hotproduct['productid'];
		
		$productOrders = $wishHelper->getProductOrders($accountid, $hotproductid, $threeweeksdateStart, $threeweeksdateEnd);
		$initOrder = 0;
		$isIncreased = 1;
		foreach ($productOrders as $productOrder){
			if($productOrder < $initOrder){//订单减少
				$isIncreased = 0;
				continue;
			}
			$initOrder = $productOrder;
		}
		
		$hotskus = $wishHelper->getProductVars($hotproductid);
		foreach ($hotskus as $hotsku){
			$hotProductVar = $client->getProductVariationBySKU($hotsku);
			$params = array();
			$params['sku'] = $hotsku;
			
			if($isIncreased == 0){//订单减少的处理：  降价$0.01
				$price = $hotProductVar->price;
				$params['price'] = $price - 0.01;
				$updatecontent .= $params['sku']." lower price to ".$params['price']."\n";
			}else{//订单递增的处理： 添加库存
				$curInventory = $hotProductVar->inventory;
				if($curInventory<$regularInventory){
					$hotProductVar->inventory = $regularInventory;
				}else{
					$hotProductVar->inventory = $hotProductVar->inventory + $regularInventoryExtra;
				}
				$params['inventory'] = $hotProductVar->inventory;
				$updatecontent .= $params['sku']." updateinventory to ".$params['inventory']."\n";
			}
			$client->updateProductVarByParams($params);
		}
	}
}else if($command != null && strcmp($command,'productsOptimize') == 0){
	$weekdate = $_POST['weekdate'];
	$dates = explode(" | ",$weekdate);
	$endDate = $dates[1];
	$startDate = $dates[0];
	
	$ImpressionsProducts = $dbhelper->getProductsMoreImpressions($accountid, $startDate, $endDate, $regularImpressions);
	
	$LessImpressionsProducts = $dbhelper->getProductsLessImpressions($accountid, $startDate, $endDate, $regularImpressions, $regularBuyctr);
	
	
	$threeweeksdateEnd = $startDate;
	$tempmonday = date('Y-m-d',strtotime('last monday',strtotime($threeweeksdateEnd)));
	$threeweeksdateStart = date('Y-m-d',strtotime('last monday',strtotime($tempmonday)));
	
	$littleProductsArray = $wishHelper->processLittleImpressionsProducts($accountid, $threeweeksdateStart, $threeweeksdateEnd, $regularImpressions);
}


function getPreWeek($curtime){
	$preendDate = date('Y-m-d',strtotime('last monday',strtotime($curtime)));
	$prestartDate = date('Y-m-d',strtotime('last monday',strtotime($preendDate)));
	$week = array($prestartDate,$preendDate);
	return $week;
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
					<link href="../css/bootstrap.min.css" rel="stylesheet">
						<script src="../js/jquery-2.2.0.min.js"></script>
						<script src="../js/bootstrap.min.js"></script>

</head>
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
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">店铺优化<b class="caret"></b> </a>
								<ul class="dropdown-menu">
								<li><a href="./csvupload.php">CSV文档上传</a></li>
								<li><a href="./wproductlist.php">店铺产品同步</a></li>
								<li><a href="./wproductInfo.php">产品优化</a></li>
								</ul>
							</li> 
							<!-- <li><a href="./wuserinfo.php"> 个人信息 </a></li> -->
							<li> <a href="./whelper.php">帮助文档</a></li>
						</ul>
						</div>
					</div>
					<!-- /navbar-inner -->
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
	<form class="form-horizontal" id="optimizeproduct"
			action="./wproductInfo.php" method="post">
			<input type="hidden" id="command" name="command" value=""/>
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
			<br/>
			
			<?php 
	                      		if(isset($resultSKU)){
	                      			echo "<div class=\"alert alert-block alert-success fade in\">";
	                      			echo "<h4 class=\"alert-heading\">对以下的产品库存进行了更新:";
	                      			$count = 0;
	                      			foreach ($resultSKU as $currsku){
	                      				echo "&nbsp;&nbsp;&nbsp;&nbsp;".$currsku.",";
	                      				$count++;
	                      				if($count%10 == 0)
	                      					echo "<br/>";
	                      			}
	                      			echo "</h4>";
	                      			echo "</div>";
	                      			$resultSKU = null;
	                      		}
	                    ?>
	                    
			<div id="add-product-form">
						<div id="basic-info" class="form-horizontal">
							<div class="control-group">
								<label class="control-label" data-col-index="3"><span
									class="col-name">请选择wish账号</span></label>

								<div class="controls input-append">
									<label>
							<?php
							if ($i>0){
								for($count = 0; $count < $i; $count ++) {
									if($count != 0 && $count%3 == 0)
										echo "<br/>";
									echo "<input type=\"radio\" id=\"currentAccountid\" name=\"currentAccountid\" value=\"" . $accounts ['accountid' . $count] . "\"" . ($accountid == null ? ($count == 0 ? "checked" : "") : ((strcmp ( $accounts ['accountid' . $count], $accountid ) == 0) ? "checked" : "")) . ">";
									echo "&nbsp;&nbsp;" . $accounts ['accountname'.$count];
									echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
								}	
							}else{
								echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您暂时没有绑定任何wish账号，请先&nbsp;&nbsp;&nbsp;&nbsp;";
							}
							
							?></label>
								</div>
							</div>
							
							<div class="control-group">
								<label class="control-label" data-col-index="3"><span
									class="col-name">时间段选择</span></label>

								<div class="controls input-append">
								<label>
									<select id="weekdate" name="weekdate">
										<?php 
										$initTime =  date ( 'Y-m-d  H:i:s',time());
										for ($l=0;$l<5;$l++){
											$curWeek = getPreWeek($initTime);
											if($weekdate != null){
												if(strcmp($weekdate,$curWeek[0]." | ".$curWeek[1]) == 0){
													echo "<option selected=\"selected\" value=\"".$curWeek[0]." | ".$curWeek[1]."\">".$curWeek[0]." | ".$curWeek[1]."</option>";
												}else{
													echo "<option value=\"".$curWeek[0]." | ".$curWeek[1]."\">".$curWeek[0]." | ".$curWeek[1]."</option>";
												}												
											}else{
												if($l == 0){
													echo "<option selected=\"selected\" value=\"".$curWeek[0]." | ".$curWeek[1]."\">".$curWeek[0]." | ".$curWeek[1]."</option>";
												}else{
													echo "<option value=\"".$curWeek[0]." | ".$curWeek[1]."\">".$curWeek[0]." | ".$curWeek[1]."</option>";
												}	
											}
											$initTime = $curWeek[1];
										}
										?>
									  </select>
									</label>
								</div>
							</div>
							
							<div class="control-group">
								<div>
								<ul align="center">
				<button class="btn btn-info" type="button" onclick="updateInventory()">扫描库存</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<!-- <button class="btn btn-info" type="button"
					onclick="downloadlabels()">价格调整</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button"
					onclick="uploadtrackings()">运费调整</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; -->
				<button class="btn btn-info" type="button"
					onclick="salesOptimize()">无推送产品扫描</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button"
					onclick="productsOptimize()">产品优化</button>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-info" type="button"
					onclick="hotSalesOptimize()">热卖产品自动更新</button>
					</ul>
								</div>
							</div>
						</div>
				</div>
							
							
			<div class="control-group">
				<label class="control-label"><span
									class="col-name">查询parent_sku:</span></label>
						<div class="controls input-append">
							<input class="input-block-level required" id="query_parent_sku"
									name="query_parent_sku" type="text"
									value="<?php echo $parent_sku ?>"
									/>&nbsp;&nbsp;&nbsp;&nbsp;
									<button id="query_action" type="submit"
								class="btn btn-primary btn-large">提交</button>
						</div>
			</div>
			
			
<?php
if($command != null && strcmp($command,'salesOptimize') == 0){
	if(isset($productsResults)){
		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品在  ".$weekdate." 没有任何推送，并且已经上传产品超过".$daysUploaded."天,建议下架,可优化后重新上架:";
		echo "</div><span class=\"tools\"></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
		echo "<th style=\"width:25%\">产品名称</th><th style=\"width:20%\">父SKU</th>";
		echo "<th style=\"width:10%\">收藏数</th><th style=\"width:10%\">已售出</th><th style=\"width:10%\">上传时间</th><th style=\"width:10%\">操作</th></tr></thead>";
		echo "<tbody>";
		$orderCount = 0;
		while($productResult = mysql_fetch_array($productsResults)){
			
				if ($orderCount % 2 == 0) {
					echo "<tr>";
				} else {
					echo "<tr class=\"gradeA success\">";
				}
			
				echo "<td style=\"width:25%;vertical-align:middle;\">" . $productResult['name']. "</td>";
				echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $productResult ['main_image'] . "\">" . $productResult ['parent_sku'] ."</li><ul></td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $productResult['number_saves']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $productResult ['number_sold']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $productResult ['date_uploaded']."</td>";
				
				if($productResult['number_saves'] == 0 &&  $productResult ['number_sold'] == 0){
					$skus = $wishHelper->getProductVars($productResult['id']);
					foreach ($skus as $sku){
						$params = array();
						$params['sku'] = $sku;
						$params['enabled'] = "false";
						$client->updateProductVarByParams($params);
					}
					echo "<td style=\"width:10%;vertical-align:middle;\"><span class=\"label label-info\">该产品已经自动下架</span></td>";
				}else{
					echo "<td style=\"width:10%;vertical-align:middle;\"><button type=\"button\" onclick=\"productDetails('".$accountid."','".$productResult['id']."')\" class=\"btn btn-mini\"><span class=\"label label-info\">查看</span></button></td>";
				}
				echo "</tr>";
				$orderCount ++;
			
		}
		echo "</tbody></table></div></div></div></div>";
	}
} else if($command != null && strcmp($command,'productsOptimize') == 0){
	if(isset($ImpressionsProducts)){
		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品在  ".$weekdate." 展示超过". $regularImpressions ."次，可根据上周数据继续优化:";
		echo "</div><span class=\"tools\"></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
		echo "<th style=\"width:20%\">产品名称</th><th style=\"width:20%\">父SKU</th>";
		echo "<th style=\"width:5%\">促销</th><th style=\"width:5%\">审核</th><th style=\"width:5%\">收藏数</th><th style=\"width:5%\">已售出</th><th style=\"width:10%\">浏览数</th><th style=\"width:5%\">购物车浏览数</th>";
		echo "<th style=\"width:5%\">购买率</th><th style=\"width:5%\">订单数</th><th style=\"width:5%\">付款率</th><th style=\"width:10%\">操作</th></tr></thead>";
		echo "<tbody>";
		$orderCount = 0;
		while($impressionProduct = mysql_fetch_array($ImpressionsProducts)){
			
				if ($orderCount % 2 == 0) {
					echo "<tr>";
				} else {
					echo "<tr class=\"gradeA success\">";
				}
			
				echo "<td style=\"width:25%;vertical-align:middle;\">" . $impressionProduct['name']. "</td>";
				echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $impressionProduct ['main_image'] . "\">" . $impressionProduct ['parent_sku'] ."</li><ul></td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct['is_promoted']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct ['review_status']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct ['number_saves']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct ['number_sold']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct ['productimpressions']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct ['buycart']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct ['buyctr']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct ['orders']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $impressionProduct ['checkoutconversion']."</td>";
				
				echo "<td style=\"width:10%;vertical-align:middle;\"><button type=\"button\" onclick=\"productDetails('".$accountid."','".$impressionProduct['id']."')\" class=\"btn btn-mini\"><span class=\"label label-info\">查看</span></button></td>";
				
				echo "</tr>";
				$orderCount ++;
			
		}
		echo "</tbody></table></div></div></div></div>";
	}
	
	
	if(isset($LessImpressionsProducts)){
		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品  ".$weekdate." 展示不到". $regularImpressions ."次，但购买率达标(或已有订单),可继续优化:";
		echo "</div><span class=\"tools\"></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
		echo "<th style=\"width:20%\">产品名称</th><th style=\"width:20%\">父SKU</th>";
		echo "<th style=\"width:5%\">促销</th><th style=\"width:5%\">审核</th><th style=\"width:5%\">收藏数</th><th style=\"width:5%\">已售出</th><th style=\"width:10%\">浏览数</th><th style=\"width:5%\">购物车浏览数</th>";
		echo "<th style=\"width:5%\">购买率</th><th style=\"width:5%\">订单数</th><th style=\"width:5%\">付款率</th><th style=\"width:10%\">操作</th></tr></thead>";
		echo "<tbody>";
		$orderCount = 0;
		while($lessImpressionProduct = mysql_fetch_array($LessImpressionsProducts)){
				
			if ($orderCount % 2 == 0) {
				echo "<tr>";
			} else {
				echo "<tr class=\"gradeA success\">";
			}
				
			echo "<td style=\"width:25%;vertical-align:middle;\">" . $lessImpressionProduct['name']. "</td>";
			echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $lessImpressionProduct ['main_image'] . "\">" . $lessImpressionProduct ['parent_sku'] ."</li><ul></td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct['is_promoted']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct ['review_status']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct ['number_saves']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct ['number_sold']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct ['productimpressions']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct ['buycart']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct ['buyctr']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct ['orders']."</td>";
			echo "<td style=\"width:10%;vertical-align:middle;\">" . $lessImpressionProduct ['checkoutconversion']."</td>";
	
			echo "<td style=\"width:10%;vertical-align:middle;\"><button type=\"button\" onclick=\"productDetails('".$accountid."','".$lessImpressionProduct['id']."')\" class=\"btn btn-mini\"><span class=\"label label-info\">查看</span></button></td>";
	
			echo "</tr>";
			$orderCount ++;
				
		}
		echo "</tbody></table></div></div></div></div>";
	}
	
	if(isset($littleProductsArray)){
		$productids = $littleProductsArray['productids'];
		$littleDisabledSKU = $littleProductsArray['disable'];
		$littleLowerPrice = $littleProductsArray['lower'];

		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品或者是黄钻产品，或者连续三周展示有上升，可继续优化:";
		echo "</div><span class=\"tools\"></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
		echo "<th style=\"width:20%\">产品名称</th><th style=\"width:20%\">父SKU</th>";
		echo "<th style=\"width:5%\">促销</th><th style=\"width:5%\">审核</th><th style=\"width:5%\">收藏数</th><th style=\"width:5%\">已售出</th><th style=\"width:10%\">浏览数</th><th style=\"width:5%\">购物车浏览数</th>";
		echo "<th style=\"width:5%\">购买率</th><th style=\"width:5%\">订单数</th><th style=\"width:5%\">付款率</th><th style=\"width:10%\">操作</th></tr></thead>";
		echo "<tbody>";
		$orderCount = 0;
		foreach ($productids as $productid){
			$currentProductResult = $dbhelper->getProductSummary($productid,$startDate,$endDate);
			if($currentProduct = mysql_fetch_array($currentProductResult)){
				if ($orderCount % 2 == 0) {
					echo "<tr>";
				} else {
					echo "<tr class=\"gradeA success\">";
				}
				
				echo "<td style=\"width:25%;vertical-align:middle;\">" . $currentProduct['name']. "</td>";
				echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $currentProduct ['main_image'] . "\">" . $currentProduct ['parent_sku'] ."</li><ul></td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct['is_promoted']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct ['review_status']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct ['number_saves']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct ['number_sold']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct ['productimpressions']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct ['buycart']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct ['buyctr']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct ['orders']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $currentProduct ['checkoutconversion']."</td>";
				
				echo "<td style=\"width:10%;vertical-align:middle;\"><button type=\"button\" onclick=\"productDetails('".$accountid."','".$currentProduct['id']."')\" class=\"btn btn-mini\"><span class=\"label label-info\">查看</span></button></td>";
				
				echo "</tr>";
				$orderCount ++;
			}
		}
		echo "</tbody></table></div></div></div></div>";
		
		
		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accountid."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;下列产品已自动处理完成:";
		echo "</div><span class=\"tools\"></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
		echo "<th style=\"width:10%\">说明</th><th style=\"width:80%\">处理内容</th>";
		echo "<tbody>";
		
		echo "<tr>";
		echo "<td style=\"width:10%;vertical-align:middle;\">已禁用的产品SKU列表</td>";
		echo "<td style=\"width:80%;vertical-align:middle;\">" . $littleDisabledSKU."</td>";
		echo "</tr>";
		
		echo "<tr class=\"gradeA success\">";
		echo "<td style=\"width:10%;vertical-align:middle;\">已调低运费0.01的产品SKU列表</td>";
		echo "<td style=\"width:80%;vertical-align:middle;\">" . $littleLowerPrice."</td>";
		echo "</tr>";
		
		echo "</tbody></table></div></div></div></div>";
		
	}
} else if($command != null && strcmp($command,'hotSalesOptimize') == 0){
	if(isset($updatecontent)){
		echo "<div class=\"control-group\">";
		echo "<label class=\"control-label\"><span class=\"col-name\">更新的内容:</span></label>";
		echo "<textarea rows=\"5\" class = \"form-control\" name=\"updateContent\" id=\"updateContent\" type=\"text\">".$updatecontent;
		echo "</textarea></div></div>";
	}
} else{

	if($queryParentSKU != null){
		$orderCount = 0;
		for($count1 = 0; $count1 < $i; $count1 ++) {
			if($accounts ['token' . $count1] != null){
				$onlineProducts = $dbhelper->getOnlineProducts($accounts ['accountid' . $count1],$queryParentSKU );
				echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号:&nbsp;&nbsp;" . $accounts ['accountname' . $count1];
				echo "</div><span class=\"tools\"></div>";
				echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
				echo "<th style=\"width:25%\">产品名称</th><th style=\"width:20%\">父SKU</th>";
				echo "<th style=\"width:10%\">收藏数</th><th style=\"width:10%\">已售出</th><th style=\"width:10%\">上传时间</th><th style=\"width:10%\">操作</th></tr></thead>";
				echo "<tbody>";
				while ( $cur_product = mysql_fetch_array ( $onlineProducts) ) {
					if ($orderCount % 2 == 0) {
						echo "<tr>";
					} else {
						echo "<tr class=\"gradeA success\">";
					}
					echo "<td style=\"width:25%;vertical-align:middle;\">" . $cur_product['name']. "</td>";
					echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $cur_product ['main_image'] . "\">" . $cur_product ['parent_sku'] ."</li><ul></td>";
					echo "<td style=\"width:10%;vertical-align:middle;\">" . $cur_product['number_saves']."</td>";
					echo "<td style=\"width:10%;vertical-align:middle;\">" . $cur_product ['number_sold']."</td>";
					echo "<td style=\"width:10%;vertical-align:middle;\">" . $cur_product ['date_uploaded']."</td>";
					echo "<td style=\"width:10%;vertical-align:middle;\"><button type=\"button\" onclick=\"productDetails('".$accounts ['accountid' . $count1]."','".$cur_product['id']."')\" class=\"btn btn-mini\"><span class=\"label label-info\">查看</span></button></td>";
					echo "</tr>";
					$orderCount ++;
				}
				echo "</tbody></table></div></div></div></div>";
			}
		}
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
				</span>
			</div>
		</div>
	</div>
	<!-- END FOOTER -->
	<script type="text/javascript">
		function productDetails(uid,pid){
			window.open("./wproductDetails.php?uid=" + uid + "&pid=" + pid);
		}

		function updateInventory(){
			var form = document.getElementById("optimizeproduct");
			$('#command').val("updateInventory");
			form.submit();
		}

		function salesOptimize(){
			var form = document.getElementById("optimizeproduct");
			$('#command').val("salesOptimize");
			form.submit();
		}

		function hotSalesOptimize(){
			var form = document.getElementById("optimizeproduct");
			$('#command').val("hotSalesOptimize");
			form.submit();
		}

		function productsOptimize(){
			var form = document.getElementById("optimizeproduct");
			$('#command').val("productsOptimize");
			form.submit();
		}
		
	</script>
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