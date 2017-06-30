<?php
session_start ();
include dirname ( '__FILE__' ) . './Wish/WishClient.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
include_once dirname ( '__FILE__' ) . './user/wconfig.php';
use Wish\WishHelper;
header ( "Content-Type: text/html;charset=utf-8" );
$wishHelper = new WishHelper ();

$userid = $_SESSION ['userid'];
$username = $_SESSION ['username'];
session_commit();
if ($userid == null) { // 未登录
	header ( "Location:./wlogin.php?errorMsg=登录失败" );
	exit ();
}


$parent_sku = $_GET['query_parent_sku'];

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
		<form class="form-horizontal" id="processsource"
			action="./wproductsource.php" method="get">
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
			<div class="control-group">
				<label class="control-label"><span
									class="col-name">查询parent_sku当前库存:</span></label>
						<div class="controls input-append">
							<input class="input-block-level required" id="query_parent_sku"
									name="query_parent_sku" type="text"
									value="<?php echo $parent_sku ?>"
									/>
									<button id="query_action" type="button"
								class="btn btn-primary btn-large">提交</button>
						</div>
			</div>
<?php
	echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\">&nbsp;&nbsp;&nbsp;&nbsp;账号&nbsp;&nbsp;产品实时库存信息：";
	echo "</div><span class=\"tools\"><a class=\"fs1\" aria-hidden=\"true\" data-icon=\"&#xe090;\"></a></span></div>";
	echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
	echo "<th style=\"width:20%\">父SKU</th><th style=\"width:20%\">SKU</th><th style=\"width:20%\">备注</th>";
	echo "<th style=\"width:20%\">当前库存</th><th style=\"width:20%\">操作</th></tr></thead>";
	echo "<tbody>";

	$inventoryarray = $wishHelper->getInventories($userid);
	$count = 0;
	foreach ($inventoryarray as $key=>$inventoryvalue){
		$parent_sku = $inventoryvalue['PSKU'];
		$skuvalues = $inventoryvalue['SKUInventory'];
		$skunote = $inventoryvalue['NOTE'];
		$skucount = count($skuvalues);
		
		foreach ($skuvalues as $currsku=>$currinventory){
			if ($count % 2 == 0) {
				echo "<tr>";
			} else {
				echo "<tr class=\"gradeA success\">";
			}
			if($count == 0)
				echo "<td rowspan=".$skucount." style=\"width:20%;vertical-align:middle;\">" . $parent_sku."</td>";
			echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li>".$currsku ."</li></ul></td>";
			echo "<td style=\"width:20%;vertical-align:middle;\"><ul><li>".$skunote ."</li></ul></td>";
			echo "<td style=\"width:20%;vertical-align:middle;\">" . $currinventory ."</td>";
			echo "<td style=\"width:20%;vertical-align:middle;\">出库  入库</td>";
			echo "</tr>";
			$count ++;
		}
		
	}
	echo "</tbody></table></div></div></div></div>";
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

	<script type="text/javascript" src="../js/jquery-2.2.0.min.js" charset="UTF-8"></script>
	<script type="text/javascript">
		$(document).ready(function(){

			$('button#query_action').click(function(){
				$('#processsource').submit();	
			});

		}); 
	</script>
</body>
</html>