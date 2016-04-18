<?php
session_start ();
header ( "Content-Type: text/html;charset=utf-8" );
include_once dirname ( '__FILE__' ) . './mysql/dbhelper.php';
use mysql\dbhelper;

$username = $_SESSION ['username'];
session_commit();
if ($username == null) { // 未登录
	header ( "Location:./wlogin.php" );
	exit ();
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
					<link href="../css/bootstrap.min.css" rel="stylesheet">
						<script src="../js/jquery-2.2.0.min.js"></script>
						<script src="../js/bootstrap.min.js"></script>

</head>
<body>
<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top 



"
		style="left: 0px;">
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
<?php 
echo "<div id=\"page-content\">";
echo "<div class=\"row-fluid\"><div class=\"span12\"><div class=\"widget\"><div class=\"widget-header\"><div class=\"title\"><h1>Wish颜色列表：</h1><br/>";
echo "</div><span class=\"tools\"><a class=\"fs1\" aria-hidden=\"true\" data-icon=\"&#xe090;\"></a></span></div>";
echo "<div class=\"widget-body\"><table class=\"table table-condensed table-striped table-bordered table-hover no-margin\"><thead><tr>";
echo "<th style=\"width:35%\"><h1>颜色</h1></th><th style=\"width:35%\"><h1>颜色</h1></th>";
echo "<th style=\"width:35%\"><h1>颜色</h1></th></tr></thead>";
echo "<tbody>";

$dbhelper = new dbhelper();
$colors = $dbhelper->getWishColors();
$colorcount = 0;
$currentLine = 0;
echo "<tr>";
while($color = mysql_fetch_array($colors)){
	if($currentLine % 3 == 0){
		if ($colorcount % 2 == 0) {
			echo "</tr><tr>";
		} else {
			echo "</tr><tr class=\"gradeA success\">";
		}
		echo "<td style=\"width:35%;vertical-align:middle;\"><h2>" . $color['color'] ."</h2></td>";
		$colorcount ++;
	}else{
		echo "<td style=\"width:35%;vertical-align:middle;\"><h2>" . $color['color'] ."</h2></td>";
	}
	
	$currentLine ++;
}
echo "</tbody></table></div></div></div></div>";
echo "</div>";
?>
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