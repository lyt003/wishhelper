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


$result = $dbhelper->getUserToken ( $username );
$accounts = array ();
$i = 0;
while ( $rows = mysql_fetch_array ( $result ) ) {
	$accounts ['clientid' . $i] = $rows ['clientid'];
	$accounts ['clientsecret' . $i] = $rows ['clientsecret'];
	$accounts ['token' . $i] = $rows ['token'];
	$accounts ['refresh_token' . $i] = $rows ['refresh_token'];
	$accounts ['accountid' . $i] = $rows ['accountid'];
	$i ++;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- saved from url=(0031)http://china-merchant.wish.com/ -->
<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wish 商户平台</title>
<meta name="keywords" content="">
<link rel="stylesheet" type="text/css" href="../css/home_page.css">
</head>
<body>
<!-- HEADER -->
<div id="header" class="navbar navbar-fixed-top 



" style="left: 0px;">
<div class="container-fluid ">
<a class="brand" href="http://wishconsole.com/">
<span
				class="merchant-header-text"> 更有效率的Wish商户实用工具 </span>
</a>

<div class="pull-right">
<ul class="nav">
<li data-mid="5416857ef8abc87989774c1b" data-uid="5413fe984ad3ab745fee8b48">
<?php echo $username?>
</li>
<li><button><a href="./wlogin.php?type=exit">注销</a></button></li>

</ul>

</div>

</div>
</div>
<!-- END HEADER -->
<!-- SUB HEADER NAV-->
<!-- splash page subheader-->



<div id="sub-header-nav" class="navbar navbar-fixed-top sub-header" style="left: 0px;">
<div class="navbar-inner">
<div class="container-fluid">
<div class="pull-left">
<ul class="nav">


<li><a href="./wusercenter.php">
订单处理
</a></li>
<li>
<a href="./wuploadproduct.php">
产品上传
</a>
</li>
<li><a href="http://wishconsole.com/">
个人信息
</a></li>
</ul>
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

<div id="page-content" class="container-fluid  user">
<li>已绑定的wish账号:
<?php  for($count = 0; $count < $i; $count ++) {
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$accounts ['accountid' . $count];
}?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="wbindwish.php">绑定wish账号</a></li>
<ul align="center"><a href="../orders.php" style="font-size: 56px; color: #000000">处理订单</a></ul>

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