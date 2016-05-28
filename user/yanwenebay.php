<?php 
session_start ();
use mysql\dbhelper;
use Wish\WishHelper;
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
include_once dirname ( '__FILE__' ) . './Wish/WishHelper.php';
header ( "Content-Type: text/html;charset=utf-8" );
$currentUserid = $_SESSION ['userid'];
session_commit();
$userid = '1';
$accountid = '0';
$dbhelper = new dbhelper();
$wishHelper = new WishHelper();
$result = $_GET['msg'];
if($result != null){
	if(strcmp($result,'success') ==0){
		$labels = $wishHelper->getUserLabelsArray ( $currentUserid );
		$expressinfo = $wishHelper->getExpressInfo ( $currentUserid );
		$wishHelper->applyTrackingsForOrders ($userid, $accountid, $labels, $expressinfo );
	}else if(strcmp($result,'label') ==0){
		$dbhelper->insertproductLabel ( $currentUserid, "underwear", 5);
		$dbhelper->insertproductLabel ( $currentUserid, "pants",6);
		$dbhelper->insertproductLabel ( $currentUserid, "dress",1);
		$dbhelper->insertproductLabel ( $currentUserid, "camisole",7);
		$dbhelper->insertproductLabel ( $currentUserid, "earring",3);
		$dbhelper->insertproductLabel ( $currentUserid, "sealer",17);
		$dbhelper->insertproductLabel ( $currentUserid, "cup",16);
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
		<form id="addform" action="./yanwenebayprocess.php" method="post" enctype="multipart/form-data">
			<input type="hidden" id="localfile" name="localfile" value=""/>
			<div id="add-products-page" class="center">
				<div>
					<div id="add-product-form">
						<div id="basic-info" class="form-horizontal">
							<div class="control-group">
								<label class="control-label" data-col-index="5"><span
									class="col-name">要导入的csv文件</span></label>
								<div class="controls input-append">
									<input type="file" name="file" id="file">
								</div>
							</div>
						</div>

						<div id="buttons-section" class="control-group text-right">
							<br/>
							<br/>
							
							<button id="submit-button" type="button"
								class="btn btn-primary btn-large" onclick="readexcel()">读取本地csv文件</button>
									
							<button id="submit-button" type="button"
								class="btn btn-primary btn-large" onclick="importexcel()">导入csv文件</button>
						</div>
						<ul align="center">
							<button class="btn btn-info" type="button" onclick="initebayLabel()">初始化标签</button>
						</ul>
						<?php 
						if($result != null){
							if(strcmp($result,'success') ==0){
								echo "<ul align=\"center\">";
								echo "<button class=\"btn btn-info\" type=\"button\"";
								echo "		onclick=\"downloadebaylabels()\">下载物流面单</button>";
								echo "</ul>";
							}else if(strcmp($result,'label') !=0){
								echo "*****".$result;		
							}							
						}
						?>
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
<script type="text/javascript">
	function importexcel(){
		var csvfile = document.getElementById("file").value;
		if(csvfile == null || $.trim(csvfile) == ''){
			alert("请先选择excel文件");
			return;}
		var form = document.getElementById("addform");
		form.submit();
	}

	function readexcel(){
		$('#localfile').val("./SalesHistory.csv");
		
		var form = document.getElementById("addform");
		form.submit();
	}

	function initebayLabel(){
		var form = document.getElementById("addform");
		form.action = "./yanwenebay.php?msg=label";
		form.submit();
	}
	
	function downloadebaylabels(){
		window.location.href="./wdownload.php";
	}
</script>
</body>
</html>