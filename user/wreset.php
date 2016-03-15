<?php 
require_once("PHPMailerAutoload.php");
include dirname ( '__FILE__' ) . './mysql/dbhelper.php';
use mysql\dbhelper;
$dbhelper = new dbhelper();
$errorMsg = $_GET ['errorMsg'];
$token = $_GET ['t'];
$isreset = $_GET['type'];
if(strcmp("reset",$isreset) == 0){
	$password = $_POST ["password"];
	$currentUserid = $_POST ["userid"];
	$result = $dbhelper->updatepsd ( $currentUserid, md5 ( $password ));
	$dbhelper->removeResetToken($currentUserid);
	if($result){
		header ( "Location:./wlogin.php?errorMsg=重置密码成功,请重新登录" );
		exit();
	}else{
		$errorMsg = "对不起，重置密码失败，请重新操作,或者联系管理员admin@wishconsole.com";
	}
	
}
$userid = $dbhelper->queryResetpsdUser($token);
if($token != null && $userid == null)
	$errorMsg = "对不起，您邮箱中的链接不正确或者已经过期，如想重置密码，请重新操作";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Wish管理助手-更有效率的Wish商户实用工具</title>
			<meta name="keywords" content="">
				<link rel="stylesheet" type="text/css"
					href="../css/new_signup_page.css">

</head>
<script type="text/javascript">

	function sendmail(){
		var email = document.getElementById("email").value;
		if(email == null || email == ''){
			alert("请输入邮箱地址");
			return;
		}
		var emailpattern = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
		if(!emailpattern.test(email)){
	    	alert("邮箱格式不正确");
	    	return;
	    }
		var resetform = document.getElementById("resetform");
		resetform.submit();
	}

	function resetpsd(){
		var password = document.getElementById("password").value;
		var confirm_password = document.getElementById("confirm_password").value;
		if(password == null || password == ''){
			alert("请输入密码");
			return;
		}
		if(confirm_password == null || confirm_password == ''){
			alert("请再次输入密码");
			return;
		}

		if(password != confirm_password){
			alert("两次输入的密码不一致，请重新输入");
			return;
		} 
		var registerform = document.getElementById("resetform");
		resetform.action = "wreset.php?type=reset";
		resetform.submit();
	}
</script>
<body>
	<!-- HEADER -->
	<div id="header" class="navbar navbar-fixed-top">
		<div class="container-fluid">
			<a class="brand" href="https://wishconsole.com/"> <span
				class="merchant-header-text">Wish管理助手-更有效率的Wish商户实用工具</span>
			</a>

		</div>
	</div>
	<!-- END HEADER -->
	<!-- SUB HEADER NAV-->
	<!-- splash page subheader-->

	<div id="page-content" class="container-fluid fixed-width ">

		<div id="signup-page-content">
			<div class="signup-page-container">
				<div class="signup-page-title">重置密码</div>
				<div class="signup-page-content">
					<form class="form form-horizontal" id="resetform" method="post"
						action="sendmail.php">
						<?php if($errorMsg != null)
							echo "<ul align=\"center\">".$errorMsg."</ul>";
						if(t != null && $userid != null){
							echo "<div class=\"control-group\">";
							echo "<input type=\"hidden\" id=\"userid\" name=\"userid\" value=".$userid.">";
							echo "<label class=\"control-label\" for=\"password\"> 密码</label>";
							echo "<div class=\"controls input-append\">";
							echo "<input type=\"password\" id=\"password\" name=\"password\" class=\"input-block-level\"";
							echo "placeholder=\"输入密码\"></div></div>";
							echo "<div class=\"control-group\">";
							echo "<label class=\"control-label\" for=\"confirm_password\"> 确认密码</label>";
							echo "<div class=\"controls input-append\">";
							echo "<input type=\"password\" id=\"confirm_password\" name=\"confirm_password\" class=\"input-block-level\" placeholder=\"请再次输入您的密码\"></div></div>";
							echo "<div id=\"create-store-container\"><input type=\"button\" id=\"signup-button\"";
							echo " class=\"input-block-level flat-signup-btn\" onclick=\"resetpsd()\" value=\"重置密码\"></div>";
						}else{
							echo "<div class=\"control-group\">";
							echo "<label class=\"controls\" for=\"email_address\"> 请输入注册时使用的邮箱地址</label>";
							echo "<div class=\"controls input-append\"><input type=\"text\" id=\"email\" name=\"email\" class=\"input-block-level\" placeholder=\"示例：hello@example.com\">";
							echo "</div></div>";
							echo "<div id=\"create-store-container\"><input type=\"button\" id=\"signup-button\"";
							echo " class=\"input-block-level flat-signup-btn\" onclick=\"sendmail()\" value=\"发送邮件\"></div>";
						}
						?>
					</form>
				</div>
			</div>
		</div>
		<div class="signup-page-footer">

			已经有账号了？ <a href="wlogin.php">点击这里登入</a>

		</div>



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