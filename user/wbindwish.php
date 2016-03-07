<?php
session_start ();
use mysql\dbhelper;
include '../mysql/dbhelper.php';
header ( "Content-Type: text/html;charset=utf-8" );
$dbhelper = new dbhelper();

$username = $_SESSION ['username'];
if($username == null){
	$type = $_GET ['type'];
	if(strcmp($type,"register") == 0){
		$email = $_POST ["email"];
		$username = $_POST ["username"];
		$password = $_POST ["password"];
		$check = $dbhelper->queryUser($username, $email);
		$checkrow=mysql_fetch_array($check);
		if($checkrow){
			if($checkrow['username'] == $username){
				header("Location:./wregister.php?errorMsg=该用户已经存在");
				exit;
			}
			if($checkrow['email'] == $email){
				header("Location:./wregister.php?errorMsg=该邮箱地址已经被注册");
				exit;
			}
		}else{
			$result = $dbhelper->createUser($username, md5($password), $email);
			if($result !== false){
				$_SESSION ['username'] = $username;
			}else{
				header("Location:./wregister.php?errorMsg=注册失败");
				exit;
			}
		}
	}else{
		//login;
		$username = $_POST["username"];
		$password = $_POST ["password"];
	
		$dbhelper = new dbhelper();
		$result = $dbhelper->userLogin($username, md5($password));
		$row=mysql_fetch_array($result);
		if($row){
			$_SESSION ['username'] = $username;
		}else{
			header("Location:http://localhost/wishhelper/user/wlogin.php?errorMsg=登录失败");
			exit;
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
			<title>Wish 商户平台</title>
			<meta name="keywords" content="">
				<link rel="stylesheet" type="text/css" href="../css/home_page.css">
					<link href="../css/bootstrap.min.css" rel="stylesheet">
						<script src="../js/jquery-2.2.0.min.js"></script>
						<script src="../js/bootstrap.min.js"></script>
</head>
<body>
<!-- HEADER -->
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
								<li><a href="./wproductstatus.php">产品状态</a></li>
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
<div class="banner-container">
</div>
<form id="bind_form" action="./wbinding.php" method="post">
<div id="page-content" class="container-fluid  user">
<ul>
&nbsp;&nbsp;&nbsp;&nbsp;<h4>请在wish设置页(账号-》设置-》API设置(V2))处，复制并填写如下wish账号验证信息：</h4></ul>
<br/>
<ul><h5>1，请在"App Name"项填写应用名称: wish管理助手</h4></ul>
<br/>
<ul><h5>2，请填写&nbsp;&nbsp;Client Id：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="clientid" type="text" name="clientid" value=""/></h5></ul>
<br/>
<ul><h5>3，请填写&nbsp;&nbsp;Client Secret：&nbsp;&nbsp;<input id="clientsecret" type="text" name="clientsecret" value=""/></h5></ul>
<br/>
<ul><h5>4，请在"Redirect URI"项填写如下网址: https://wishconsole.com/user/wbinding.php</h5></ul>
<br/>
<ul><h5>5，请填写店铺名称：&nbsp;&nbsp;<input id="storename" type="text" name="storename" value=""/></h5></ul>
<br/>	
<ul><button type="button" id="bind">绑定账号</button></ul>
</div>
</form>
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
	
<script type="text/javascript" src="../js/jquery-2.2.0.min.js" charset="UTF-8"></script>
<script type="text/javascript">
$(document).ready(function(){
  $("#bind").click(function(){
  	if($("#clientid").val() == ""){
  		alert("请输入Client Id值");
  	}
  	if($("#clientsecret").val() == ""){
  		alert("请输入Client Secret值");
  	}
  	if($("#storename").val() == ""){
  		alert("请输入店铺名称");
  	}
  	if($("#clientid").val() != "" && $("#clientsecret").val() != "" && $("#storename").val() != ""){
  	  	$("#bind_form").submit()}	
  });
});
</script>
</body>
</html>