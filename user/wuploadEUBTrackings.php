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
	header ( "Location:./wlogin.php" );
	exit ();
}

$userid = $_SESSION ['userid'];
session_commit();
$trackings = $_POST['trackings'];
if($trackings != null){
	$trackingdatas = array();
	$trackingArr = explode("\r\n",$trackings);
	if($trackingArr != null){
		foreach ($trackingArr as $trackingdata){
			$trackingInfo = explode("|",$trackingdata);
			if($trackingInfo != null && count($trackingInfo)>=2){
				if(strlen(trim($trackingInfo[0])) == 24 || strpos(trim($trackingInfo[1]),'L') == 0){
					$trackingdatas[] = $trackingInfo;
					$dbhelper->uploadEUBTracking(trim($trackingInfo[0]), trim($trackingInfo[1]));
				}
			}
		}
	}
	if(count($trackingdatas) == 0){
		echo "<br/><br/><br/><br/><br/><br/>数据格式不对";
	}else{
		header ( "Location:./wusercenter.php" );
		exit();
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
		<form class="form-horizontal" id="uploadEUBTrackings"
			action="./wuploadEUBTrackings.php" method="post">
			<fieldset>
				<textarea rows="10" id="trackings" name="trackings"
					class="input-block-level">
                        </textarea>
				<button type="button" class="btn btn-info" id="parse" name="parse" style="display: black" onclick="parseData()">文本解析</button>
				<button type="submit" class="btn btn-info" id="upload" name="upload" style="display: none" >提交</button>
			</fieldset>
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
	function parseData(){
		var data = document.getElementById("trackings").value;
		var r2 = new RegExp("\r","g");//回车符
		var r3 = new RegExp("\v","g");//垂直制表符
		var r4 = new RegExp("\t","g");//水平制表符
		var r5 = new RegExp("\f","g");//换页符
		data = data.replace(r4," ");
		var verifieddata = new Array();
		var trackings = new Array();
		trackings = data.split("\n");
		for(var i=0;i<trackings.length;i++){
			var curdatas = new Array();
			curdatas = trackings[i].split(" ");
			if(curdatas != null && curdatas.length>1){
				 for(var l=0;l<curdatas.length;l++){
						if($.trim(curdatas[l]).length == 24){
							curdatas[0] =curdatas[l]; 
						}
					 if($.trim(curdatas[l]).length == 13 && $.trim(curdatas[l]).indexOf("L") ==0){
						curdatas[1] =curdatas[l];
						} 
					}
			 	if($.trim(curdatas[0]).length== 24 && $.trim(curdatas[1]).length== 13) {
			 		verifieddata.push(curdatas);
				 	}
					  
			}
			
		}
		var verifiedContent = "解析的订单号和运单号：\n";
		if(verifieddata.length>0){
			for(var k=0;k<verifieddata.length;k++){
				verifiedContent = verifiedContent + verifieddata[k][0]  + "  | " +verifieddata[k][1] + "\n"; 
				}
			document.getElementById("parse").style.display="none";
			document.getElementById("upload").style.display="block";
		}else{
			verifiedContent = "字符串格式不对\n" + data;
			document.getElementById("parse").style.display="block";
			document.getElementById("upload").style.display="none";
		}

		document.getElementById("trackings").value = verifiedContent;
		
	}
</script>
</body>
</html>