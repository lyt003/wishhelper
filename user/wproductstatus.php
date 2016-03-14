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
		$i ++;
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
			<title>更有效率的Wish商户实用工具</title>
			<meta name="keywords" content="">
				<link rel="stylesheet" type="text/css" href="../css/home_page.css">
					<link href="../css/bootstrap.min.css" rel="stylesheet">
						<script src="../js/jquery-2.2.0.min.js"></script>
						<script src="../js/bootstrap.min.js"></script>

</head>
<body>
	<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top 
" style="left: 0px;">
		<div class="container-fluid ">
			<a class="brand" href="http://wishconsole.com/"> <span
				class="merchant-header-text"> 更有效率的Wish商户实用工具 </span>
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
<?php
$orderCount = 0;
for($count1 = 0; $count1 < $i; $count1 ++) {
	if($accounts ['token' . $count1] != null){
		$scheduleProducts = $dbhelper->getUploadProducts($accounts ['accountid' . $count1] );
		$productsInfo = $wishHelper->getProductVarsCount($scheduleProducts);
		$productvars = $productsInfo['productvars'];
		echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">账号" . $accounts ['accountid' . $count1];
		echo "</div><span class=\"tools\"><a class=\"fs1\" aria-hidden=\"true\" data-icon=\"&#xe090;\"></a></span></div>";
		echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
		echo "<th style=\"width:15%\">产品名称</th><th style=\"width:10%\">父SKU</th>";
		echo "<th style=\"width:20%\">SKU</th><th style=\"width:10%\">价格</th><th style=\"width:10%\">库存</th><th style=\"width:5%\">上传时间</th><th style=\"width:10%\">操作</th></tr></thead>";
		echo "<tbody>";
		$tempParentSKU = "";
		$isProduct = false;
		foreach ($productvars as $cur_product){
			if ($orderCount % 2 == 0) {
				echo "<tr>";
			} else {
				echo "<tr class=\"gradeA success\">";
			}
			
			$currentParentSKU =  $cur_product['parent_sku'];
			if($currentParentSKU != $tempParentSKU )
				$isProduct = true;
				
			if($isProduct){
				$varCounts = $productsInfo[$currentParentSKU];
				echo "<td rowspan=".$varCounts." style=\"width:15%;vertical-align:middle;\">" . $cur_product['name']. "</td>";
				echo "<td rowspan=".$varCounts." style=\"width:10%;vertical-align:middle;\"><ul><li><img width=50 height=50 style=\"vertical-align:middle;\" src=\"" . $cur_product ['main_image'] . "\">" . $cur_product ['parent_sku'] ."</li><ul></td>";
			}	
				echo "<td style=\"width:20%;vertical-align:middle;\">" . $cur_product['sku']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $cur_product ['price'] ." + ".$cur_product ['shipping']."</td>";
				echo "<td style=\"width:10%;vertical-align:middle;\">" . $cur_product ['quantity'] ."</td>";

			if($isProduct){
				echo "<td rowspan=".$varCounts." style=\"width:5%;vertical-align:middle;\">" . $cur_product ['scheduledate'] ."</td>";
				echo "<td rowspan=".$varCounts." style=\"width:10%;vertical-align:middle;\">";
				if($cur_product['errormessage'] != null){
					echo "<p>上传失败".$cur_product['errormessage']."</p>";
				}else{
					echo"<p>查看</p>";
				}
				echo "</td>";
				$tempParentSKU = $currentParentSKU;
				$isProduct = false;
			}
				
			echo "</tr>";
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
				<span><a href="http://wishconsole.com/">关于我们</a></span> <span><a>2016
						wishconsole版权所有 京ICP备16000367号</a></span>
			</div>
		</div>
	</div>
	<!-- END FOOTER -->
</body>
</html>